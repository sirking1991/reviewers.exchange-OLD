<div>
    <div class="card shadow-sm bg-white rounded">
        <div class="card-header">
            Questionnaires ({{ count($questionnaires) }} questions)
            <div class="float-right">
                <input onclick="openQuestionnaireGroupList()" type="button" class="btn btn-sm btn-secondary" value="Questionnaire groups">
                <input onclick="openQuestionDetail(-1)" type="button" class="btn btn-sm btn-secondary" value="New questionnaire">
            </div>            
        </div>
        <div class="card-body">
            <div class="list-group" id='questionList'></div>            
        </div>
    </div>
</div>


<div class="modal fade" id="questionnaireGroupListModal" tabindex="-1" role="dialog" aria-labelledby="questionnaireGroupListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
          <div class="modal-body">
                <div class="card">
                    <div class="card-header">
                        Questionnaire groups
                        <div class="float-right">                            
                            <input onclick=" openQuestionnaireGroupDetail(-1)" type="button" class="btn btn-sm btn-secondary" value="New questionnaire group">
                            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                        </div>                             
                    </div>
                    <div class="card-body">
                        <div class="list-group" id='questionnaireGroupList'></div>
                    </div>                  
                </div>
          </div>
      </div>
    </div>
</div>


<div class="modal fade" id="questionnaireGroupModal" tabindex="-1" role="dialog" aria-labelledby="questionnaireGroupLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
            &nbsp;
            <div class="float-right">
                <button type="button" class="btn btn-sm btn-danger" id='deleteQuestionnaireGroupBtn' onclick="deleteSelectedQuestionnaireGroup()">Delete</button>
                <button type="button" class="btn btn-sm btn-success" id='saveQuestionnaireGroupBtn' onclick="saveQuestionnaireGroup()">Save</button>
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
        <div class="modal-body">
            <form name="questionnaireGroupDetail">
                <div class="row">
                    <div class="col-md-12">
                        {!! Form::label('name', 'Group name:', ['class' => 'control-label']) !!}
                        {!! Form::text('name', '' , ['class' => 'form-control']) !!}                        
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        {!! Form::label('groupContent', 'Content:', ['class' => 'control-label']) !!}
                        {!! Form::textarea('groupContent', '' , ['class' => 'form-control wysiwyg', 'rows'=>'2']) !!}                                                
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        {!! Form::label('randomly_display_questions', 'Randomly display questions:', ['class' => 'control-label']) !!}
                        {!! Form::select('randomly_display_questions', ['yes' => 'Yes', 'no' => 'No'], '', ['class' => 'form-control']) !!}
                    </div>
                </div>                
            </form>
        </div>       
      </div>
    </div>
</div>


<div class="modal fade" id="questionModal" tabindex="-1" role="dialog" aria-labelledby="questionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
            &nbsp;
            <div class="float-right">
                <button type="button" class="btn btn-sm btn-danger" id='deleteQuestionBtn'onclick="deleteSelectedQuestion()">Delete</button>
                <button type="button" class="btn btn-sm btn-success" id='saveQuestionBtn' onclick="saveQuestion()">Save</button>
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
      <div class="modal-body">        
            <div class="row">
                <div class="col-md-12">
                    {!! Form::label('questionnaire_group_id', 'Question group:', ['class' => 'control-label']) !!}
                    {!! Form::select('questionnaire_group_id', [], '', ['class' => 'form-control']) !!}                    
                </div>
            </div>          
            <div class="row">
                <div class="col-md-12">
                    {!! Form::label('Question', 'Question:', ['class' => 'control-label']) !!}
                    {!! Form::textarea('question', '' , ['class' => 'form-control wysiwyg', 'rows'=>'3']) !!}                
                </div>
            </div>
            <div class="row">
                <div class="col-md">
                    {!! Form::label('image', 'Image:', ['class' => 'control-label']) !!}
                    {!! Form::file('image', ['class' => 'form-control', 'accept' => 'image/*']) !!}          
                </div>
                <div class="col-md existingImg">
                    <label class="control-label">Existing image <input type='button' class="btn btn-sm btn-danger" onclick="removeQuestionImg()" value='Remove' /></label>
                    <br/>
                    <a href="#" target="question_img">
                        <img height="60px" src="https://via.placeholder.com/100">
                    </a>
                    <input type="hidden" name='remove_image' >
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
    </div>
  </div>
</div>


