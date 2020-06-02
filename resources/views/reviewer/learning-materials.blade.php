@extends('layouts.app')

@section('content')    
    <div class="container">
        
        <div class="card">
            <div class="card-header">
                <select name="title" class='form-control'>
                    @foreach($learningMaterials as $i =>$lm)
                    <option value='{{ $lm->id }}'>{{ $lm->title }}</option>
                    @endforeach     
                </select>                
            </div>
            <div class="card-body"></div>
        </div>
       
    </div>
@endsection

@section('scripts')
    <script>

        var learningMaterials = {!! $learningMaterials !!};
        var selectedId = {{ $selectedId }};

        document.addEventListener("DOMContentLoaded", function() {
            display(0==selectedId ? learningMaterials[0].id : selectedId);    // display initial lm

            $('select[name=title]').on('change', function(e){
                display($(this).val())
            });
        });

        function display(id) {
            for (let i = 0; i < learningMaterials.length; i++) {
                const lm = learningMaterials[i];
                if(lm.id==id) {
                    selectedId = id;
                    // $('div.card div.card-header').html(lm.title);
                    $('div.card div.card-body').html(lm.content);
                    break;
                }
            }
        }

    </script>

@endsection