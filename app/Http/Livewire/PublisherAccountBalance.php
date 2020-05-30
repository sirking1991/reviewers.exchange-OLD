<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PublisherAccountBalance extends Component
{
    public $balance;
    public function render()
    {
        $add = DB::table('transactions')->where('user_id', Auth()->user()->id)->sum('add');
        $sub = DB::table('transactions')->where('user_id', Auth()->user()->id)->sum('sub');

        $this->balance = $add - $sub;
        return <<<'blade'
        <div class="card shadow-sm rounded-lg">
            <div class="card-header text-center">
                Account balance
            </div>
            <div class="card-body text-center">
                <h1>{{ number_format($balance) }}</h1>
                <button class="btn btn-sm btn-primary">Withdraw fund</button>
            </div>
        </div>
        blade;
    }
}
