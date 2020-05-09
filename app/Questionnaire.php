<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model
{
    protected $guarded = [];
    
    public function answers() {
        return $this->hasMany('App\Answer', 'questionnaire_id');
    }    
}
