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

                    <div class="card-deck">
                        @livewire('publisher-sales-this-month')
                        @livewire('publisher-sales-this-year')
                        @livewire('publisher-account-balance')
                    </div>

                    @livewire('publisher-best-seller')    

                    @livewire('publisher-exam-stat')
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
