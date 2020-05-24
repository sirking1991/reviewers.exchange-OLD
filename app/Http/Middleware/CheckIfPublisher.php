<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckIfPublisher
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if('publisher'!=Auth()->user()->type) {
            abort(403, __('Unauthorized action.'));
        } else {
            return $next($request);
        }

        
    }
}
