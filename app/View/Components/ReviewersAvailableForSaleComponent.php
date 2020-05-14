<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ReviewersAvailableForSaleComponent extends Component
{
    public $reviewers;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->reviewers = \App\Reviewer::all();
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
                    <div class="card-header"><h4>Reviewers available for sale</h4></div>
                    <div class="card-body horizontal-scroll">
                        @foreach($reviewers as $r)
                        <div class="card">
                            <img src="https://via.placeholder.com/150" class="card-img-top" alt="...">
                            <div class="card-body wrapword">
                                <p>{{ $r->name }}</p>
                                <p class='selling-price'>{{ $r->price }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div> 
    
        blade;
    }
}
