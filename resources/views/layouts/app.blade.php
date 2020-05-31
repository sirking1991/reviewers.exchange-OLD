<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @include('layouts.appname')
    </title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">

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

    <!-- Scripts -->
    <livewire:scripts>
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

    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
        var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
        (function(){
        var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
        s1.async=true;
        s1.src='https://embed.tawk.to/5ed3ae0dc75cbf1769f0fecf/default';
        s1.charset='UTF-8';
        s1.setAttribute('crossorigin','*');
        s0.parentNode.insertBefore(s1,s0);
        })();
    </script>
    <!--End of Tawk.to Script-->
    
</body>
</html>
