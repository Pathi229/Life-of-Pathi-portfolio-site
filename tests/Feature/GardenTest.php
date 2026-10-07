<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Entry;
use App\Models\Media;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GardenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->withoutVite();
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_public_destinations_and_same_project_from_branch_and_channel(): void
    {
        foreach (['/', '/explore', '/work', '/blogs', '/channels', '/channels/crochet-guide', '/branches/crochet', '/now', '/contact', '/sitemap.xml'] as $path) {
            $this->get($path)->assertOk();
        }
        $url = route('entry', 'everyday-crochet-bag');
        $this->get('/branches/crochet')->assertSee($url);
        $this->get('/channels/crochet-guide')->assertSee($url);
        $this->get($url)->assertSee('Gather your materials');
    }

    public function test_parent_branch_includes_its_nested_pathways(): void
    {
        $branch = Branch::where('slug', 'making-practice')->firstOrFail();
        $this->get('/branches/making-practice')->assertOk()->assertSee('An everyday crochet bag');
        $this->get('/explore?branch='.$branch->id.'&type=article')->assertOk()->assertSee('Gather your materials');
    }

    public function test_tutorial_order_is_shared_with_project_and_series(): void
    {
        $this->get('/entries/crochet-bag-main-body')->assertSee('Gather your materials')->assertSee('Bringing the pieces together');
        $this->get('/series/crochet-bag-start-to-finish')->assertSeeInOrder(['Gather your materials', 'Making the main body', 'Bringing the pieces together', 'The finished result']);
    }

    public function test_combined_filters_work_and_are_preserved_between_views(): void
    {
        $b = Branch::where('slug', 'crochet')->first();
        $query = ['branch' => $b->id, 'type' => 'article', 'maturity' => 'growing', 'difficulty' => 'beginner', 'view' => 'tree'];
        $this->get('/explore?'.http_build_query($query))->assertOk()->assertSee('Gather your materials')->assertDontSee('A table worth gathering around')->assertSee('view=list')->assertSee('difficulty=beginner');
        $this->get('/explore?'.http_build_query(array_merge($query, ['view' => 'list'])))->assertOk()->assertSee('Gather your materials')->assertSee('view=tree');
    }

    public function test_private_draft_and_unlisted_discovery_rules(): void
    {
        foreach (['private', 'draft'] as $slug) {
            $this->get('/entries/demo-'.$slug)->assertNotFound();
        }
        $this->get('/entries/demo-unlisted')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        foreach (['/explore', '/blogs', '/channels/life-of-pathi', '/sitemap.xml', '/'] as $path) {
            $this->get($path)->assertDontSee('demo-unlisted')->assertDontSee('demo-private')->assertDontSee('demo-draft');
        }
    }

    public function test_media_is_private_and_requires_an_accessible_content_context(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('media/notes.txt', 'Private attachment');
        $m = Media::create(['name' => 'Notes', 'path' => 'media/notes.txt', 'alt' => 'Text notes']);
        $e = Entry::where('slug', 'demo-private')->first();
        $e->media()->attach($m);
        $this->get('/media/'.$m->id)->assertNotFound();
        $this->get('/media/'.$m->id.'?entry='.$e->id)->assertNotFound();
        $this->get('/storage/media/notes.txt')->assertNotFound();
        $e->update(['visibility' => 'public']);
        $this->get('/media/'.$m->id.'?entry='.$e->id)->assertOk()->assertStreamedContent('Private attachment')->assertHeader('X-Content-Type-Options', 'nosniff');
        $e->update(['publication' => 'archived']);
        $this->get('/media/'.$m->id.'?entry='.$e->id)->assertNotFound();
    }

    public function test_admin_is_explicit_and_registration_is_unavailable(): void
    {
        $this->get('/admin')->assertRedirect();
        $this->get('/admin/register')->assertNotFound();
        $this->actingAs(User::factory()->create(['is_admin' => false]))->get('/admin')->assertForbidden();
        $this->actingAs($this->admin())->get('/admin')->assertOk();
    }

    public function test_admin_resources_load(): void
    {
        $this->actingAs($this->admin());
        foreach (['projects', 'articles', 'videos', 'branches', 'channels', 'media', 'series', 'settings'] as $resource) {
            $this->get('/admin/'.$resource)->assertOk();
        }
    }

    public function test_preview_requires_admin_and_valid_expiring_signature(): void
    {
        $e = Entry::where('slug', 'demo-draft')->first();
        $url = URL::temporarySignedRoute('entry.preview', now()->addMinutes(20), ['entry' => $e->id]);
        $this->get($url)->assertRedirect();
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
        $this->actingAs($this->admin())->get($url)->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->travel(21)->minutes();
        $this->get($url)->assertForbidden();
    }

    public function test_publication_archive_and_slug_redirect_are_consistent(): void
    {
        $e = Entry::where('slug', 'demo-draft')->first();
        $e->update(['publication' => 'published', 'visibility' => 'public']);
        $this->get('/entries/demo-draft')->assertOk();
        $this->get('/explore?q=Demo+draft')->assertSee('Demo draft entry');
        $e->update(['slug' => 'a-new-url']);
        $this->get('/entries/demo-draft')->assertRedirect(route('entry', 'a-new-url'));
        $this->get('/entries/a-new-url')->assertOk();
        $this->get('/sitemap.xml')->assertSee('a-new-url')->assertDontSee('/entries/demo-draft');
        $e->update(['publication' => 'archived']);
        $this->get('/entries/a-new-url')->assertNotFound();
        $this->get('/entries/demo-draft')->assertNotFound();
        $this->get('/explore?q=Demo+draft')->assertDontSee('Demo draft entry');
    }

    public function test_portfolio_curation_is_independent_from_publication(): void
    {
        $this->get('/work?category=technology')->assertSee('A small digital garden')->assertDontSee('A table worth gathering around');
        $this->get('/work?category=creative')->assertSee('A table worth gathering around')->assertDontSee('A small digital garden');
        $e = Entry::where('slug', 'everyday-crochet-bag')->first();
        $this->get('/work')->assertDontSee($e->title);
        $e->update(['portfolio' => true]);
        $this->get('/work')->assertSee($e->title);
        $e->update(['visibility' => 'private']);
        $this->get('/work')->assertDontSee($e->title);
    }

    public function test_branch_cycles_are_rejected(): void
    {
        $parent = Branch::where('slug', 'making-practice')->first();
        $child = Branch::where('slug', 'crochet')->first();
        $this->expectException(ValidationException::class);
        $parent->update(['parent_id' => $child->id]);
    }

    public function test_video_urls_are_validated(): void
    {
        $e = Entry::where('type', 'video')->first();
        $this->expectException(ValidationException::class);
        $e->update(['video_url' => 'https://youtube.com.evil.test/embed/script']);
    }

    public function test_project_relationships_are_validated(): void
    {
        $article = Entry::where('type', 'article')->first();
        $this->expectException(ValidationException::class);
        $article->update(['project_id' => $article->id]);
    }

    public function test_referenced_media_cannot_be_removed(): void
    {
        $m = Media::create(['name' => 'Test', 'path' => 'media/test.pdf']);
        Entry::first()->media()->attach($m);
        $this->expectException(ValidationException::class);
        $m->delete();
    }

    public function test_rich_content_is_sanitised(): void
    {
        $e = Entry::where('slug', 'notes-on-learning-by-making')->first();
        $e->update(['body' => '<script>alert("unsafe")</script>'."\n\n[Unsafe](javascript:alert(1))\n\n## Safe heading"]);
        $this->get('/entries/'.$e->slug)->assertOk()->assertDontSee('<script>alert', false)->assertDontSee('href="javascript:', false)->assertSee('Safe heading');
    }

    public function test_seed_is_idempotent_and_does_not_overwrite_edits(): void
    {
        $count = Entry::count();
        $e = Entry::first();
        $e->update(['summary' => 'My edited summary']);
        $this->seed(DatabaseSeeder::class);
        $this->assertSame($count, Entry::count());
        $this->assertSame('My edited summary', $e->fresh()->summary);
    }

    public function test_soft_deleted_content_is_recoverable_and_excluded(): void
    {
        $e = Entry::where('slug', 'everyday-crochet-bag')->first();
        $e->delete();
        $this->get('/entries/'.$e->slug)->assertNotFound();
        $this->get('/channels/crochet-guide')->assertDontSee($e->title);
        $e->restore();
        $this->get('/entries/'.$e->slug)->assertOk();
    }

    public function test_tree_has_keyboard_controls_and_normal_links(): void
    {
        $this->get('/')->assertSee('aria-controls=', false)->assertSee('type="button"', false)->assertSee('Browse everything as a list')->assertSee(route('branch', 'crochet') === null ? 'impossible' : 'Making &amp; Practice', false);
    }
}
