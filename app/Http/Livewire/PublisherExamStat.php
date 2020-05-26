<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PublisherExamStat extends Component
{
    public function render()
    {
        return <<<'blade'
        <div class="card" style='margin-top:10px;'>
            <div class="card-header">
                Practice exam statistics
            </div>
            <div class="card-body">
                <div class="alert alert-light" role="alert"">
                    Accounting for non accountant
                    <br/>
                    <div class="text-muted">
                        <span>Bought by 999 people. 700 people took practice exam. On average got 89% score.</span>
                    </div>
                </div>
                <div class="alert alert-light" role="alert"">
                    Corporate law 
                    <br/>
                    <div class="text-muted">
                        <span>Bought by 900 people. 798 people took practice exam. On average got 76% score.</span>
                    </div>
                </div>
                <div class="alert alert-light" role="alert"">
                    Auditing Practice  
                    <br/>
                    <div class="text-muted">
                        <span>Bought by 891 people. 751 people took practice exam. On average got 91% score.</span>
                    </div>
                </div>
            </div>
        </div>
        blade;
    }
}
