<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reviewer extends Model
{
    
    public function questionnaires() {
        return $this->hasMany('App\Questionnaires', 'reviewer_id');
    }    
}
