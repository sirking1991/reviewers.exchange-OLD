@extends('layouts.show')

@php
    $status = $record->status ?? 'inactive';    
    $id = $record->id ?? 0;
@endphp

@section('form-title')
    Reviewers @if( 0 == $id) "New"  @endif
@endsection

@section('action-buttons')
    @if (0 != $id)
        <button class="btn btn-sm btn-danger" onclick="deleteRecord()"><i class="fas fa-trash"></i>Delete</button>                
    @endif
    <button class="btn btn-sm btn-success" onclick="$('form#main').submit()"><i class="fas fa-save"></i>{{ empty($record->ide) ? 'Save' : 'Update' }}</button>
    <button class="btn btn-sm btn-secondary" onclick="window.close()"><i class="fas fa-times"></i>Close</button>
@endsection

@section('card-body')
    @if (!empty($record->id))
        @method('PUT')    
    @endif
    
    <input type="hidden" name="id" value="{{$record->id ?? ''}}">
    
    <div class="form-group">
        <div class="row">
            <div class="col-md-10">
                {!! Form::label('name', 'Name:', ['class' => 'control-label']) !!}
                {!! Form::text('name', $record->name ?? '', ['class' => 'form-control']) !!}
            </div>
            <div class="col-md-2">
                {!! Form::label('status', 'Status:', ['class' => 'control-label']) !!}
                {!! Form::select('status', ['inactive' => 'Inactive', 'active' => 'Active'], $record->status ?? 'inactive', ['class' => 'form-control']) !!}
            </div>
        </div>

    </div>

    <div class="form-group">
        <div class="row">
            <div class="col-md-4">
                {!! Form::label('questionnaires_to_display', 'Questionnaires to display:', ['class' => 'control-label']) !!}
                {!! Form::select('questionnaires_to_display', ['10' => '10 questions', 
                                                '20' => '20 questions',
                                                '30' => '30 questions',
                                                '40' => '40 questions',
                                                '50' => '50 questions',
                                                '60' => '60 questions',], $record->questionnaires_to_display ?? '30', ['class' => 'form-control']) !!}
            </div>
            <div class="col-md-4">
                {!! Form::label('time_limit', 'Time limit:', ['class' => 'control-label']) !!}
                {!! Form::select('time_limit', ['10' => '10 minutes', 
                                                '20' => '20 minutes',
                                                '30' => '30 minutes',
                                                '40' => '40 minutes',
                                                '50' => '50 minutes',
                                                '60' => '60 minutes',], $record->time_limit ?? '30', ['class' => 'form-control']    ) !!}
            </div>
            <div class="col-md-4">
                {!! Form::label('price', 'Price:', ['class' => 'control-label']) !!}
                {!! Form::text('price', $record->price ?? '30', ['class' => 'form-control']) !!} 
            </div>
        </div>
        
        
        <div class="row" style="margin-top:20px;">
            <div class="col-12">
                @if (0 != $id)
                    <livewire:questionnaire-list :reviewer_id="$record->id"/>
                    {{-- <x-questionnaire-list :reviewer_id="$record->id"> --}}
                @else
                    <span class='font-italic text-muted'>The questionnaires will appear after you've save this reviewer.</span>
                @endif
            </div>
        </div>

        
    </div>    
@endsection


@section('scripts')
    <script type="text/javascript">      

        document.addEventListener('DOMContentLoaded', function(){ 

        }, false);

    </script>
@endsection