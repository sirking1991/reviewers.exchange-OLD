<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\LearningMaterial;

class LearningmaterialList extends Component
{
    public $reviewerId;
    public $lists = [];

    public function mount($reviewerId)
    {
        $this->reviewerId = $reviewerId;

        $this->lists = LearningMaterial::where('reviewer_id', $this->reviewerId)->get();
    }

    public function render()
    {
        return view('livewire.learningmaterial-list');
    }
}
