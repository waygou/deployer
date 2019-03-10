<?php

Route::post('prechecks', PreChecksController::class)
     ->name('prechecks');

Route::post('ping', PingController::class)
     ->name('ping');

/*
Route::fallback(function () {
    return response()->json([
        'response' => 'Route Not Found.', ], 404);
});
*/
