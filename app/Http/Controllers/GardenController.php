<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Channel;
use App\Models\Entry;
use App\Models\Media;
use App\Models\Series;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GardenController extends Controller
{
    public function home()
    {
        return view('garden.home', ['treeEntries' => Entry::discoverable()->with(['branches', 'primaryBranch'])->orderByDesc('featured')->orderBy('sort_order')->get(), 'branches' => Branch::where('archived', false)->with('channels')->orderBy('sort_order')->orderBy('id')->get(), 'selected' => Entry::discoverable()->where('type', 'project')->where('portfolio', true)->orderBy('sort_order')->limit(3)->get(), 'growing' => Entry::discoverable()->where('type', 'project')->where('maturity', 'growing')->limit(3)->get(), 'recent' => Entry::discoverable()->latest('meaningful_updated_at')->limit(4)->get()]);
    }

    public function explore(Request $r)
    {
        $q = Entry::discoverable()->with(['primaryBranch', 'media', 'branches']);
        if ($r->filled('q')) {
            $q->where(fn ($q) => $q->where('title', 'like', '%'.$r->string('q').'%')->orWhere('summary', 'like', '%'.$r->string('q').'%')->orWhere('body', 'like', '%'.$r->string('q').'%'));
        }
        foreach (['type', 'maturity', 'difficulty', 'technique', 'category'] as $f) {
            if ($r->filled($f)) {
                $q->where($f, $r->string($f));
            }
        }
        if ($r->filled('branch')) {
            $branchIds = Branch::find($r->integer('branch'))?->subtreeIds() ?? [$r->integer('branch')];
            $q->where(fn ($q) => $q->whereIn('primary_branch_id', $branchIds)->orWhereHas('branches', fn ($q) => $q->whereIn('branches.id', $branchIds)));
        }
        if ($r->filled('channel')) {
            $q->whereHas('channels', fn ($q) => $q->where('channels.id', $r->integer('channel')));
        }
        if ($r->routeIs('work')) {
            $q->where('type', 'project')->where('portfolio', true);
        }
        if ($r->routeIs('blogs')) {
            $q->where('type', 'article');
        }

        return view('garden.explore', ['treeEntries' => (clone $q)->limit(200)->get(), 'entries' => $q->orderBy('sort_order')->latest('meaningful_updated_at')->paginate(12)->withQueryString(), 'branches' => Branch::where('archived', false)->with('channels')->orderBy('sort_order')->orderBy('id')->get(), 'channels' => Channel::all(), 'title' => $r->routeIs('work') ? 'Selected work' : ($r->routeIs('blogs') ? 'Blogs & Guides' : 'Explore the garden')]);
    }

    public function branch(string $slug)
    {
        if ($redirect = $this->collectionRedirect(Branch::class, $slug, 'branch')) {
            return $redirect;
        }
        $branch = Branch::where('slug', $slug)->firstOrFail();
        $branchIds = $branch->subtreeIds();

        return view('garden.collection', ['title' => $branch->name, 'intro' => $branch->description, 'status' => ($branch->archived ? 'Archived · ' : '').$branch->activity, 'entries' => Entry::discoverable()->where(fn ($q) => $q->whereIn('primary_branch_id', $branchIds)->orWhereHas('branches', fn ($q) => $q->whereIn('branches.id', $branchIds)))->orderByDesc('featured')->orderBy('sort_order')->paginate(12), 'channels' => $branch->channels, 'children' => $branch->children()->where('archived', false)->get()]);
    }

    public function channels()
    {
        return view('garden.channels', ['channels' => Channel::withCount(['entries' => fn ($q) => $q->discoverable()])->get()]);
    }

    public function channel(string $slug)
    {
        if ($redirect = $this->collectionRedirect(Channel::class, $slug, 'channel')) {
            return $redirect;
        }
        $c = Channel::where('slug', $slug)->firstOrFail();

        return view('garden.collection', ['title' => $c->name, 'intro' => $c->introduction, 'entries' => $c->entries()->discoverable()->orderByDesc('featured')->latest('published_at')->paginate(12), 'collection' => $c, 'series' => Series::whereHas('entries', fn ($q) => $q->discoverable()->whereHas('channels', fn ($q) => $q->where('channels.id', $c->id)))->get(), 'channels' => collect(), 'children' => $c->branches]);
    }

    public function series(string $slug)
    {
        if ($redirect = $this->collectionRedirect(Series::class, $slug, 'series')) {
            return $redirect;
        }
        $s = Series::where('slug', $slug)->firstOrFail();
        $entries = $s->entries()->discoverable()->get();
        abort_if($entries->isEmpty(), 404);

        return view('garden.collection', ['title' => $s->name, 'intro' => $s->description, 'entries' => $entries, 'channels' => collect(), 'children' => collect()]);
    }

    public function entry(Request $r, string $slug)
    {
        $entry = Entry::where('slug', $slug)->first();
        if (! $entry) {
            $id = DB::table('slug_redirects')->where('slug', $slug)->value('entry_id');
            $entry = Entry::find($id);
            abort_unless($entry && $entry->publiclyAccessible(), 404);

            return redirect()->route('entry', $entry->slug, 301);
        }abort_unless($entry->publiclyAccessible() || $r->user()?->is_admin, 404);

        return $this->renderEntry($entry);
    }

    public function preview(Request $r, Entry $entry)
    {
        abort_unless($r->user()?->is_admin && $r->hasValidSignature(), 403);

        return $this->renderEntry($entry, true);
    }

    private function renderEntry(Entry $entry, bool $preview = false)
    {
        $parts = $entry->parts()->discoverable()->where('type', 'article')->get();
        $videos = Entry::discoverable()->where('type', 'video')->where(fn ($q) => $q->where('article_id', $entry->id)->orWhere('project_id', $entry->id))->get();
        $related = Entry::discoverable()->where('type', 'project')->where('primary_branch_id', $entry->primary_branch_id)->whereKeyNot($entry->id)->limit(3)->get();
        $siblings = $entry->series_id ? $entry->series->entries()->discoverable()->get() : ($entry->project_id ? $entry->project->parts()->discoverable()->where('type', 'article')->get() : collect());
        $index = $siblings->search(fn ($x) => $x->id === $entry->id);

        return response()->view('garden.entry', ['entry' => $entry, 'parts' => $parts, 'previous' => $index !== false && $index > 0 ? $siblings[$index - 1] : null, 'next' => $index !== false ? $siblings->get($index + 1) : null, 'preview' => $preview, 'videos' => $videos, 'related' => $related])->header('X-Robots-Tag', $preview || $entry->visibility !== 'public' ? 'noindex, nofollow' : 'index, follow');
    }

    public function media(Request $r, Media $media)
    {
        $admin = (bool) $r->user()?->is_admin;
        $context = $r->integer('entry');
        $entry = $context ? Entry::find($context) : null;
        $allowed = $entry && $entry->publiclyAccessible() && $entry->media()->whereKey($media->id)->exists();
        $cv = Setting::where('cv_media_id', $media->id)->exists();
        abort_unless($admin || $allowed || $cv || Channel::where('cover_media_id', $media->id)->exists(), 404);
        abort_unless(Storage::disk('local')->exists($media->path), 404);
        $mime = Storage::disk('local')->mimeType($media->path);
        abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'application/pdf', 'text/plain']), 404);

        return Storage::disk('local')->response($media->path, null, ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store', 'Content-Disposition' => str_starts_with($mime, 'image/') ? 'inline' : 'attachment']);
    }

    private function collectionRedirect(string $model, string $slug, string $route)
    {
        if ($model::where('slug', $slug)->exists()) {
            return null;
        }
        $id = DB::table('collection_redirects')->where('kind', class_basename($model))->where('slug', $slug)->value('record_id');
        $record = $id ? $model::find($id) : null;

        return $record ? redirect()->route($route, $record->slug, 301) : null;
    }

    public function sitemap()
    {
        return response()->view('garden.sitemap', ['entries' => Entry::discoverable()->get(), 'branches' => Branch::where('archived', false)->get(), 'channels' => Channel::all()], 200)->header('Content-Type', 'application/xml');
    }
}
