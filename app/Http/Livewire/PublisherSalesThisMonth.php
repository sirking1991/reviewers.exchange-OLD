<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PublisherSalesThisMonth extends Component
{
    public $salesThisMonth = 0;

    public function render()
    {
        $this->salesThisMonth = DB::table('reviewer_purchases')
                    ->where('status', 'success')
                    ->whereMonth('created_at', date('n'))
                    ->whereRaw("reviewer_id IN (SELECT id FROM reviewers WHERE user_id=" . Auth()->user()->id . ")")
                    ->sum('amount');

        return <<<'blade'
            <div class="card shadow-sm rounded-lg">
                <div class="card-header text-center">
                    Sales this Month
                </div>
                <div class="card-body text-center">
                    <h1>{{ number_format($salesThisMonth,2) }}</h1>
                </div>
            </div>
        blade;
    }
}
