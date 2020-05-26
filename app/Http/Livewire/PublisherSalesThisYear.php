<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PublisherSalesThisYear extends Component
{
    public $salesThisYear = 0;
    public function render()
    {
        $this->salesThisYear = DB::table('reviewer_purchases')
                    ->where('status', 'success')
                    ->whereYear('created_at', date('Y'))
                    ->whereRaw("reviewer_id IN (SELECT id FROM reviewers WHERE user_id=" . Auth()->user()->id . ")")
                    ->sum('amount');

        return <<<'blade'
        <div class="card shadow-sm rounded-lg">
            <div class="card-header text-center">
                Sales this Year
            </div>
            <div class="card-body text-center">
                <h1>{{ number_format($salesThisYear,2) }}</h1>
            </div>
        </div>
        blade;
    }
}
