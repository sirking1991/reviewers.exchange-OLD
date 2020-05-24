@extends('layouts.list')

@section('form-title')
    Reviewers
@endsection

@section('action-buttons')
    <button class="btn btn-sm btn-primary float-right" onclick="window.open('/publisher/reviewers/')"><i class="fas fa-plus"></i>Create new</button>
@endsection

@section('table-header')
    <tr>
        <th scope="col">Name</th>        
        <th scope="col">Status</th>
        <th scope="col">Price</th>
        <th scope="col">&nbsp;</th>
    </tr>
@endsection

@section('table-body')
    @foreach ($list as $l)
    <tr class='clickable'  onclick="window.open('{{ '/publisher/reviewers/'.$l->id }}', '{{ $l->id }}')">
        <td>{{ $l->name }}</td>        
        <td>
            @if ('active'==$l->status)
                <span class="badge badge-pill badge-success">Active</span>
            @else
                <span class="badge badge-pill badge-secondary">Inactive</span>
            @endif
        </td>
        <td>{{ $l->price }}</td>
    </tr>
    @endforeach
@endsection

@section('table-footer')
    {{ $list->appends(['search' => $search ?? ''])->links() }}
@endsection

