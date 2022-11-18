<?php

use App\Http\Controllers\DiscordUserController;
use App\Http\Controllers\PeopleOfInterestController;
use App\Http\Controllers\TrackingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/discord/user/{userID}', [DiscordUserController::class, 'show']);

Route::prefix('poi')->group(function () {
    Route::post('/tracking/{user}/{tag}', [TrackingController::class, 'set']);
    Route::post('/tracking/{discordID}', [TrackingController::class, 'setByDiscordID']);
    Route::get('/tracking', [TrackingController::class, 'get']);
    Route::delete('/tracking', [TrackingController::class, 'destroy']);
    Route::get('/tracking/status', [TrackingController::class, 'status']);

    Route::get('/', [PeopleOfInterestController::class, 'index']);
    Route::post('/', [PeopleOfInterestController::class, 'create']);
    Route::delete('/destroy/{user}/{tag}', [PeopleOfInterestController::class, 'destroy']);
});

Route::get('/bot/resend', [BotController::class, 'resend']);
