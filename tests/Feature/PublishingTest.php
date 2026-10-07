<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\ManageBranches;
use App\Filament\Resources\Pages\ManageMedias;
use App\Filament\Resources\Pages\ManageProjects;
use App\Filament\Resources\Pages\ManageVideos;
use App\Models\Branch;
use App\Models\Channel;
use App\Models\Entry;
use App\Models\Media;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PublishingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->withoutVite();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_admin_creates_branch_uploads_image_and_publishes_project_without_code_changes(): void
    {
        Storage::fake('local');
        Livewire::test(ManageBranches::class)->callAction('create', data: ['name' => 'Test garden', 'slug' => 'test-garden', 'description' => 'A real publishing test', 'activity' => 'new', 'sort_order' => 7])->assertHasNoActionErrors();
        $branch = Branch::where('slug', 'test-garden')->firstOrFail();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII=');
        $file = UploadedFile::fake()->createWithContent('sample.png', $png);
        Livewire::test(ManageMedias::class)->callAction('create', data: ['name' => 'Test image', 'path' => $file, 'alt' => 'A neutral one-pixel test image', 'caption' => 'Upload acceptance test'])->assertHasNoActionErrors();
        $media = Media::where('name', 'Test image')->firstOrFail();
        Storage::disk('local')->assertExists($media->path);
        $channel = Channel::where('slug', 'life-of-pathi')->firstOrFail();
        Livewire::test(ManageProjects::class)->callAction('create', data: ['title' => 'A newly published project', 'slug' => 'newly-published-project', 'summary' => 'Created in the dashboard test', 'body' => '## Progress\nPublished without source edits.', 'primary_branch_id' => $branch->id, 'channels' => [$channel->id], 'media' => [$media->id], 'publication' => 'published', 'visibility' => 'public', 'maturity' => 'growing', 'featured' => true])->assertHasNoActionErrors();
        $entry = Entry::where('slug', 'newly-published-project')->firstOrFail();
        $this->assertSame('project', $entry->type);
        $this->assertTrue($entry->media->contains($media));
        $this->get('/entries/'.$entry->slug)->assertOk()->assertSee('A neutral one-pixel test image');
        $this->get('/branches/test-garden')->assertSee($entry->title);
        $this->get('/channels/life-of-pathi')->assertSee($entry->title);
        $this->get('/explore')->assertSee($entry->title);
        $this->get('/media/'.$media->id.'?entry='.$entry->id)->assertOk()->assertHeader('Content-Type', 'image/png');
    }

    public function test_admin_adds_video_related_to_article_and_project(): void
    {
        $project = Entry::where('type', 'project')->first();
        $article = Entry::where('type', 'article')->first();
        Livewire::test(ManageVideos::class)->callAction('create', data: ['title' => 'A new video', 'slug' => 'new-video', 'video_url' => 'https://vimeo.com/123456', 'project_id' => $project->id, 'article_id' => $article->id, 'publication' => 'published', 'visibility' => 'public', 'maturity' => 'seed'])->assertHasNoActionErrors();
        $video = Entry::where('slug', 'new-video')->firstOrFail();
        $this->assertSame('video', $video->type);
        $this->assertSame('vimeo', $video->platform);
        $this->get('/entries/'.$article->slug)->assertSee('A new video');
        $this->get('/entries/'.$project->slug)->assertSee('A new video');
    }

    public function test_gallery_order_is_saved_from_the_dashboard(): void
    {
        $first = Media::create(['name' => 'First image', 'path' => 'media/first.png', 'alt' => 'First']);
        $second = Media::create(['name' => 'Second image', 'path' => 'media/second.png', 'alt' => 'Second']);
        Livewire::test(ManageProjects::class)->callAction('create', data: ['title' => 'Ordered gallery', 'slug' => 'ordered-gallery', 'media' => [$second->id, $first->id], 'publication' => 'draft', 'visibility' => 'private', 'maturity' => 'seed'])->assertHasNoActionErrors();
        $this->assertSame([$second->id, $first->id], Entry::where('slug', 'ordered-gallery')->firstOrFail()->media->pluck('id')->all());
    }

    public function test_bad_upload_format_is_rejected(): void
    {
        Livewire::test(ManageMedias::class)->callAction('create', data: ['name' => 'Unsafe file', 'path' => UploadedFile::fake()->createWithContent('attack.html', '<script>alert(1)</script>'), 'alt' => 'Invalid'])->assertHasActionErrors(['path']);
        $this->assertFalse(Media::where('name', 'Unsafe file')->exists());
    }

    public function test_collection_slug_changes_redirect_and_seed_keeps_edits(): void
    {
        $branch = Branch::where('slug', 'crochet')->first();
        $branch->update(['slug' => 'crochet-practice']);
        $this->get('/branches/crochet')->assertRedirect(route('branch', 'crochet-practice'));
        $channel = Channel::where('slug', 'crochet-guide')->first();
        $channel->update(['slug' => 'stitch-guide']);
        $this->get('/channels/crochet-guide')->assertRedirect(route('channel', 'stitch-guide'));
        $this->seed(DatabaseSeeder::class);
        $this->assertSame('crochet-practice', $branch->fresh()->slug);
        $this->assertSame('stitch-guide', $channel->fresh()->slug);
    }
}
