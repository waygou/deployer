<?php

Route::post('ping', PingController::class)
     ->name('ping');

Route::post('predeployment-check', PreDeploymentChecksController::class)
     ->name('predeployment-check');

/*
Route::fallback(function () {
    return response()->json([
        'response' => 'Route Not Found.', ], 404);
});
*/
