<?php

namespace App\Models;

use App\Models\Concerns\RemembersCollectionSlugs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Channel extends Model
{
    use RemembersCollectionSlugs, SoftDeletes;

    protected $guarded = [];

    protected $casts = ['social_links' => 'array'];

    public function cover()
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class);
    }

    public function entries()
    {
        return $this->belongsToMany(Entry::class);
    }
}
