<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PublisherAccountBalance extends Component
{
    public function render()
    {
        return <<<'blade'
        <div class="card shadow-sm rounded-lg">
            <div class="card-header text-center">
                Account balance
            </div>
            <div class="card-body text-center">
                <h1>999.00</h1>
                <button class="btn btn-sm btn-primary">Withdraw fund</button>
            </div>
        </div>
        blade;
    }
}
