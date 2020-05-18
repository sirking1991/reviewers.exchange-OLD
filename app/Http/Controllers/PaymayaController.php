<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Aceraven777\PayMaya\PayMayaSDK;
use Aceraven777\PayMaya\API\Webhook;
use Aceraven777\PayMaya\API\Customization;
use Aceraven777\PayMaya\API\Checkout;
use Aceraven777\PayMaya\Model\Checkout\Item;
use App\Libraries\PayMaya\User as PayMayaUser;
use Aceraven777\PayMaya\Model\Checkout\ItemAmount;
use Aceraven777\PayMaya\Model\Checkout\ItemAmountDetails;
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
        $this->clearWebhooks();
    
        $successWebhook = new Webhook();
        $successWebhook->name = Webhook::CHECKOUT_SUCCESS;
        $successWebhook->callbackUrl = $url . '/paymaya/callback/success';
        $result = $successWebhook->register();
        Log::info('payamay success webhook setup', ['result'=>$result]);
    
        $failureWebhook = new Webhook();
        $failureWebhook->name = Webhook::CHECKOUT_FAILURE;
        $failureWebhook->callbackUrl = $url . '/paymaya/callback/error';
        $result = $failureWebhook->register();
        Log::info('payamay failure webhook setup', ['result'=>$result]);
    
        $dropoutWebhook = new Webhook();
        $dropoutWebhook->name = Webhook::CHECKOUT_DROPOUT;
        $dropoutWebhook->callbackUrl = $url . '/paymaya/callback/dropout';
        $result = $dropoutWebhook->register();
        Log::info('payamay dropout webhook setup', ['result'=>$result]);
    }
    
    private function clearWebhooks()
    {
        $webhooks = Webhook::retrieve();
        foreach ($webhooks as $webhook) {
            $webhook->delete();
        }
    }  
    
    public function callback(Request $request, $status)
    {
    
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
    
        return $checkout;
    }      

    public function customizeMerchantPage()
    {
    
        $shopCustomization = new Customization();
        $shopCustomization->get();
    
        $shopCustomization->logoUrl = asset('logo.jpg');
        $shopCustomization->iconUrl = asset('favicon.ico');
        $shopCustomization->appleTouchIconUrl = asset('favicon.ico');
        $shopCustomization->customTitle = 'PayMaya Payment Gateway';
        $shopCustomization->colorScheme = '#f3dc2a';
    
        $shopCustomization->set();
    }
    
    public function checkout()
    {

        $sample_item_name = 'Product 1';
        $sample_total_price = 1000.00;
    
        $sample_user_phone = '1234567';
        $sample_user_email = 'test@gmail.com';
        
        $sample_reference_number = 'order-' . date('YmdHis');
    
        // Item
        $itemAmountDetails = new ItemAmountDetails();
        $itemAmountDetails->tax = "0.00";
        $itemAmountDetails->subtotal = number_format($sample_total_price, 2, '.', '');
        $itemAmount = new ItemAmount();
        $itemAmount->currency = "PHP";
        $itemAmount->value = $itemAmountDetails->subtotal;
        $itemAmount->details = $itemAmountDetails;
        $item = new Item();
        $item->name = $sample_item_name;
        $item->amount = $itemAmount;
        $item->totalAmount = $itemAmount;
    
        // Checkout
        $itemCheckout = new Checkout();
    
        $user = new PayMayaUser();
        $user->contact->phone = $sample_user_phone;
        $user->contact->email = $sample_user_email;
    
        $itemCheckout->buyer = $user->buyerInfo();
        $itemCheckout->items = array($item);
        $itemCheckout->totalAmount = $itemAmount;
        $itemCheckout->requestReferenceNumber = $sample_reference_number;
        $itemCheckout->redirectUrl = array(
            "success" => url('paymaya/redirectUrl/success'),
            "failure" => url('paymaya/redirectUrl/failure'),
            "cancel" => url('paymaya/redirectUrl/cancel'),
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
    
        return redirect()->to($itemCheckout->url);
    }  

    public function redirect(Request $request, $status)
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
