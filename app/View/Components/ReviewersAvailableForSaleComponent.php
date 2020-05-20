<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\DB;

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
        // $this->reviewers = \App\Reviewer::all();
        $this->reviewers = DB::select("
                        SELECT * FROM reviewers 
                        WHERE status='active'
                            AND id NOT IN (SELECT reviewer_id 
                                            FROM reviewer_purchases 
                                            WHERE user_id=" . Auth()->user()->id . " AND status='success')");
                
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
                        @foreach($reviewers as $index => $r)
                        @php
                            $sellingPrice = $r->price +  ( env('PAYMAYA_ADDON_AMOUNT')  + (env('PAYMAYA_ADDON_RATE') * $r->price)  + (env('CONVINIENCE_FEE_RATE') * $r->price) );
                        @endphp
                        <div class="card">
                            <img src="{{ $r->cover_photo }}" class="card-img-top" alt="...">
                            <div class="card-body wrapword">
                                <p class='name'>{{ $r->name }}</p>
                                <p class='selling-price'>                                    
                                    <button class='btn btn-danger btn-block' onclick="buyNow({{ $r->id }})">Buy Now {{ number_format($sellingPrice, 2) }}</button>                                
                                </p> 
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div> 
    
        <div class="modal fade" id="reviewerDetailModal" tabindex="-1" role="dialog" aria-labelledby="reviewerDetailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="row">
                            <div class='col-md-5'>
                                <img class='cover-photo' src="https://via.placeholder.com/300">
                            </div>                             
                            <div clas='col-md'>
                                <p class='reviewer-title'></p>
                                <p class='reviewer-content'></p>                                
                            </div>                            
                        </div>
                    </div>      
                    <div class='modal-footer'>
                        <input type='button' onclick='buyNow()' class="btn btn-success btn-lg btn-block" value='Buy Now' /> 
                    </div>
                </div>
            </div>
        </div>



        <script>
            
            function buyNow(reviewerId)
            {
                window.location = '/buyReviewer/' + reviewerId;
            }
        </script>

        blade;
    }
}
