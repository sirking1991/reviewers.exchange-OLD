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
                    <div class="card shadow-sm">
                        <div class="card-header"><h4>Reviewers you've purchased</h4></div>
                        <div class="card-body horizontal-scroll">
                            @foreach($reviewersPurchased as $index => $rp)
                                <div class="card shadow-sm rounded-lg" onclick="openPurchasedReviewerDialog({{ $index }})">
                                    <img src="{{ env('AWS_S3_URL') . $rp->reviewer->cover_photo }}" class="card-img-top" alt="...">
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
                        <div class="card">
                            <img class='cover-photo' src="https://via.placeholder.com/300">
                            <div class="card-body">
                                <p class='reviewer-title'></p>
                                <p class='reviewer-content'></p>
                                <p class='stats'>
                                    Questions answered: <span class='questions-answered'></span><br/>
                                    Correcly answered: <span class='correct_answers'></span><br/>
                                    Incorrecly answered: <span class='wrong_answers'></span><br/>
                                    Average: <span class='average'></span>%<br/>
                                </p>
                                <input type='button' onclick='confirmStartExam()' class="btn btn-success btn-lg btn-block" value='Take Practice Exam' /> 
                            </div> 
                        </div>
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

                $.get('/userExamSummary/' + selectedReviewerId)
                    .then(function(data){
                        console.log(data);
                        const average = Math.round((data.correct_answers / data.questions) * 100);
                        
                        // questions-answered
                        if(0==data.questions)
                            $("#reviewerPurchasedModal .stats .questions-answered").html('0');
                        else
                            animateValue("#reviewerPurchasedModal .stats .questions-answered", 0, data.questions, 2000, false);
                        
                        // correct_answers
                        if(0==data.correct_answers)
                            $("#reviewerPurchasedModal .stats .correct_answers").html('0');
                        else
                            animateValue("#reviewerPurchasedModal .stats .correct_answers", 0, data.correct_answers, 2000, false);
                        
                        // wrong_answers
                        if(0==data.wrong_answers)
                            $("#reviewerPurchasedModal .stats .wrong_answers").html('0');
                        else
                            animateValue("#reviewerPurchasedModal .stats .wrong_answers", 0, data.wrong_answers, 2000, false);
                        
                        // average
                        if(isNaN(average) || undefined==average || 0==average)   
                            $("#reviewerPurchasedModal .stats .average").html('0');                     
                        else
                            animateValue("#reviewerPurchasedModal .stats .average", 0, average, 2000, false);
                    })
                    .catch(function(e){console.log(e)});

                selectedReviewerPurchased = this.reviewers[index];
                $('#reviewerPurchasedModal p.reviewer-title').html(selectedReviewerPurchased.reviewer.name);
                $('#reviewerPurchasedModal img.cover-photo').attr('src', '{!! env('AWS_S3_URL') !!}' + selectedReviewerPurchased.reviewer.cover_photo);
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
                <div class='row p-3'>
                    <div class='text-muted wrongAnswerCount'></div>
                    <div class='col-md-12 wrong_answers'>                        
                    </div>
                </div>
            `;

            function openExam()
            {
                $('#practiceExamModal').modal({backdrop: 'static', keyboard: false}); // prevent modal from closing when click outside
                // get exam
                $.get('/generateExam/' + selectedReviewerId)
                    .then(function(data){
                        examData = data;
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

                $(`.wrong_answers .card`).remove();
                
                // process answers                
                var correctAnswers = 0;
                
                var wrongAnswers = 0;
                var wrongAnsweredQuestions = [];
                var wrongAnswersHtml = ``;             
                for(var q=0; q<examData.questionnaire.length; q++){
                    var question = examData.questionnaire[q];
                    // assume correct, unless any of the answer did not match
                    var isCorrect = true;
                    var correctAnswersList = [];
                    for(var ai=0; ai<question.answers.length; ai++){
                        if('yes'==question.answers[ai].is_correct) {
                            correctAnswersList.push(question.answers[ai]);
                        }
                        if('yes'==question.answers[ai].is_correct && undefined==question.answers[ai].selected){
                            isCorrect = false;
                        }
                        if('no'==question.answers[ai].is_correct && 'yes'==question.answers[ai].selected){
                            isCorrect = false;
                        }                        
                    }
                    question.correctly_answered = isCorrect ? 'yes' : 'no';
                    if(isCorrect) {
                        correctAnswers++;                        
                    } else {
                        wrongAnswers++;
                        wrongAnsweredQuestions.push(question);
                        wrongAnswersHtml += `
                            <div class="alert alert-danger" role="alert">
                                <strong class="alert-heading">${question.question}</strong>
                                <hr>
                                `;
                        wrongAnswersHtml += `<p class="mb-0"><li class='list-group'>`;
                        for(var x=0; x<correctAnswersList.length; x++){
                            var answer = correctAnswersList[x].answer;
                            if(''!=correctAnswersList[x].image) answer = "<img src='https://lares-reviewers.s3-ap-southeast-1.amazonaws.com/" + correctAnswersList[x].image + "' />"
                            wrongAnswersHtml += `<li class="list-group-item list-group-item-danger">${answer}</li>`;
                        }
                        wrongAnswersHtml += `</li></p>`;
                        wrongAnswersHtml += `</div>`;                        
                    }
                }
                $('.wrong_answers').html(wrongAnswersHtml);
                $('.wrongAnswerCount').html(0==wrongAnswers?`<h3>Perfect! You answered all the questions correctly</h3>`:`Here are the questions that you didn't answer correctly`);
                
                axios.post('/saveExamResult', examData)
                    .then(function(resp){
                        // 
                    })
                    .catch(function (error) {
                        // handle error
                        console.log(error);
                    });

                
                $(`#practiceExamModal .modal-body .score`).html(`You got <span id='totalScore'></span> correct answer${correctAnswers>1?'s':''} out of ${examData.questionnaire.length} questions`);

                if(0<correctAnswers)
                    animateValue("#totalScore", 0, correctAnswers, 3000);
                else 
                    $("#totalScore").html('0');
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
                    var content = '' != answer.image 
                                        ? "<img src='https://lares-reviewers.s3-ap-southeast-1.amazonaws.com/" + answer.image + "' />"
                                        : answer.answer;
                    $('#practiceExamModal .modal-body .answers').append(`
                        <button type="button" class="list-group-item list-group-item-action ${selectedClass} answer_index_${i}" onclick="answerClick(${answer.id}, ${i})">
                            ${content}
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
