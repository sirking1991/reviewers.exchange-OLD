<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reviewer extends Model
{
    public $paymentGatewayFee = 0;
    public $serviceFee = 0;
    public $otherFees = 0;

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function($model){
            if (0 < $model->price) {
                $gateway = env('PAYMENT_GATEWAY', 'paymongo');
                $model->paymentGatewayFee = env($gateway . '_ADDON_AMOUNT') + (env($gateway . '_ADDON_RATE') * $model->price);
                $model->serviceFee = env('SERVICE_FEE_RATE') * $model->price;
                $model->otherFees = 0;
            }
        }); 
    }

    public function publisher(){
        return $this->belongsTo('\App\User', 'user_id', 'id');
    }

    public function questionnaires()
    {
        return $this->hasMany('App\Questionnaires', 'reviewer_id');
    }

    public function questionnaireGroups()
    {
        return $this->hasMany('App\QuestionnaireGroups', 'reviewer_id');
    }

    public function reviewerPurchases()
    {
        return $this->hasMany('App\ReviewerPurchases');
    }

    public function sellingPrice()
    {
        return $this->price + $this->paymentGatewayFee + $this->serviceFee + $this->otherFees;
    }
}
