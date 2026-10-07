<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Media extends Model
{
    use SoftDeletes;

    protected $table = 'media';

    protected $guarded = [];

    public function entries()
    {
        return $this->belongsToMany(Entry::class);
    }

    protected static function booted(): void
    {
        static::deleting(function ($m) {
            if ($m->entries()->exists() || Setting::where('cv_media_id', $m->id)->exists() || Channel::where('cover_media_id', $m->id)->exists()) {
                throw ValidationException::withMessages(['media' => 'Remove content references before deleting this media.']);
            }
        });
    }
}
