<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ReviewersPurchasedComponent extends Component
{
    public $reviewersPurchased;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->reviewersPurchased = \App\ReviewerPurchase::where('user_by', Auth()->user()->id)
            ->with('reviewer')
            ->get();
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
            <div class="col-md ">
                @if(0 < count($reviewersPurchased))
                    <div class="card">
                        <div class="card-header"><h4>Reviewers you've purchased</h4></div>
                        <div class="card-body horizontal-scroll">
                            @foreach($reviewersPurchased as $index => $rp)
                            <div class="card" onclick="openPurchasedReviewerDialog({{ $index }})">
                                <img src="https://via.placeholder.com/150" class="card-img-top" alt="...">
                                <div class="card-body wrapword">{{ $rp->reviewer->name }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="alert alert-secondary" role="alert">
                        {{ __('Buy reviewers availble below to start taking practice exams') }}
                    </div>
                @endif
            </div>
        </div>
        
        <div class="modal fade" id="reviewerPurchasedModal" tabindex="-1" role="dialog" aria-labelledby="reviewerPurchasedModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="row">
                            <div class='col-md-5'>
                                <img src="https://via.placeholder.com/300">
                            </div>                             
                            <div clas='col-md'>
                                <p class='reviewer-title'></p>
                                <p class='reviewer-content'></p>
                                <p class='stats'>
                                    Questions answered: <span class='questions-answered'>121</span><br/>
                                    Correcly answered: <span class='correctly-answered'>89</span><br/>
                                    Incorrecly answered: <span class='incorrectly-answered'>32</span> <button class='btn btn-sm btn-secondary'>View</button><br/>
                                    Average: <span class='average'>74%</span><br/>
                                </p>
                            </div>                            
                        </div>
                    </div>      
                    <div class='modal-footer'>
                        <input type='button' onclick='confirmStartExam()' class="btn btn-success btn-lg btn-block" value='Take Practice Exam' /> 
                    </div>
                </div>
            </div>
        </div>

        <script>
            var reviewers = {!! $reviewersPurchased !!}
            var selectedReviewerPurchased;
            document.addEventListener("DOMContentLoaded", function() 
            { });

            function openPurchasedReviewerDialog(index) {
                var rp = this.reviewers[index];
                selectedReviewerPurchased = this.reviewers[index];
                $('#reviewerPurchasedModal p.reviewer-title').html(selectedReviewerPurchased.reviewer.name);
                $('#reviewerPurchasedModal').modal('show');
            }

            function confirmStartExam(){
                bootbox.confirm({
                    centerVertical: true,
                    backdrop: true,
                    message: `<strong>Are you ready to practice exam?</strong><br/>` +
                             `<br/>There will be ${selectedReviewerPurchased.reviewer.questionnaires_to_display} questions for this practice exam.`+
                             (0!=selectedReviewerPurchased.reviewer.time_limit ? `<br/>The time limit will be ${selectedReviewerPurchased.reviewer.time_limit} minutes.` : ``),
                    buttons: {
                        confirm: {
                            label: 'Yes',
                            className: 'btn-success'
                        },
                        cancel: {
                            label: 'No',
                            className: 'btn-danger'
                        }
                    },
                    callback: function (result) {
                        if(result){
                            $('#reviewerPurchasedModal').modal('hide');
                        }
                    }
                });
            }
        </script>
        
        blade;
    }
}
