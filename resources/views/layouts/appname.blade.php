@if ('publisher' == Auth::user()->type)
    @if ('' !== Auth::user()->display_name)
        {{ Auth::user()->display_name }}
    @else 
        {{ config('app.name', 'Laravel') }}
    @endif
@else
{{ config('app.name', 'Laravel') }}
@endif