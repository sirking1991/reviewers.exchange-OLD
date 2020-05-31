@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md">
            <div class="card">
                <div class="card-header bg-primary text-white">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class='col-md-4'>
                            @livewire('publisher-sales-this-month')
                        </div>
                        <div class='col-md-4'>
                            @livewire('publisher-sales-this-year')
                        </div>
                        <div class='col-md-4'>
                            @livewire('publisher-account-balance')
                        </div>
                    </div>

                    @livewire('publisher-best-seller')    

                    @livewire('publisher-exam-stat')
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
