<?php

namespace App\Filament;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class LocalAvatarProvider implements AvatarProvider
{
    public function get(Model|Authenticatable $record): string
    {
        return asset('images/avatar.svg');
    }
}
