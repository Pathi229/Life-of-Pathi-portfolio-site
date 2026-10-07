<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Entry extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = ['case_study' => 'array', 'featured' => 'boolean', 'portfolio' => 'boolean', 'published_at' => 'datetime', 'meaningful_updated_at' => 'datetime', 'started_at' => 'date', 'finished_at' => 'date'];

    public function scopeDiscoverable($q)
    {
        return $q->where('publication', 'published')->where('visibility', 'public')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function publiclyAccessible(): bool
    {
        return $this->publication === 'published' && in_array($this->visibility, ['public', 'unlisted']) && $this->published_at && $this->published_at->lte(now());
    }

    public function primaryBranch()
    {
        return $this->belongsTo(Branch::class, 'primary_branch_id');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class);
    }

    public function channels()
    {
        return $this->belongsToMany(Channel::class);
    }

    public function media()
    {
        return $this->belongsToMany(Media::class)->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function project()
    {
        return $this->belongsTo(self::class, 'project_id');
    }

    public function article()
    {
        return $this->belongsTo(self::class, 'article_id');
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function parts()
    {
        return $this->hasMany(self::class, 'project_id')->orderBy('sequence')->orderBy('id');
    }

    protected static function booted(): void
    {
        static::saving(function ($e) {
            foreach (['type' => ['project', 'article', 'video'], 'publication' => ['draft', 'published', 'archived'], 'visibility' => ['public', 'unlisted', 'private'], 'maturity' => ['seed', 'growing', 'fruit']] as $field => $allowed) {
                if (! in_array($e->$field, $allowed)) {
                    throw ValidationException::withMessages([$field => 'Invalid '.$field]);
                }
            }
            if ($e->project_id && ($e->project_id == $e->id || static::find($e->project_id)?->type !== 'project')) {
                throw ValidationException::withMessages(['project_id' => 'Select a different project.']);
            }
            if ($e->article_id && static::find($e->article_id)?->type !== 'article') {
                throw ValidationException::withMessages(['article_id' => 'Select an article.']);
            }
            if ($e->video_url) {
                $host = strtolower(parse_url($e->video_url, PHP_URL_HOST) ?? '');
                if (parse_url($e->video_url, PHP_URL_SCHEME) !== 'https' || ! in_array($host, ['youtube.com', 'www.youtube.com', 'youtu.be', 'vimeo.com', 'www.vimeo.com'])) {
                    throw ValidationException::withMessages(['video_url' => 'Use an HTTPS YouTube or Vimeo link.']);
                }
            }
            if ($e->video_url) {
                $e->platform = str_contains(parse_url($e->video_url, PHP_URL_HOST), 'vimeo') ? 'vimeo' : 'youtube';
            }
            if ($e->publication === 'published' && ! $e->published_at) {
                $e->published_at = now();
            }
            if ($e->isDirty(['body', 'summary', 'title'])) {
                $e->meaningful_updated_at = now();
            }
            if (DB::table('slug_redirects')->where('slug', $e->slug)->where('entry_id', '!=', $e->id ?? 0)->exists()) {
                throw ValidationException::withMessages(['slug' => 'This URL has already been used.']);
            }
            if ($e->exists && $e->isDirty('slug')) {
                $old = $e->getOriginal('slug');
                if (static::where('slug', $e->slug)->whereKeyNot($e->id)->exists() || DB::table('slug_redirects')->where('slug', $e->slug)->where('entry_id', '!=', $e->id)->exists()) {
                    throw ValidationException::withMessages(['slug' => 'This URL has already been used.']);
                }
                if ($e->getOriginal('publication') === 'published') {
                    DB::table('slug_redirects')->updateOrInsert(['slug' => $old], ['entry_id' => $e->id]);
                }
            }
        });
    }
}
