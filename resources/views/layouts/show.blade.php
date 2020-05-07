@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">             
                @include('layouts.alert')   
                <div class="card shadow-sm bg-white rounded">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-4">
                                <h4>@yield('form-title')</h4>
                            </div>
                            <div class="col">
                                <div class="action-buttons float-right">
                                    @yield('action-buttons')
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        {!! Form::open(['id'=>'main']) !!}

                            @yield('card-body')

                        {!! Form::close() !!}
                    </div>

                    @hasSection('card-footer')
                        <div class="card-footer">
                            @yield('card-footer')                    
                        </div>
                    @endif
                </div>
                
            </div>
        </div>
    </div>
@endsection

<script>

    function deleteRecord() {
        if(!confirm('Are you sure you want to delete this record?')) return;

        $.ajax({
            url: "{{ url()->current() }}/delete",
            complete: function() { window.close() },
            error: function(err) { alert(err) }
        });
    }

</script>

@yield('scripts')
