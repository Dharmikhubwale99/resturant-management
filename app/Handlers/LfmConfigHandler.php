<?php

namespace App\Handlers;
use Illuminate\Support\Str;
use UniSharp\LaravelFilemanager\Handlers\ConfigHandler as Base;

class LfmConfigHandler extends Base
{
    public function userField()
    {
        $user = auth()->user();

        if (!$user) {
            return 'guest';
        }

        // adjust relation name if different
        $restaurant = $user->restaurants()->first();

        if ($restaurant) {
            return Str::slug($restaurant->name);
        }

        return 'user-' . $user->id;
    }
}
