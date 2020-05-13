<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    {{-- <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet"> --}}
    <link href="https://fonts.googleapis.com/css2?family=Rubik&display=swap" rel="stylesheet"> 

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <livewire:styles>
</head>
<body>
    <div id="app">
        @include('layouts.nav')

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <livewire:scripts>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://cdn.tiny.cloud/1/1fvr1maemph5703p8fk3zt83n40pusom3b73e36gyx2e2vsw/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    
    @yield('scripts')

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
</body>
</html>
