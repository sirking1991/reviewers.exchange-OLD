<div>
    <div class="card shadow-sm bg-white rounded">
        <div class="card-header">
            Questionnaires ({{ count($questionnaires) }} questions)
            <div class="float-right">
                <input onclick=" openQuestionDetail(-1)" type="button" class="btn btn-sm btn-success" value="New questionnaire">
            </div>            
        </div>
        <div class="card-body">
            <div class="list-group">
                @foreach ($questionnaires as $question)
                    <button onclick="openQuestionDetail({{ $question->id }})" type="button" class="list-group-item list-group-item-action">
                        {{ $question->question }}
                    </button>
                @endforeach
              </div>            
        </div>
    </div>
</div>

<div class="modal fade" id="questionModal" tabindex="-1" role="dialog" aria-labelledby="questionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="questionModalLabel">Question</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>


<script>
    function openQuestionDetail(id){
        console.log(id);
        $('#questionModal').modal('show');
    }
</script>