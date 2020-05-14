<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ReviewerStatsComponent extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return <<<'blade'

        <div class="row justify-content-center">
            <div class="col-md">
                <div class="card">
                    <div class="card-header">Your stats</div>
                    <div class="card-body">
                        &nbsp;
                    </div>
                </div>
            </div>
        </div>

        blade;
            }
}