<div class="modal fade" id="answerModal" tabindex="-1" role="dialog" aria-labelledby="answerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
            &nbsp;
            <div class="float-right">
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteSelectedAnswer()">Delete</button>
                <button type="button" class="btn btn-sm btn-success" onclick="saveAnswer()">Save</button>
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
        <div class="modal-body">
            <form name="questionDetail">
                <div class="row">
                    <div class="col-md-12">
                        {!! Form::label('answer', 'Answer:', ['class' => 'control-label']) !!}
                        {!! Form::textarea('answer', '' , ['class' => 'form-control wysiwyg', 'rows'=>'2']) !!}                        
                    </div>
                </div>
                <div class="row">
                    <div class="col-md">
                        {!! Form::label('image', 'Image:', ['class' => 'control-label']) !!}
                        {!! Form::file('image', ['class' => 'form-control', 'accept' => 'image/*']) !!}          
                    </div>
                    <div class="col-md existingImg">
                        <label class="control-label">Existing image <input type='button' class="btn btn-sm btn-danger" onclick="removeAnswerImg()" value='Remove' /></label>
                        <br/>
                        <a href="#" target="question_img">
                            <img height="60px" src="https://via.placeholder.com/100">
                        </a>
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
      </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('scripts')
<script>
    var questionnaires = {!! $questionnaires !!};

    var questionnaireGroups = {!! $questionnaireGroups !!};
    var selectedQuestionnaireGroupIndex;
    var selectedQuestionnaireGroup;

    var selectedQuestionIndex;
    var selectedQuestion;    

    var selectedAnswerIndex;
    var selectedAnswer;

    document.addEventListener("DOMContentLoaded", function() 
    {
        loadQuestionnaires();
        loadQuestionnaireGroups();

        questionEditor = tinymce.init({
            selector: '.wysiwyg',
            plugins: 'casechange linkchecker autolink lists checklist media mediaembed pageembed powerpaste table advtable tinymcespellchecker',
            toolbar_mode: 'floating',
            forced_root_block : '',
            force_br_newlines : true,
            force_p_newlines : false,
        });        
       
    });

    function loadQuestionnaires()
    {        
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

    function openQuestionDetail(id)
    {
        $('#questionModal').modal('show');

        selectedQuestionIndex = id;
        selectedAnswerIndex = -1;
        selectedAnswer = undefined

        $('#questionModal select[name=questionnaire_group_id]').val('0')
        $('#questionModal textarea[name=question]').val('')
        tinymce.get('question').setContent('');
        $('#questionModal select[name=difficulty_level]').val('normal')
        $('#questionModal select[name=randomly_display_answers]').val('no')

        $('#questionModal input[name=image]').val('')   // reset image file
        $('#questionModal .modal-dialog .modal-content .modal-body .row .existingImg input[name=remove_image]').val('no');
        
        $('#questionModal .modal-dialog .modal-content .modal-body .row .existingImg').hide();
        
        selectedQuestion = questionnaires[id];
        if(undefined!=selectedQuestion) {
            $('#questionModal select[name=questionnaire_group_id]').val(selectedQuestion.questionnaire_group_id);
            $('#questionModal textarea[name=question]').val(selectedQuestion.question);
            tinymce.get('question').setContent(selectedQuestion.question);
            $('#questionModal select[name=difficulty_level]').val(selectedQuestion.difficulty_level);
            $('#questionModal select[name=randomly_display_answers]').val(selectedQuestion.randomly_display_answers);
            if('' != selectedQuestion.image) {
                $('#questionModal .modal-dialog .modal-content .modal-body .row .existingImg').show();
                $('#questionModal .modal-dialog .modal-content .modal-body .row .existingImg a').attr('href', '{!! env('AWS_S3_URL') !!}' + selectedQuestion.image);
                $('#questionModal .modal-dialog .modal-content .modal-body .row .existingImg a img').attr('src', '{!! env('AWS_S3_URL') !!}' + selectedQuestion.image)
            }
        } else {
            selectedQuestion = {'question':'', 'difficulty_level':'normal', 'randomly_display_answers':'no', 'answers':[]};
        }
        loadAnswers();        
        $('#questionModal textarea[name=question]').focus();

    }

    function removeQuestionImg() 
    {
        $('#questionModal .modal-dialog .modal-content .modal-body .row .existingImg input[name=remove_image]').val('yes');
        $('#questionModal .modal-dialog .modal-content .modal-body .row .existingImg').hide();
    }

    function loadAnswers()
    {
        var html = '';
        for (let i = 0; i < selectedQuestion.answers.length; i++) {
            const answer = selectedQuestion.answers[i];
            html = html +  `
                <button onclick="openAnswerDetail(`+i+`)" 
                        type='button' 
                        class='list-group-item list-group-item-action '>` + 
                        ('yes'==answer.is_correct ? '&#10004;' : '&#10060;') + ` ` + answer.answer + 
                        (''!=answer.image && undefined != answer.image ? 
                            `<img class='float-right' height='60px' src='{!! env('AWS_S3_URL') !!}`+answer.image+`'/>` 
                            : ``) 
                        +
                `</button>`;                
        };
        $('div#answerList').html(html);
    }

    function saveQuestion()
    {
        var data = {
            'questionnaire_group_id': $('#questionModal select[name=questionnaire_group_id]').val(),
            'question': tinymce.get('question').getContent(),
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
        $('#saveQuestionBtn').html("Saving...");
        $('#saveQuestionBtn').addClass('disabled');

        var formData = new FormData();        
        formData.append('questionnaire_group_id', $('#questionModal select[name=questionnaire_group_id]').val());
        formData.append('question', tinymce.get('question').getContent());
        formData.append('difficulty_level', $('#questionModal select[name=difficulty_level]').val());
        formData.append('randomly_display_answers', $('#questionModal select[name=randomly_display_answers]').val());
        formData.append('answers', JSON.stringify(selectedQuestion.answers));
        formData.append('image', $('#questionModal input[name=image]')[0].files[0])
        if('yes' == $('#questionModal .modal-dialog .modal-content .modal-body .row .existingImg input[name=remove_image]').val() ) {
            formData.append('remove_image', 'yes')
        }
        // attach answer images
        for (let i = 0; i < selectedQuestion.answers.length; i++) {
            const answer = selectedQuestion.answers[i];
            if ('' != answer.image_for_upload) {
                formData.append('answer_image_' + i, answer.image_for_upload);
            }            
        }
        $.ajax({
            url: '/admin/reviewers/{{ $reviewerId }}/question/' + questionId,
            method: 'POST',
            processData: false,
            contentType: false,
            data: formData,
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}                 
        }).then(function(data){
            questionnaires = data;
            loadQuestionnaires();
            $('#saveQuestionBtn').html("Save");
            $('#saveQuestionBtn').removeClass('disabled');            
            $('#questionModal').modal('hide');        
        });
        
    }

    // Delete selectedQuestion
    function deleteSelectedQuestion()
    {
        if(!confirm('Are you sure you want to delete this question?')) return;

        $('#deleteQuestionBtn').html("Deleting...");
        $('#deleteQuestionBtn').addClass('disabled');
        $.ajax({
            url: '/admin/reviewers/{{ $reviewerId }}/question/' + selectedQuestion.id,
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}                 
        }).then(function(data){
            questionnaires = data;
            loadQuestionnaires();
            $('#deleteQuestionBtn').html("Delete");
            $('#deleteQuestionBtn').removeClass('disabled');            
            $('#questionModal').modal('hide');
        });                
    }

    function openAnswerDetail(id)
    {
        selectedAnswerIndex=id;
        $('#answerModal textarea[name=answer]').val('');
        tinymce.get('answer').setContent('');
        $('#answerModal textarea[name=answer_explanation]').val('');
        $('#answerModal select[name=is_correct]').val('no')

        $('#answerModal input[name=image]').val('')   // reset image file
        $('#answerModal .modal-dialog .modal-content .modal-body .row .existingImg input[name=remove_image]').val('no');        
        
        $('#answerModal .modal-dialog .modal-content .modal-body .row .existingImg').hide();

        selectedAnswer = selectedQuestion.answers[id];
        if(undefined!=selectedAnswer){
            $('#answerModal textarea[name=answer]').val(selectedAnswer.answer);
            tinymce.get('answer').setContent(selectedAnswer.answer);
            $('#answerModal textarea[name=answer_explanation]').val(selectedAnswer.answer_explanation);
            $('#answerModal select[name=is_correct]').val(selectedAnswer.is_correct)
            if(undefined != selectedAnswer.image && '' != selectedAnswer.image) {
                $('#answerModal .modal-dialog .modal-content .modal-body .row .existingImg').show();
                $('#answerModal .modal-dialog .modal-content .modal-body .row .existingImg a').attr('href', '{!! env('AWS_S3_URL') !!}' + selectedAnswer.image);
                $('#answerModal .modal-dialog .modal-content .modal-body .row .existingImg a img').attr('src', '{!! env('AWS_S3_URL') !!}' + selectedAnswer.image)
            }            
        }
        $('#answerModal').modal('show');
        $('#answerModal textarea[name=answer]').focus();
    }

    function deleteSelectedAnswer()
    {
        selectedQuestion.answers.splice(selectedAnswerIndex,1);
        loadAnswers();
        $('#answerModal').modal('hide');
    }

    function removeAnswerImg() 
    {
        $('#answerModal .modal-dialog .modal-content .modal-body .row .existingImg').hide();        
        selectedAnswer.remove_image = 'yes';
    }

    function saveAnswer()
    {
        var data = undefined != selectedAnswer 
                    ? selectedAnswer 
                    : {answer:'', answer_explanation:'', is_correct:'no', remove_image:'no'};

        data.answer = tinymce.get('answer').getContent();
        data.answer_explanation = $('#answerModal textarea[name=answer_explanation]').val();
        data.is_correct = $('#answerModal select[name=is_correct]').val();              

        if (undefined != $('#answerModal input[name=image]')[0].files[0]) {
            data.image_for_upload = $('#answerModal input[name=image]')[0].files[0];
        }
        if (undefined != data.remove_image && 'yes' == data.remove_image) {
            data.remove_image = 'yes';
        }
        if (-1==selectedAnswerIndex) {
            // new answer
            selectedQuestion.answers.push(data);
        } else {
            // updating existing order
            selectedQuestion.answers[selectedAnswerIndex]=data;
        }        
        
        $('#answerModal').modal('hide');
        loadAnswers();

    }    

    function loadQuestionnaireGroups()
    {        
        var html = '';
        var options = `<option value='0'>None</option>`;
        for (let i = 0; i < questionnaireGroups.length; i++) {
            const group = questionnaireGroups[i];
            html = html +  `
                <button onclick="openQuestionnaireGroupDetail(` + i + `)" type="button" class="list-group-item list-group-item-action">
                    ` + group.name + `
                </button>
            `;                

            options = options + `
                <option value='` + group.id + `'>` + group.name + `</option>
            `;
        };
        $('div#questionnaireGroupList').html(html);

        // update questionGroup selection in questionnaireModal
        $('#questionnaire_group_id').html(options);


    }

    function openQuestionnaireGroupList() 
    {
        $('#questionnaireGroupListModal').modal('show');
    }

    function openQuestionnaireGroupDetail(id)
    {
        selectedQuestionnaireGroupIndex=id;
        $('#questionnaireGroupModal input[name=name]').val('');
        $('#questionnaireGroupModal textarea[name=groupContent]').val('');
        tinymce.get('groupContent').setContent('');
        $('#questionnaireGroupModal select[name=randomly_display_questions]').val('no')
        
        selectedQuestionnaireGroup = questionnaireGroups[id];
        
        if(undefined!=selectedQuestionnaireGroup){
            $('#questionnaireGroupModal input[name=name]').val(selectedQuestionnaireGroup.name);
            $('#questionnaireGroupModal textarea[name=groupContent]').val(selectedQuestionnaireGroup.content);
            tinymce.get('groupContent').setContent(selectedQuestionnaireGroup.content);
            $('#questionnaireGroupModal select[name=randomly_display_questions]').val(selectedQuestionnaireGroup.randomly_display_questions)
        }
        $('#questionnaireGroupModal').modal('show');
        $('#questionnaireGroupModal input[name=name]').focus();
    }   

    function saveQuestionnaireGroup() 
    {
        
        var id = -1 != selectedQuestionnaireGroupIndex ? selectedQuestionnaireGroup.id : 0;

        var data = {
            'id': id,
            'name': $('#questionnaireGroupModal input[name=name]').val(),
            'content': tinymce.get('groupContent').getContent(),
            'randomly_display_questions':$('#questionnaireGroupModal select[name=randomly_display_questions]').val(),
        };

        $('#saveQuestionnaireGroupBtn').html("Saving...");
        $('#saveQuestionnaireGroupBtn').addClass('disabled');
        $.ajax({
            url: '/admin/reviewers/{{ $reviewerId }}/questionnaire-group/' + id,
            method: 'POST',
            data: data,
            dataType: 'json',       
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}                 
        }).then(function(data){
            questionnaireGroups = data;
            loadQuestionnaireGroups();
            $('#saveQuestionnaireGroupBtn').html("Save");
            $('#saveQuestionnaireGroupBtn').removeClass('disabled');            
            $('#questionnaireGroupModal').modal('hide');        
        });        
    }
    
   function deleteSelectedQuestionnaireGroup() 
   {
        if(!confirm('Are you sure you want to delete this question?')) return;

        $('#deleteQuestionnaireGroupBtn').html("Deleting...");
        $('#deleteQuestionnaireGroupBtn').addClass('disabled');
        $.ajax({
            url: '/admin/reviewers/{{ $reviewerId }}/questionnaire-group/' + selectedQuestionnaireGroup.id,
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}                 
        }).then(function(data){
            questionnaireGroups = data;
            loadQuestionnaireGroups();
            $('#deleteQuestionnaireGroupBtn').html("Delete");
            $('#deleteQuestionnaireGroupBtn').removeClass('disabled');            
            $('#questionnaireGroupModal').modal('hide');
        });       
   }  
</script>

@endsection