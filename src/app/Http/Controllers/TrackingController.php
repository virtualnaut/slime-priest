<?php

namespace App\Http\Controllers;

use App\Models\PersonOfInterest;
use App\Services\API\HenrikAPIService;
use App\Services\TrackedPersonService;

class TrackingController extends Controller
{
    public function __construct()
    {
        $this->valorant = new HenrikAPIService();
    }

    public function set($user, $tag)
    {
        $changed = TrackedPersonService::set(PersonOfInterest::where('name', '=', $user)->where('tag', '=', $tag)->firstOrFail());

        return response([
            'changed' => $changed
        ], 200);
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
