<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PublisherSalesThisYear extends Component
{
    public $salesThisYear = 0;
    public function render()
    {
         $add = DB::table('transactions')
                    ->whereIn('type', ['sales','sales-refund'])
                    ->whereYear('created_at', date('Y'))
                    ->where('user_id', Auth()->user()->id)
                    ->sum('add');
        $sub = DB::table('transactions')
                    ->whereIn('type', ['sales','sales-refund'])
                    ->whereYear('created_at', date('Y'))
                    ->where('user_id', Auth()->user()->id)
                    ->sum('sub');
        $this->salesThisYear = $add - $sub;                    

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
