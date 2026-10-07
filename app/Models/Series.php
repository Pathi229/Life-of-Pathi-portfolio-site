<?php

namespace App\Models;

use App\Models\Concerns\RemembersCollectionSlugs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Series extends Model
{
    use RemembersCollectionSlugs, SoftDeletes;

    protected $table = 'series';

    protected $guarded = [];

    public function entries()
    {
        return $this->hasMany(Entry::class)->orderBy('sequence')->orderBy('id');
    }
}
