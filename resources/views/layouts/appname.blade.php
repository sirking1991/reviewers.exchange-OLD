@auth
    @if ('publisher' == Auth::user()->type && '' != Auth::user()->display_name)
        {{ Auth::user()->display_name }}
    @else
        {{ config('app.name', 'Laravel') }}
    @endif
@endauth

@guest
    {{ config('app.name', 'Laravel') }}
@endguest
