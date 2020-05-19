<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckIfPaymaya
{
    /**
     * If caller is not from Paymaya, then reject
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if('admin'!=Auth()->user()->type) {
            abort(403, __('Unauthorized action.'));
        } else {
            return $next($request);
        }

        
    }
}
