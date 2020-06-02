<div class="card shadow-sm bg-white rounded">
    <div class="card-header">
        {{ count($lists) }} entries
        <div class="float-right">
            <input onclick="openLearningMaterialDetail(-1)" type="button" class="btn btn-sm btn-secondary" value="New entry">
        </div>            
    </div>
    <div class="card-body">
        <div class="list-group lists">
            @foreach($lists as $i => $entry)
                <button onclick="openLearningMaterialDetail({{ $i }})" type="button" class="list-group-item list-group-item-action">
                    {{ $entry->title }}
                </button> 
            @endforeach               
        </div>            
    </div>
</div>

<div class="modal fade" id="learningMaterialModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="false" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
            &nbsp;
            <div class="float-right">
                <button type="button" class="btn btn-sm btn-danger" id='deleteLearningMaterialBtn' onclick="deleteSelectedLearningMaterial()">Delete</button>
                <button type="button" class="btn btn-sm btn-success" id='saveLearningMaterialBtn' onclick="saveLearningMaterial()">Save</button>
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
        <div class="modal-body">
            <form name="learningMaterialDetail">
                <div class="row">
                    <div class="col-md-12">
                        {!! Form::label('title', 'Title:', ['class' => 'control-label']) !!}
                        {!! Form::text('title', '' , ['class' => 'form-control']) !!}                                                
                    </div>
                </div>                
                <div class="row">
                    <div class="col-md-12">
                        {!! Form::label('content', 'Content:', ['class' => 'control-label']) !!}
                        {!! Form::textarea('content', '' , ['class' => 'form-control wysiwyg', 'rows'=>'30']) !!}                        
                    </div>
                </div>                                
            </form>
        </div>      
      </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script type="text/javascript">
    var learningMaterialLists  = {!! $lists !!};
    var selectedLearningMaterialIndex;
    var selectedLearningMaterial;  

    document.addEventListener("DOMContentLoaded", function() 
    {
        contentEditor = tinymce.init({
            selector: '#content',
            plugins: 'casechange linkchecker autolink lists checklist media mediaembed pageembed powerpaste table advtable tinymcespellchecker',
            toolbar_mode: 'floating',
            skin: 'bootstrap',
            forced_root_block : '',
            force_br_newlines : true,
            force_p_newlines : false,
            height: 600,           
            setup: editor => {
                // Apply the focus effect
                editor.on("init", () => {
                editor.getContainer().style.transition =
                    "border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out";
                });
                editor.on("focus", () => {
                (editor.getContainer().style.boxShadow =
                    "0 0 0 .2rem rgba(0, 123, 255, .25)"),
                    (editor.getContainer().style.borderColor = "#80bdff");
                });
                editor.on("blur", () => {
                (editor.getContainer().style.boxShadow = ""),
                    (editor.getContainer().style.borderColor = "");
                });
            }            
        });

        // Prevent Bootstrap dialog from blocking focusin
        // https://www.tiny.cloud/blog/bootstrap-wysiwyg-editor/
        $(document).on("focusin", function(e) {
            if (
                $(e.target).closest(
                ".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root"
                ).length
            ) {
                e.stopImmediatePropagation();
            }
        });    
    });

    function loadLM()
    {        
        var html = '';
        for (let i = 0; i < learningMaterialLists.length; i++) {
            html = html +  `
                <button onclick="openLearningMaterialDetail(` + i + `)" type="button" class="list-group-item list-group-item-action">
                    ` + learningMaterialLists[i].title + `
                </button>
            `;                
        };
        $('div.lists').html(html);
    }

    function openLearningMaterialDetail(id)
    {
        $('div.modal-backdrop').remove();
        
        $('#learningMaterialModal').modal('show');
        
        selectedLearningMaterialIndex = id;

        $('#learningMaterialModal input[name=title]').val('')
        $('#learningMaterialModal textarea[name=content]').val('')
        tinymce.get('content').setContent('');
        
        selectedLearningMaterial = learningMaterialLists[id];
        if(undefined!=selectedLearningMaterial) {
            $('#learningMaterialModal input[name=title]').val(selectedLearningMaterial.title);
            $('#learningMaterialModal textarea[name=content]').val(selectedLearningMaterial.content);
            tinymce.get('content').setContent(selectedLearningMaterial.content);
        } else {
            selectedLearningMaterial = {'title':'', 'content':''};
        }
        
        $('#learningMaterialModal input[name=title]').focus();
    }

    function saveLearningMaterial()
    {
        // save learning material to server
        var learningMaterialId = undefined!=selectedLearningMaterial.id ? selectedLearningMaterial.id : 0;
        $('#saveLearningMaterialBtn').html("Saving...");
        $('#saveLearningMaterialBtn').addClass('disabled');

        var formData = new FormData();        
        formData.append('title', $('#learningMaterialModal  input[name=title]').val());
        formData.append('content', tinymce.get('content').getContent());

        $.ajax({
            url: '/publisher/reviewers/{{ $reviewerId }}/learning-material/' + learningMaterialId,
            method: 'POST',
            processData: false,
            contentType: false,
            data: formData,
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}                 
        }).then(function(data){
            learningMaterialLists = data;

            $('#saveLearningMaterialBtn').html("Save");
            $('#saveLearningMaterialBtn').removeClass('disabled');            
            $('#learningMaterialModal').modal('hide');            
            
            loadLM();

        }).catch(function(error){            
            $('#saveLearningMaterialBtn').html("Save");
            $('#saveLearningMaterialBtn').removeClass('disabled');              
            $('#learningMaterialModal').modal('hide');
            console.log(error);
        });
        
    }

    // Delete selectedQuestion
    function deleteSelectedLearningMaterial()
    {
        if(!confirm('Are you sure you want to delete this learning material?')) return;

        $('#deleteLearningMaterialBtn').html("Deleting...");
        $('#deleteLearningMaterialBtn').addClass('disabled');

        $.ajax({
            url: '/publisher/reviewers/{{ $reviewerId }}/learning-material/' + selectedLearningMaterial.id,
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}                 
        }).then(function(data){
            learningMaterialLists = data;

            $('#deleteLearningMaterialBtn').html("Delete");
            $('#deleteLearningMaterialBtn').removeClass('disabled');            
            $('#learningMaterialModal').modal('hide');
            
            loadLM();            
        });                
    }    
</script>

