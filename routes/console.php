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
Artisan::command('paymaya:webhooks-set', function () {

    $url = $this->ask('Base URL');

    $paymayaController = new \App\Http\Controllers\PaymayaController();

    $paymayaController->clearWebhooks();
    $paymayaController->setupWebhooks($url);
});

Artisan::command('paymaya:webhooks-list', function(){
    $paymayaController = new \App\Http\Controllers\PaymayaController();

    $list = $paymayaController->listWebhooks();

    foreach ($list as $wh) {
        dump($wh);
    }
});