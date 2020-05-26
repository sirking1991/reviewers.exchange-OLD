<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ReviewersForSale extends Component
{
    public $reviewers;
    public $search;
    public $category;

    public function render()
    {
        $this->reviewers = \App\Reviewer::where('status', 'active')
            ->when(3 <= strlen($this->search), function ($query) {
                return $query->where('name', 'like', "%$this->search%");
            })
            ->when('' != strlen($this->category), function ($query) {
                return $query->where('category', 'like', "%$this->category%");
            })
            ->whereRaw("id NOT IN (SELECT reviewer_id FROM reviewer_purchases WHERE user_id=" . Auth()->user()->id . " AND status='success')")
            ->get();

        return <<<'blade'
    
        <div class="row justify-content-center">
            <div class="col-md">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <div class='row'>
                            <div class='col-md-4'>
                                <h4>Reviewers available for sale</h4>
                            </div>
                            <div class='col-md-8'>
                                <div class='row'>
                                    <div class='col-md-6'>
                                        <input type='text' wire:model.debounce.250ms="search" class='form-control' placeholder='Search' />
                                    </div>
                                    <div class='col-md-6'>
                                        <select class='form-control'  wire:model="category" >
                                            <option value=''>All</option>
                                            <option value='accounting'>Accounting</option>
                                            <option value='engineering'>Engineering</option>
                                            <option value='civil-service'>Civil Service</option>
                                            <option value='entrance-exam'>Entrance Exams</option>
                                            <option value='nursing'>Nursing</option>
                                            <option value='medicine'>Medicine</option>
                                            <option value='education'>Education</option>
                                            <option value='law'>Law</option>
                                            <option value='others'>Others</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body horizontal-scroll">
                    @if(0 < count($reviewers))
                        @foreach($reviewers as $index => $r)
                        @php
                            $sellingPrice = 0; 
                            if ($r->price > 0) $sellingPrice = $r->price +  ( env('PAYMAYA_ADDON_AMOUNT')  + (env('PAYMAYA_ADDON_RATE') * $r->price)  + (env('CONVINIENCE_FEE_RATE') * $r->price) ); 
                        @endphp
                        <div class="card shadow-sm  rounded-lg">
                            <img src="{{ env('AWS_S3_URL') . $r->cover_photo }}" class="card-img-top" alt="...">
                            <div class="card-body wrapword">
                                <p class='name'>{{ $r->name }}</p>
                                <p class='selling-price'>                                    
                                    @if(0>=$sellingPrice)
                                        <button class='btn btn-danger btn-block' onclick="buyNow({{ $r->id }})">Get this for free!</button>
                                    @else
                                        <button class='btn btn-danger btn-block' onclick="buyNow({{ $r->id }})">Buy Now {{ number_format($sellingPrice, 2) }}</button>
                                    @endif
                                </p> 
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="alert alert-secondary" role="alert">
                            {{ __('No reviewers for this creteria was found') }}
                        </div>
                    @endif
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
