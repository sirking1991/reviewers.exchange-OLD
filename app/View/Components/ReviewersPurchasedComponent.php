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
        $this->reviewersPurchased = \App\ReviewerPurchase::where('user_id', Auth()->user()->id)
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

        <div class="modal fade" id="practiceExamModal" tabindex="-1" role="dialog" aria-labelledby="practiceExamModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class='modal-header hidden'>
                        <span class='title'></span>
                        <div class="float-right">
                            <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Stop practice exam</button>
                        </div>                        
                    </div>
                    <div class="modal-body">
                        <h3>Setting up exam questionnaires, pls wait...</h3>
                    </div>      
                    <div class='modal-footer hidden'>
                        <div class="float-right">                        
                            <button type="button" class="btn btn-sm btn-secondary prev-question-btn" onclick='prevQuestion()'>Previous question</button>
                            <button type="button" class="btn btn-sm btn-primary next-question-btn" onclick='nextQuestion()'>Next question</button>
                            <button type="button" class="btn btn-sm btn-success submit-answers-btn" onclick='submitAnswers()'>Submit answers</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>        

        <script>
            var reviewers = {!! $reviewersPurchased !!}
            var selectedReviewerPurchased;            

            document.addEventListener("DOMContentLoaded", function() 
            {

            });

            function openPurchasedReviewerDialog(index) 
            {
                var rp = this.reviewers[index];
                selectedReviewerId = rp.reviewer_id;

                selectedReviewerPurchased = this.reviewers[index];
                $('#reviewerPurchasedModal p.reviewer-title').html(selectedReviewerPurchased.reviewer.name);
                $('#reviewerPurchasedModal').modal('show');
            }

            function confirmStartExam()
            {
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
                            openExam();
                        }
                    }
                });
            }

            const selectedAnswerClass = 'list-group-item-success';

            var examData;
            var selectedReviewerId = 0;
            var currentQuestionnaireIndex = 0;
            var currentQuestion;
            
            var examHtml = `
                <div class='row'>
                    <div class='col-md-12 questionnaire-group'>Questionnaire group</div>
                    <div class='col-md-12 question'>Question</div>
                    <div class='col-md-12' style='margin-top:10px;'>
                        <div class='no_correct_answers text-muted'></div>
                        <div class="list-group answers"></div>
                    </div>
                </div>
            `;

            function openExam()
            {
                $('#practiceExamModal').modal({backdrop: 'static', keyboard: false}); // prevent modal from closing when click outside
                // get exam
                axios.get('http://localhost:8000/generateExam/' + selectedReviewerId)
                    .then(function(resp){
                        examData = resp.data;
                        $('#practiceExamModal .modal-header').removeClass('hidden');
                        $('#practiceExamModal .modal-footer').removeClass('hidden');                        
                        
                        currentQuestionnaireIndex = 0;
                        currentQuestion = undefined;

                        displayQuestion();
                    })
                    .catch(function (error) {
                        // handle error
                        console.log(error);
                    });              
            }
            
            function displayQuestion()
            {
                currentQuestion = examData.questionnaire[currentQuestionnaireIndex];

                $('#practiceExamModal .modal-body').html(examHtml);

                if (null != currentQuestion.questionnaire_group) {
                    $('#practiceExamModal .modal-body .questionnaire-group').show();
                    $('#practiceExamModal .modal-body .questionnaire-group').html(currentQuestion.questionnaire_group.content);
                } else {
                    $('#practiceExamModal .modal-body .questionnaire-group').hide();
                }

                $('#practiceExamModal .modal-header .title').html( 'Question #' + (currentQuestionnaireIndex + 1) + ' of ' + examData.questionnaire.length );

                $('#practiceExamModal .modal-body .question').html(currentQuestion.question);

                if ('yes'==currentQuestion.randomly_display_answers) shuffle(currentQuestion.answers);

                $('#practiceExamModal .modal-body .answers button').remove();                
                for(var i=0; i<currentQuestion.answers.length; i++) {
                    var answer = currentQuestion.answers[i];
                    var selectedClass = (undefined!=answer.selected) ? selectedAnswerClass : '';  // lets mark the answer selected/unselected                    
                    $('#practiceExamModal .modal-body .answers').append(`
                        <button type="button" class="list-group-item list-group-item-action ${selectedClass} answer_index_${i}" onclick="answerClick(${answer.id}, ${i})">
                            ${answer.answer}
                        </button>
                    `)                    
                }

                $('#practiceExamModal .modal-body .no_correct_answers').html(`<i>Choose only <strong>${currentQuestion.correct_answer_count}</strong> correct answer</i>`);
                
                setButtons();
            }

            function answerClick(answerId, answerIndex){
                var answer = currentQuestion.answers[answerIndex];
                var isSelected = undefined != answer.selected;

                // check if selected answer is less or equal to currentQuestion.correct_answer_count
                var selectedAnswer = 0;
                for (var i=0; i<currentQuestion.answers.length; i++) {
                    if (undefined!=currentQuestion.answers[i].selected) selectedAnswer++;
                }
                if (selectedAnswer==currentQuestion.correct_answer_count && !isSelected) {
                    bootbox.alert({
                        centerVertical: true,
                        backdrop: true,
                        message: `You can only select ${currentQuestion.correct_answer_count} answer.<br/>You must unselect if you want to change your answer`
                    });
                    return;
                }

                // mark the answer button
                if (!isSelected)
                    $(`#practiceExamModal .modal-body .answers .answer_index_${answerIndex}`).addClass(selectedAnswerClass);
                else 
                    $(`#practiceExamModal .modal-body .answers .answer_index_${answerIndex}`).removeClass(selectedAnswerClass);

                // set the answer data
                for (var i=0; i<currentQuestion.answers.length; i++) {
                    if (answerId==currentQuestion.answers[i].id) {
                        currentQuestion.answers[i].selected = isSelected ? undefined : 'yes';
                        break;
                    }
                }
            }

            function setButtons()
            {
                
                $('#practiceExamModal .modal-footer .submit-answers-btn').hide();

                if (0==currentQuestionnaireIndex) {
                    $('#practiceExamModal .modal-footer .prev-question-btn').hide();
                } else {
                    $('#practiceExamModal .modal-footer .prev-question-btn').show();
                }

                if (examData.questionnaire.length == (currentQuestionnaireIndex+1)) {
                    $('#practiceExamModal .modal-footer .next-question-btn').hide();
                    $('#practiceExamModal .modal-footer .submit-answers-btn').show();
                } else {
                    $('#practiceExamModal .modal-footer .next-question-btn').show();
                }

            }

            function prevQuestion()
            {
                currentQuestionnaireIndex--;
                displayQuestion();                          
            }

            function nextQuestion()
            {
                currentQuestionnaireIndex++;
                displayQuestion();
            }

            function shuffle(array) 
            {
                for (let i = array.length - 1; i > 0; i--) {
                  let j = Math.floor(Math.random() * (i + 1));
                  [array[i], array[j]] = [array[j], array[i]];
                }
            }
        </script>
        
        blade;
    }
}
