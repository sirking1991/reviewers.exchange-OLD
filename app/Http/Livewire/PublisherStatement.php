<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PublisherStatement extends Component
{
    public function render()
    {
        return <<<'blade'
        @extends('layouts.app')

        @section('content')
            <div class="container">
                <div class='card'>
                    <div class='card-header bg-primary text-white'>
                        Statement of account
                        <button class="btn btn-sm btn-primary float-right" onclick="window.open('/publisher/reviewers/')"><i class="fas fa-plus"></i>Filter</button>
                    </div>
                    <div class='card-body'>

                    </div>
                </div>
            </div>
        @endsection
        blade;
    }
}
