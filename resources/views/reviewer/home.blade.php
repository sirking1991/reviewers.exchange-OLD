@extends('layouts.app')

@section('content')
<div class="container">

    <x-reviewers-purchased-component />

    @livewire('reviewers-for-sale')
</div>
@endsection
