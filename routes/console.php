<?php

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

use Luigel\Paymongo\Facades\Paymongo;
Artisan::command('paymongo:test-payment', function(){
    
    // $paymentMethod = Paymongo::paymentMethod()->create([
    //     'type' => 'card',
    //     'details' => [
    //         'card_number' => '4343434343434345',
    //         'exp_month' => 12,
    //         'exp_year' => 25,
    //         'cvc' => "123",
    //     ],
    //     'billing' => [
    //         'address' => [
    //             'line1' => 'Somewhere there',
    //             'city' => 'Pasay City',
    //             'state' => 'NCR',
    //             'country' => 'PH',
    //             'postal_code' => '1300',
    //         ],
    //         'name' => 'Sherwin de Jesus',
    //         'email' => 'sirking1991@gmail.com',
    //         'phone' => '09204759976'
    //     ],
    // ]);    

    // dump($paymentMethod);

    $paymentIntent = Paymongo::paymentIntent()->create([
        'amount' => 100,
        'payment_method_allowed' => [
            'card'
        ],
        'payment_method_options' => [
            'card' => [
                'request_three_d_secure' => 'automatic'
            ]
        ],
        'description' => 'This is a test payment intent',
        'statement_descriptor' => 'LUIGEL STORE',
        'currency' => "PHP",
    ]);

    dump($paymentIntent);

    // // Attached the payment method to the payment intent
    // $successfulPayment = $paymentIntent->attach($paymentMethod->{'id'});

    // dump($successfulPayment);
});