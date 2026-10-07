<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

trait RemembersCollectionSlugs
{
    protected static function bootRemembersCollectionSlugs(): void
    {
        static::saving(function ($record) {
            $kind = class_basename($record);
            if (DB::table('collection_redirects')->where('kind', $kind)->where('slug', $record->slug)->where('record_id', '!=', $record->id ?? 0)->exists()) {
                throw ValidationException::withMessages(['slug' => 'This URL belongs to a previous collection.']);
            }
        });
        static::updated(function ($record) {
            if ($record->wasChanged('slug')) {
                DB::table('collection_redirects')->updateOrInsert(['kind' => class_basename($record), 'slug' => $record->getOriginal('slug')], ['record_id' => $record->id]);
            }
        });
    }
}
