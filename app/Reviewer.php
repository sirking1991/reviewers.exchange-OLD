<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reviewer extends Model
{
    
    public function questionnaires() {
        return $this->hasMany('App\Questionnaires', 'reviewer_id');
    } 
    
    public function questionnaireGroups() {
        return $this->hasMany('App\QuestionnaireGroups', 'reviewer_id');
    }     
}
