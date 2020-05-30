<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewerPurchase extends Model
{
    protected $table = 'reviewer_purchases';

    protected $guarded = [];

    // public function user()
    // {
    //     return $this->belongsTo('App\User');
    // }

    public function reviewer()
    {
        return $this->belongsTo('App\Reviewer', 'reviewer_id', 'id');
    }

}
