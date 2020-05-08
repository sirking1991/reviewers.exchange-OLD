<?php

namespace App\Http\Livewire;

use Livewire\Component;

class QuestionnaireList extends Component
{

    public $reviewerId;
    public $questionnaires;

    public function mount($reviewerId)
    {
        $this->reviewerId = $reviewerId;

        $this->questionnaires = \App\Questionnaire::where('reviewer_id', $this->reviewerId)->with('answers')->get();
    }

    public function render()
    {
        return view('livewire.questionnaire-list');
    }

    public function openQuestionDetail($id) {
        return view('livewire.questionnaire-detail');
    }
}
