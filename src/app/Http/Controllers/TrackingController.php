<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TrackingController extends Controller
{
    public function set($user, $tag)
    {
        Cache::put('tracking-user', [$user, $tag]);
        return response('', 204);
    }
}
