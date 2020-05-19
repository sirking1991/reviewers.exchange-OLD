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
            ->where('status', 'success')
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
                                    Questions answered: <span class='questions-answered'></span><br/>
                                    Correcly answered: <span class='correct_answers'></span><br/>
                                    Incorrecly answered: <span class='wrong_answers'></span><br/>
                                    Average: <span class='average'></span>%<br/>
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
                        <span class='title'></span> <div class='time-remaining'></div>
                        <div class="float-right">
                            <button type="button" class="btn  btn-danger" onclick='stopExam()'>Stop practice exam</button>
                        </div>                        
                    </div>
                    <div class="modal-body">
                        <h3>Setting up exam questionnaires, pls wait...</h3>
                    </div>      
                    <div class='modal-footer hidden'>
                        <div class="float-right action-buttons">                        
                            <button type="button" class="btn btn-sm btn-secondary prev-question-btn" onclick='prevQuestion()'>Previous question</button>
                            <button type="button" class="btn btn-sm btn-primary next-question-btn" onclick='nextQuestion()'>Next question</button>
                            <button type="button" class="btn btn-sm btn-success submit-answers-btn" onclick='submitAnswers()'>Submit answers</button>
                            <button type="button" class="btn btn-sm btn-secondary close-btn" data-dismiss="modal" >Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>        

        <script>
            var reviewers = {!! $reviewersPurchased !!}
            var selectedReviewerPurchased;            

            const selectedAnswerClass = 'list-group-item-success';

            var examData;
            var selectedReviewerId = 0;
            var currentQuestionnaireIndex = 0;
            var currentQuestion;
            var timer;
            var timeRemaining;
            
            var audioCoinUri = '/sounds/347174__davidsraba__coin-pickup-sound-v-0.wav';

            document.addEventListener("DOMContentLoaded", function() {});

            function openPurchasedReviewerDialog(index) 
            {
                var rp = this.reviewers[index];
                selectedReviewerId = rp.reviewer_id;

                axios.get('/userExamSummary/' + selectedReviewerId)
                    .then(function(resp){
                        console.log(resp.data);
                        const average = Math.round((resp.data.correct_answers / resp.data.questions) * 100);
                        // questions-answered
                        animateValue("#reviewerPurchasedModal .stats .questions-answered", 0, resp.data.questions, 2000, false);
                        // correct_answers
                        animateValue("#reviewerPurchasedModal .stats .correct_answers", 0, resp.data.correct_answers, 2000, false);
                        // wrong_answers
                        animateValue("#reviewerPurchasedModal .stats .wrong_answers", 0, resp.data.wrong_answers, 2000, false);
                        // average                        
                        animateValue("#reviewerPurchasedModal .stats .average", 0, average, 2000, false);
                    })
                    .catch(function(error){console.log(error)});

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

            
            var examHtml = `
                <div class='row'>
                    <div class='col-md-12 questionnaire-group'>Questionnaire group</div>
                    <div class='col-md-12 question'>Question</div>
                    <div class='col-md-12' style='margin-top:10px;'>
                        <div class='nmbr_correct_answers text-muted'></div>
                        <div class="list-group answers"></div>
                    </div>
                </div>
            `;

            var examSummaryHtml = `
                <div class='row p-3'>
                    <div class='score' style='font-size:xx-large'></div>
                </div>
                <div class='row p-3 text-muted'>
                    Here are the questions that you didn't answer correctly
                </div>
                <div class='row p-3 wrong_answers'>
                    <div class='col-md-12>                        
                        <div class="accordion" id="accordionAnswers">
                            <div class="card">
                                <div class="card-header" id="headingOne">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        Collapsible Group Item #1
                                        </button>
                                    </h2>
                                </div>

                                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionAnswers">
                                    <div class="card-body">
                                        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
                                    </div>
                                </div>
                            </div>                                                                                                                
                        </div>
                    </div>
                </div>
            `;

            function openExam()
            {
                $('#practiceExamModal').modal({backdrop: 'static', keyboard: false}); // prevent modal from closing when click outside
                // get exam
                axios.get('/generateExam/' + selectedReviewerId)
                    .then(function(resp){
                        examData = resp.data;
                        $('#practiceExamModal .modal-header').removeClass('hidden');
                        $('#practiceExamModal .modal-footer').removeClass('hidden');                        
                        
                        currentQuestionnaireIndex = 0;
                        currentQuestion = undefined;

                        timeRemaining = parseInt(examData.reviewer.time_limit) * 60 + 1;
                        timer = setInterval(function(){timerTick()}, 1000);
                        $('#practiceExamModal .modal-header .time-remaining').removeClass('pulsate');
                        
                        $('#practiceExamModal .modal-footer .close-btn').hide();

                        displayQuestion();
                    })
                    .catch(function (error) {
                        // handle error
                        console.log(error);
                    });              
            }

            function stopExam() 
            {
                clearInterval(timer);
                $('#practiceExamModal').modal('hide');
            }

            function submitAnswers()
            {   
                clearInterval(timer);
                $(`#practiceExamModal .modal-header`).hide();
                $(`#practiceExamModal .modal-body`).html(examSummaryHtml);
                
                $('#practiceExamModal .modal-footer .prev-question-btn').hide();
                $('#practiceExamModal .modal-footer .next-question-btn').hide();
                $('#practiceExamModal .modal-footer .submit-answers-btn').hide();
                $('#practiceExamModal .modal-footer .close-btn').show();

                $(`#practiceExamModal .modal-body .wrong_answers .accordion`).html(``);
                
                // process answers                
                var correctAnswers = 0;
                var wrongAnswers = 0;
                var wrongAnsweredQuestions = [];
                for(var q=0; q<examData.questionnaire.length; q++){
                    var question = examData.questionnaire[q];
                    // assume correct, unless any of the answer did not match
                    var isCorrect = true;
                    for(var ai=0; ai<question.answers.length; ai++){
                        if('yes'==question.answers[ai].is_correct && undefined==question.answers[ai].selected){
                            isCorrect = false;
                            break;
                        }
                        if('no'==question.answers[ai].is_correct && 'yes'==question.answers[ai].selected){
                            isCorrect = false;
                            break;
                        }
                    }
                    question.correctly_answered = isCorrect ? 'yes' : 'no';
                    if(isCorrect) {
                        correctAnswers++;
                    } else {
                        wrongAnswers++;
                        wrongAnsweredQuestions.push(question);
                        $(`#practiceExamModal .modal-body .wrong_answers .accordion`).append(`
                            <div class="card">
                                <div class="card-header" id="heading_${question.id}">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse_${question.id}" aria-expanded="true" aria-controls="collapse_${question.id}">
                                        Question #${q+1}
                                        </button>
                                    </h2>
                                </div>

                                <div id="collapse_${question.id}" class="collapse show" aria-labelledby="heading_${question.id}" data-parent="#accordionAnswers">
                                    <div class="card-body">
                                        ${question.question}
                                    </div>
                                </div>
                            </div>                        
                        `);
                    }
                }
                
                axios.post('/saveExamResult', examData)
                    .then(function(resp){
                        // 
                    })
                    .catch(function (error) {
                        // handle error
                        console.log(error);
                    });

                
                $(`#practiceExamModal .modal-body .score`).html(`You got <span id='totalScore'></span> correct answer${correctAnswers>1?'s':''} out of ${examData.questionnaire.length} questions`);

                animateValue("#totalScore", 0, correctAnswers, 3000);
            }

            function timerTick(){
                timeRemaining--;

                if (120>=timeRemaining && !$('#practiceExamModal .modal-header .time-remaining').hasClass('pulsate')) {
                    $('#practiceExamModal .modal-header .time-remaining').addClass('pulsate');
                }

                if (0>=timeRemaining) {                      
                    clearInterval(timer);
                    bootbox.alert({
                        centerVertical: true,
                        backdrop: true,
                        message: `Time is up!`,
                        callback: function () {
                            submitAnswers();
                        }
                    });                    
                }

                $('#practiceExamModal .modal-header .time-remaining').html( secondsToHms(timeRemaining) );
            }
            
            function displayQuestion()
            {
                currentQuestion = examData.questionnaire[currentQuestionnaireIndex];

                $('#practiceExamModal .modal-body').html(examHtml);
                $(`#practiceExamModal .modal-header`).show();

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

                $('#practiceExamModal .modal-body .nmbr_correct_answers').html(`<i>Choose only <strong>${currentQuestion.correct_answer_count}</strong> correct answer</i>`);
                
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

            function secondsToHms(d) 
            {
                d = Number(d);
                var h = Math.floor(d / 3600);
                var m = Math.floor(d % 3600 / 60);
                var s = Math.floor(d % 3600 % 60);
            
                var hDisplay = h > 0 ? h + (h == 1 ? " hour " : " hours ") : "";
                var mDisplay = m > 0 ? m + (m == 1 ? " minute " : " minutes ") : "";
                var sDisplay = s > 0 ? s + (s == 1 ? " second" : " seconds") : "";
                return hDisplay + mDisplay + sDisplay; 
            }           
            
            function animateValue(el, start, end, duration, playSound=true) {
                var range = end - start;
                var current = start;
                var increment = end > start? 1 : -1;
                var stepTime = Math.abs(Math.floor(duration / range));
                var sounds = [];
                var timer = setInterval(function() {                    
                    current += increment;
                    if (playSound){
                        sounds[current] = new Audio(audioCoinUri);
                        sounds[current].play();
                    }
                    $(el).html(current);
                    if (current == end) {                        
                        clearInterval(timer);
                    }
                }, stepTime);

            }            
        </script>
        
        blade;
    }
}
