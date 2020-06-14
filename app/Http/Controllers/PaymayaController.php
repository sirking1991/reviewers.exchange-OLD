<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Aceraven777\PayMaya\PayMayaSDK;
use Aceraven777\PayMaya\API\Webhook;
use Aceraven777\PayMaya\API\Customization;
use Aceraven777\PayMaya\API\Checkout;
use Aceraven777\PayMaya\API\VoidPayment;
use Aceraven777\PayMaya\API\RefundPayment;
use Aceraven777\PayMaya\Model\Refund\Amount;

class PaymayaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        PayMayaSDK::getInstance()->initCheckout(
            env('PAYMAYA_PUBLIC_API_KEY'), 
            env('PAYMAYA_SECRET_API_KEY'), 
            (env('production') ? 'PRODUCTION' : 'SANDBOX')
        );
    }

    public function setupWebhooks($url)
    {
        $successWebhook = new Webhook();
        $successWebhook->name = Webhook::CHECKOUT_SUCCESS;
        $successWebhook->callbackUrl = $url . '/paymaya/callback/success';
        $result = $successWebhook->register();
        Log::info('paymaya success webhook setup', ['result'=>$result]);
    
        $failureWebhook = new Webhook();
        $failureWebhook->name = Webhook::CHECKOUT_FAILURE;
        $failureWebhook->callbackUrl = $url . '/paymaya/callback/error';
        $result = $failureWebhook->register();
        Log::info('paymaya failure webhook setup', ['result'=>$result]);
    
        $dropoutWebhook = new Webhook();
        $dropoutWebhook->name = Webhook::CHECKOUT_DROPOUT;
        $dropoutWebhook->callbackUrl = $url . '/paymaya/callback/dropout';
        $result = $dropoutWebhook->register();
        Log::info('paymaya dropout webhook setup', ['result'=>$result]);

        $this->customizeMerchantPage();
    }
    
    public function clearWebhooks()
    {
        $webhooks = Webhook::retrieve();
        foreach ($webhooks as $webhook) {
            $webhook->delete();
        }
    }  

    public function listWebhooks()
    {
        return Webhook::retrieve();
    }
    
    public function callback(Request $request, $status)
    {
        Log::info('paymaya callback', ['status'=>$status, 'request'=>$request->all()]);

        $transaction_id = $request->get('id');
        if (! $transaction_id) {
            return ['status' => false, 'message' => 'Transaction Id Missing'];
        }
        
        $itemCheckout = new Checkout();
        $itemCheckout->id = $transaction_id;
    
        $checkout = $itemCheckout->retrieve();
    
        if ($checkout === false) {
            $error = $itemCheckout::getError();
            return redirect()->back()->withErrors(['message' => $error['message']]);
        }

        // find the gateway_trans_id and update
        $reviewerPurchase = \App\ReviewerPurchase::where('gateway_trans_id', $itemCheckout->id)->first();
        if(!$reviewerPurchase) {
            Log::error('paymaya callback: unrecognized gateway transaction id', ['id', $itemCheckout->id, 'raw_data'=>json_encode($checkout)]);
            return response()->json('Unrecognized gateway transaction id', 404);
        }

        $reviewerPurchase->status = $status;
        $reviewerPurchase->raw_response_data = json_encode($checkout);
        $reviewerPurchase->save();

        // add publisher transaction
        // \App\Transaction::create([
        //     'reviewer_purchase_id' => $reviewerPurchase->id,
        //     'user_id' => $reviewerPurchase->reviewer()->user_id,
        //     'description' => 'Someone bought ' . $reviewerPurchase->reviewer()->name,
        //     'add' => $reviewerPurchase->amount
        // ]);        
    
        return $reviewerPurchase->reviewer();
    }      

    public function customizeMerchantPage()
    {
    
        $shopCustomization = new Customization();
        $shopCustomization->get();

        $shopCustomization->logoUrl = env('AWS_S3_URL') . 'common/reviewers.exchange-logo.png';
        $shopCustomization->iconUrl = env('AWS_S3_URL') . 'common/reviewers.exchange-logo.png';
        $shopCustomization->appleTouchIconUrl = env('AWS_S3_URL') . 'common/reviewers.exchange-logo.png';
        $shopCustomization->customTitle = env('APP_NAME');
        $shopCustomization->colorScheme = '#000000';
    
        $shopCustomization->set();

        Log::debug('Paymaya customiseMerchantPage', ['customization'=>$shopCustomization, 'logo_url'=>env('AWS_S3_URL') . 'common/reviewers.exchange-logo.png']);        
    }
    
    public function checkout($user, $item, $itemAmount, $reference)
    {
        // Checkout
        $itemCheckout = new Checkout();
        $itemCheckout->buyer = $user->buyerInfo();
        $itemCheckout->items = array($item);
        $itemCheckout->totalAmount = $itemAmount;
        $itemCheckout->requestReferenceNumber = $reference;
        $itemCheckout->redirectUrl = array(
            "success" => url('paymaya/redirectUrl/success/' . $reference),
            "failure" => url('paymaya/redirectUrl/failure/' . $reference),
            "cancel" => url('paymaya/redirectUrl/cancel/' . $reference),
        );
        
        if ($itemCheckout->execute() === false) {            
            $error = $itemCheckout::getError();
            Log::error('paymaya error:',['error'=>$error]);
            return redirect()->back()->withErrors(['message' => $error['message']??'Unknown error']);
        }
    
        if ($itemCheckout->retrieve() === false) {
            $error = $itemCheckout::getError();
            Log::error('paymaya error:',['error'=>$error]);
            return redirect()->back()->withErrors(['message' => $error['message']??'Unknown error']);
        }
    
        return $itemCheckout;
    }  

    public function redirect(Request $request, $status, $reference)
    {
        Log::info('paymaya redirect', ['status'=>$status, 'request'=>$request->all()]);

        return redirect('home');
    }
    
    public function voidPayment($checkoutId)
    {

        $voidPayment = new VoidPayment;
        $voidPayment->checkoutId = $checkoutId;
        $voidPayment->reason = 'The item is out of stock.';
    
        $response = $voidPayment->execute();
    
        if ($response === false) {
            $error = $voidPayment::getError();
            return redirect()->back()->withErrors(['message' => $error['message']]);
        }
    
        return $response;
    }    

    public function refundPayment($checkoutId)
    {
        $refundAmount = new Amount();
        $refundAmount->currency = "PHP";
        $refundAmount->value = 200.22;
    
        $refundPayment = new RefundPayment;
        $refundPayment->checkoutId = $checkoutId;
        $refundPayment->reason = 'The item is out of stock.';
        $refundPayment->amount = $refundAmount;
    
        $response = $refundPayment->execute();
    
        if ($response === false) {
            $error = $refundPayment::getError();
            return redirect()->back()->withErrors(['message' => $error['message']]);
        }
    
        return $response;
    }   
    
    public function retrieveRefunds($checkoutId)
    {
        $refundPayment = new RefundPayment;
        $refundPayment->checkoutId = $checkoutId;
        
        $refunds = $refundPayment->retrieve();
    
        if ($refunds === false) {
            $error = $refundPayment::getError();
            return redirect()->back()->withErrors(['message' => $error['message']]);
        }
    
        return $refunds;
    }
    
    public function retrieveRefundInfo($checkoutId, $refundId)
    {

        $refundPayment = new RefundPayment;
        $refundPayment->checkoutId = $checkoutId;
        $refundPayment->refundId = $refundId;
    
        $refund = $refundPayment->retrieveInfo();
    
        if ($refund === false) {
            $error = $refundPayment::getError();
            return redirect()->back()->withErrors(['message' => $error['message']]);
        }
    
        return $refund;
    }    
}
