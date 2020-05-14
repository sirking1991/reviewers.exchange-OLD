@extends('layouts.app')

@section('content')
<div class="container">
    {{-- <x-reviewer-stats-component /> --}}

    <x-reviewers-purchased-component />

    <x-reviewers-available-for-sale-component />  
</div>
@endsection
