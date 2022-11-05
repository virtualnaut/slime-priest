<?php

namespace App\Http\Controllers;

use App\Services\API\HenrikAPIService;
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
        Cache::put('tracking-user', $this->valorant->puuid($user, $tag));
        return response('', 204);
    }
}
