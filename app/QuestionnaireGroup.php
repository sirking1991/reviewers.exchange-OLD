<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuestionnaireGroup extends Model
{
    protected $table = 'questionnaire_groups';

    protected $guarded = [];    

    public function questionnaires() {
        return $this->hasMany('App\Questionnaire', 'questionnaire_group_id');
    }     
}
