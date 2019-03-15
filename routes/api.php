<?php

// Request a pre-check to the remote server.
Route::post('prechecks', PreChecksController::class)->name('prechecks');

// Request an access token request, and a connectivity test.
Route::post('ping', PingController::class)->name('ping');

// Uploads a zip file.
Route::post('upload', UploadController::class)->name('upload');
