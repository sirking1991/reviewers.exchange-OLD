<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PublisherBestSeller extends Component
{
    public function render()
    {
        return <<<'blade'
        <div class="card" style='margin-top:10px;'>
            <div class="card-header">
                Best seller
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item">
                        Accounting for non accountant
                        <div class="float-right">999 copies sold</div>
                    </li>   
                    <li class="list-group-item">
                        Corporate law
                        <div class="float-right">900 copies sold</div>
                    </li>   
                    <li class="list-group-item">
                        Auditing Practice
                        <div class="float-right">871 copies sold</div>
                    </li>   
                </ul>

            </div>
        </div>
        blade;
    }
}
