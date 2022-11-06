<?php

namespace App\Http\Controllers;

use App\Models\PersonOfInterest;
use App\Services\API\HenrikAPIService;
use App\Services\TrackedPersonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TrackingController extends Controller
{
    public function __construct()
    {
        $this->valorant = new HenrikAPIService();
    }

    public function set($user, $tag)
    {
        TrackedPersonService::set(PersonOfInterest::where('name', '=', $user)->where('tag', '=', $tag)->firstOrFail());

        // Cache::put('tracking-user', $this->valorant->puuid($user, $tag));

        return response('', 204);
    }

    public function get()
    {
        return TrackedPersonService::current();
    }

    public function destroy()
    {
        TrackedPersonService::turn_off();
        return response('', 204);
    }
}
