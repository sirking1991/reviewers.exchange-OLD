<?php

use Illuminate\Support\Facades\Artisan;
use Luigel\Paymongo\Facades\Paymongo;

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

Artisan::command('paymongo:webhook-set', function(){
    $url = $this->ask('Base URL');
    $webhook = Paymongo::webhook()->create([
        'url' => $url . '/paymongo/webhook',
        'events' => ['source.chargeable']
    ]);
    dump($webhook);
});

Artisan::command('paymongo:webhook-enable', function(){
    $webhooks = Paymongo::webhook()->all();
    foreach ($webhooks as $wh) $wh->enable();
    dump(Paymongo::webhook()->all());
});

Artisan::command('paymongo:webhook-disable', function(){
    $webhooks = Paymongo::webhook()->all();
    foreach ($webhooks as $wh) $wh->disable();
    dump(Paymongo::webhook()->all());
});

Artisan::command('paymongo:webhook-list', function(){
    dump(Paymongo::webhook()->all());
});