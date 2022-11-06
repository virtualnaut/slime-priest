<?php

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


Route::prefix('poi')->group(function () {
    Route::post('/tracking/{user}/{tag}', [TrackingController::class, 'set']);
    Route::get('/tracking', [TrackingController::class, 'get']);
    Route::delete('/tracking', [TrackingController::class, 'destroy']);

    Route::post('/create', [PeopleOfInterestController::class, 'create']);
});
