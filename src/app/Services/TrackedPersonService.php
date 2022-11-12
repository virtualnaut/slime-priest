<?php

namespace App\Services;

use App\Models\PersonOfInterest;
use Illuminate\Support\Facades\Cache;

class TrackedPersonService
{
    public static function current()
    {
        return PersonOfInterest::where('is_tracking', '=', true)->first() ?? null;
    }

    public static function set(PersonOfInterest $person): bool
    {
        if ($person->is_tracking) {
            return false;
        }

        self::turn_off();

        $person = $person->fresh();
        $person->is_tracking = true;
        $person->save();

        // Clear the cache if the tracked user has actually changed.
        Cache::forget('last-match-id');

        return true;
    }

    public static function turn_off()
    {
        // There should only ever be one tracked person, but clear all to make sure.
        $people = PersonOfInterest::where('is_tracking', '=', true)->get();

        foreach ($people as $person) {
            $person->is_tracking = false;
            $person->save();
        }
    }

    public static function active()
    {
        return self::current() === null ? false : true;
    }

    public static function puuid()
    {
        return self::current()->puuid;
    }
}
