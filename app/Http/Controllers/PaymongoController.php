<?php

namespace App\Http\Controllers;

use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;

use Luigel\Paymongo\Facades\Paymongo;

use App\Reviewer;
use App\ReviewerPurchase;

class PaymongoController extends Controller
{

    public function buyReviewer($reviewerId)
    {
        // check if user has no yet purchase this reviewer
        $reviewer = Reviewer::find($reviewerId);
        if (!$reviewer) return redirect('home');

        $reference = Auth()->user()->id . '-' . date('YmdHis');

        $clientKey = 'free';

        if(0<$reviewer->price) {            
            $paymentIntent = Paymongo::paymentIntent()->create([
                'amount' => $reviewer->sellingPrice(),
                'payment_method_allowed' => [
                    'card'
                ],
                'payment_method_options' => [
                    'card' => [
                        'request_three_d_secure' => 'automatic'
                    ]
                ],
                'description' => $reviewer->name,
                'statement_descriptor' => env('APP_NAME'),
                'currency' => "PHP",
            ]);   
            $clientKey = $paymentIntent->getClientKey();      
        }

        // create new order
        ReviewerPurchase::create([
            'reference' => $reference,
            'gateway_trans_id' => $clientKey,
            'reviewer_id' => $reviewerId,
            'user_id' => Auth()->user()->id,
            'amount' => $reviewer->price,
            'payment_gateway_fee' => $reviewer->paymentGatewayFee,
            'service_fee' => $reviewer->serviceFee,
            'other_fees' => $reviewer->otherFees,
            'total' =>  $reviewer->sellingPrice(),
            'status' => 0==$reviewer->price ? 'success' : 'pending',
            'raw_request_data' => json_encode($paymentIntent),
            'raw_response_data' => '',
        ]);

        if('free'==$clientKey)
            return redirect()->to('/home');
        else
            return response()->json($clientKey);
    }

    public function webhook(Request $request)
    {
        Log::info('Paymongo webhook', ['request'=>$request->all()]);
    }

}