<?php

use Illuminate\Support\Facades\Route;
use Layerok\PosterPos\Controllers\PosterWebhookController;
use Layerok\PosterPos\Controllers\WayForPayController;
use poster\src\PosterApi;

Route::post('/posterpos/webhook/handle', PosterWebhookController::class);
Route::post('/wayforpay-service-url', WayForPayController::class);


