<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Reviewer;

class PublisherReviewerList extends Component
{   
    public $list;

    public function render()
    {
        $this->list = Reviewer::where('user_id', Auth()->user()->id)
                    ->orderBy('name')
                    ->get();

        return <<<'blade'
        @extends('layouts.app')

        @section('content')
            <div class="container">
                <div class='card'>
                    <div class='card-header bg-primary text-white'>
                        Reviewers
                        <button class="btn btn-sm btn-primary float-right" onclick="window.open('/publisher/reviewers/')"><i class="fas fa-plus"></i>Create new</button>
                    </div>
                    <div class='card-body'>
                        <div class="list-group">
                        @foreach ($list as $l)
                            <a href="/publisher/reviewers/{{ $l->id }}" target='reviewer_{{ $l->id }}' class="list-group-item list-group-item-action">
                                {{ $l->name }}
                                <span class='float-right'>
                                    @if ('active'==$l->status)
                                        <span class="badge badge-pill badge-success">Active</span>
                                    @else
                                        <span class="badge badge-pill badge-secondary">Inactive</span>
                                    @endif
                                    @if (0>=$l->price)
                                        <span class="badge badge-pill badge-danger">Free</span>
                                    @else
                                        <span class="badge badge-pill badge-primary">{{ number_format($l->price) }}</span>
                                    @endif
                                </span>
                            </a>
                        @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endsection
        blade;
    }
}
