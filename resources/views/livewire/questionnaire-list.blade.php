<div>
    <div class="card shadow-sm bg-white rounded">
        <div class="card-header">
            Questionnaires ({{ count($questionnaires) }} questions)
            <div class="float-right">
                <input onclick=" openQuestionDetail(-1)" type="button" class="btn btn-sm btn-secondary" value="New questionnaire">
            </div>            
        </div>
        <div class="card-body">
            <div class="list-group" id='questionList'></div>            
        </div>
    </div>
</div>

<div class="modal fade" id="questionModal" tabindex="-1" role="dialog" aria-labelledby="questionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      {{-- <div class="modal-header">
        <h5 class="modal-title" id="questionModalLabel">Question</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div> --}}
      <div class="modal-body">        
            <div class="row">
                <div class="col-md-12">
                    {!! Form::label('Question', 'Question:', ['class' => 'control-label']) !!}
                    {!! Form::textarea('question', '' , ['class' => 'form-control', 'rows'=>'3']) !!}                
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    {!! Form::label('randomly_display_answers', 'Randomly display answers:', ['class' => 'control-label']) !!}
                    {!! Form::select('randomly_display_answers', ['yes' => 'Yes', 'no' => 'No'], '', ['class' => 'form-control']) !!}
                </div>      
                <div class="col-md-6">
                    {!! Form::label('difficulty_level', 'Difficulty level:', ['class' => 'control-label']) !!}
                    {!! Form::select('difficulty_level', ['easy' => 'Easy', 'normal' => 'Normal', 'hard' => 'Hard'], '', ['class' => 'form-control']) !!}                    
                </div>
            </div>  
            <div class="row" style='margin-top:10px;'>
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            Answers
                            <div class="float-right">
                                <input onclick=" openAnswerDetail(-1)" type="button" class="btn btn-sm btn-secondary" value="New answer">
                            </div>                             
                        </div>
                        <div class="card-body">
                            <div class="list-group" id='answerList'></div>
                        </div>
                    </div>
                </div>
            </div>                  
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-danger" onclick="deleteSelectedQuestion()">Delete</button>
        <button type="button" class="btn btn-sm btn-success" onclick="saveQuestion()">Save</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="answerModal" tabindex="-1" role="dialog" aria-labelledby="answerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        {{-- <div class="modal-header">
          <h5 class="modal-title" id="answerModalLabel">Answer</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div> --}}
        <div class="modal-body">
            <form name="questionDetail">
                <div class="row">
                    <div class="col-md-12">
                        {!! Form::label('answer', 'Answer:', ['class' => 'control-label']) !!}
                        {!! Form::textarea('answer', '' , ['class' => 'form-control', 'rows'=>'2']) !!}                        
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        {!! Form::label('answer', 'Explanation:', ['class' => 'control-label']) !!}
                        {!! Form::textarea('answer_explanation', '' , ['class' => 'form-control', 'rows'=>'2']) !!}                                                
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        {!! Form::label('is_correct', 'This is a correct answer:', ['class' => 'control-label']) !!}
                        {!! Form::select('is_correct', ['yes' => 'Yes', 'no' => 'No'], '', ['class' => 'form-control']) !!}
                    </div>
                </div>                
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="deleteSelectedAnswer()">Delete</button>
            <button type="button" class="btn btn-sm btn-success" onclick="saveAnswer()">Save</button>
          </div>        
      </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    var questionnaires = {!! $questionnaires !!};
    var selectedQuestionIndex;
    var selectedQuestion;
    var selectedAnswerIndex;
    var selectedAnswer;

    document.addEventListener("DOMContentLoaded", function() {
        loadQuestionnaires();
    });

    function loadQuestionnaires(){        
        var html = '';
        for (let i = 0; i < questionnaires.length; i++) {
            const question = questionnaires[i];
            html = html +  `
                <button onclick="openQuestionDetail(` + i + `)" type="button" class="list-group-item list-group-item-action">
                    ` + question.question + `
                </button>
            `;                
        };
        $('div#questionList').html(html);
    }

    function openQuestionDetail(id){
        selectedQuestionIndex = id;
        selectedAnswerIndex = -1;
        selectedAnswer = undefined
        $('#questionModal textarea[name=question]').val('')
        $('#questionModal select[name=difficulty_level]').val('normal')
        $('#questionModal select[name=randomly_display_answers]').val('no')

        selectedQuestion = questionnaires[id];
        if(undefined!=selectedQuestion) {
            $('#questionModal textarea[name=question]').val(selectedQuestion.question)
            $('#questionModal select[name=difficulty_level]').val(selectedQuestion.difficulty_level)
            $('#questionModal select[name=randomly_display_answers]').val(selectedQuestion.randomly_display_answers)            
        } else {
            selectedQuestion = {'question':'', 'difficulty_level':'normal', 'randomly_display_answers':'no', 'answers':[]};
        }
        loadAnswers();        
        $('#questionModal').modal('show');
        $('#questionModal textarea[name=question]').focus();

    }

    function loadAnswers(){
        var html = '';
        for (let i = 0; i < selectedQuestion.answers.length; i++) {
            const answer = selectedQuestion.answers[i];
            html = html +  `
                <button onclick="openAnswerDetail(`+i+`)" 
                        type="button" 
                        class="list-group-item list-group-item-action ">` + 
                        ('yes'==answer.is_correct?'&#10004; ':'&#10060') + ` ` +answer.answer + 
                `</button>
            `;                
        };
        $('div#answerList').html(html);
    }

    function saveQuestion(){
        var data = {
            'question': $('#questionModal textarea[name=question]').val(),
            'difficulty_level': $('#questionModal select[name=difficulty_level]').val(),
            'randomly_display_answers':$('#questionModal select[name=randomly_display_answers]').val(),
            'answers': selectedQuestion.answers
        };
        if(-1==selectedQuestionIndex) {
            questionnaires.push(data);
        } else {
            questionnaires[selectedQuestionIndex] = data;
        }
        // save questionnaires to server
        var questionId = undefined!=selectedQuestion.id ? selectedQuestion.id : 0;
        $.ajax({
            url: '/admin/reviewers/{{ $reviewerId }}/question/' + questionId,
            method: 'POST',
            data: data,
            dataType: 'json',       
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}                 
        }).then(function(data){
            console.log(data);
            questionnaires = data;
            loadQuestionnaires();
            $('#questionModal').modal('hide');        
        });
        
    }

    // Delete selectedQuestion
    function deleteSelectedQuestion(){
        if(!confirm('Are you sure you want to delete this question?')) return;

        $.ajax({
            url: '/admin/reviewers/{{ $reviewerId }}/question/' + selectedQuestion.id,
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}                 
        }).then(function(data){
            console.log(data);
            questionnaires = data;
            loadQuestionnaires();
            $('#questionModal').modal('hide');
        });                
    }

    function openAnswerDetail(id){
        selectedAnswerIndex=id;
        $('#answerModal textarea[name=answer]').val('');
        $('#answerModal textarea[name=answer_explanation]').val('');
        $('#answerModal select[name=is_correct]').val('no')
        
        selectedAnswer = selectedQuestion.answers[id];
        if(undefined!=selectedAnswer){
            $('#answerModal textarea[name=answer]').val(selectedAnswer.answer);
            $('#answerModal textarea[name=answer_explanation]').val(selectedAnswer.answer_explanation);
            $('#answerModal select[name=is_correct]').val(selectedAnswer.is_correct)
        }
        $('#answerModal').modal('show');
        $('#answerModal textarea[name=answer]').focus();
    }

    function deleteSelectedAnswer(){
        selectedQuestion.answers.splice(selectedAnswerIndex,1);
        loadAnswers();
        $('#answerModal').modal('hide');
    }

    function saveAnswer(){
        var data = {
                'answer': $('#answerModal textarea[name=answer]').val(),
                'answer_explanation': $('#answerModal textarea[name=answer_explanation]').val(),
                'is_correct': $('#answerModal select[name=is_correct]').val(),
            };
        if(-1==selectedAnswerIndex) {
            // new answer
            selectedQuestion.answers.push(data);
        } else {
            // updating existing order
            selectedQuestion.answers[selectedAnswerIndex]=data;
        }
        
        $('#answerModal').modal('hide');
        loadAnswers();

    }    
</script>