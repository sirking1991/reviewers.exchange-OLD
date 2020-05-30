<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // if we're not in localhost, force use of https
        if (false === strpos(url()->full(), 'localhost') && false === strpos(url()->full(), '127.0.0.1') ) {
            URL::forceScheme('https');
        }
    }
}
