<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

use Aceraven777\PayMaya\PayMayaSDK;
Artisan::command('set-paymaya-webhooks', function () {

    $url = $this->ask('Base URL');

    $paymayaController = new \App\Http\Controllers\PaymayaController();

    $paymayaController->setupWebhooks($url);
});
