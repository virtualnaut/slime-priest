<?php

namespace App\Http\Controllers;

use App\Models\PersonOfInterest;
use App\Services\API\HenrikAPIService;
use App\Services\TrackedPersonService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class TrackingController extends Controller
{
    const CHECK_TIMEOUT = 20;

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

    public function status()
    {
        $is_running = Cache::has('last-check') && ((new Carbon((int)(Cache::get('last-check'))))->gt((Carbon::now())->subSeconds(self::CHECK_TIMEOUT)));
        $tracked_person = TrackedPersonService::current();

        if ($is_running && $tracked_person) {
            $status = 'active';
        } else if ($is_running && !$tracked_person) {
            $status = 'idle';
        } else {
            $status = 'offline';
        }

        return response([
            'is_running' => $is_running,
            'is_tracking' => !!$tracked_person,
            'tracked_person' => $tracked_person,
            'status' => $status
        ]);
    }
}
