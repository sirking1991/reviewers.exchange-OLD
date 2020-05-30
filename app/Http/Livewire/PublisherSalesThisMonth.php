<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PublisherSalesThisMonth extends Component
{
    public $salesThisMonth = 0;

    public function render()
    {

        $add = DB::table('transactions')
                    ->whereIn('type', ['sales','sales-refund'])
                    ->whereMonth('created_at', date('n'))
                    ->where('user_id', Auth()->user()->id)
                    ->sum('add');
        $sub = DB::table('transactions')
                    ->whereIn('type', ['sales','sales-refund'])
                    ->whereMonth('created_at', date('n'))
                    ->where('user_id', Auth()->user()->id)
                    ->sum('sub');
        $this->salesThisMonth = $add - $sub;

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
