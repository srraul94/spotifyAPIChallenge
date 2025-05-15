<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SpotifyController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group( function () {
    Route::get('/user', [AuthController::class, 'getUser']);

    Route::get('/get-spotify-access-token', [SpotifyController::class, 'getAccessToken']);

    Route::prefix('artists')->group(function () {
        Route::get('/get-artist/{artistID}', [SpotifyController::class, 'getArtistByID']);
    });

});


