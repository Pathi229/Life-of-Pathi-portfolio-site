<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = [];

    protected $casts = ['social_links' => 'array', 'now_date' => 'date'];

    public function cv()
    {
        return $this->belongsTo(Media::class, 'cv_media_id');
    }
}
