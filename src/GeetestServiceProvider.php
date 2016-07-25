<?php namespace Germey\Geetest;

use Illuminate\Support\ServiceProvider;

class GeetestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('geetest', function () {
            return $this->app->make('Germey\Geetest\GeetestLib');
        });
    }
}
