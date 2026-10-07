<?php

namespace App\Models;

use App\Models\Concerns\RemembersCollectionSlugs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Branch extends Model
{
    use RemembersCollectionSlugs, SoftDeletes;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function ($b) {
            $seen = [$b->id];
            $p = $b->parent_id;
            while ($p) {
                if (in_array($p, $seen)) {
                    throw ValidationException::withMessages(['parent_id' => 'A branch cannot contain itself.']);
                }
                $seen[] = $p;
                $p = static::find($p)?->parent_id;
            }
        });
    }

    public function subtreeIds(): array
    {
        $ids = [$this->id];
        $frontier = $ids;
        while ($frontier) {
            $children = static::whereIn('parent_id', $frontier)->where('archived', false)->pluck('id')->all();
            $frontier = array_values(array_diff($children, $ids));
            $ids = array_merge($ids, $frontier);
        }

        return $ids;
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function entries()
    {
        return $this->belongsToMany(Entry::class);
    }

    public function channels()
    {
        return $this->belongsToMany(Channel::class);
    }
}
