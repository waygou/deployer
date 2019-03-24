<?php

// Request a pre-check to the remote server.
Route::post('prechecks', PreChecksController::class)->name('prechecks');

// Request an access token request, and a connectivity test.
Route::post('ping', PingController::class)->name('ping');

// Uploads codebase (zip) to remote environment.
Route::post('upload', UploadController::class)->name('upload');

// Runs pre-deployment scripts.
Route::post('pre-scripts', PreScriptsController::class)->name('pre-scripts');

// Deploy (unzip) codebase.
Route::post('deploy', DeploymentController::class)->name('pre-scripts');

// Runs post-deployment scripts.
Route::post('post-scripts', PostScriptsController::class)->name('post-scripts');
