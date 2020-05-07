@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-sm bg-white rounded">
                    <div class="card-header">
                        <div class="row">
                            <div class="col">
                                <strong class='float-left'><h4>@yield('form-title')</h4></strong>
                            </div>
                            <div class="col">
                                <form class="form-inline" action="{{ url()->current() }}" method="GET">
                                <input class="form-control form-control-sm" value="{{ $search }}" name='search' type="search" placeholder="Search" aria-label="Search">&nbsp;
                                    <button class="btn btn-sm btn-secondary my-2 my-sm-0" type="submit"><i class="fas fa-search"></i>Search</button>
                                </form>
                            </div>
                            <div class="col">
                                @yield('action-buttons')
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <table class="table table-hover">
                            <thead class="thead-dark">
                            @yield('table-header')
                            </thead>
                            <tbody>
                                @yield('table-body')
                            </tbody>
                        </table>

                    </div>

                    <div class="card-footer">
                        @yield('table-footer')                    
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
