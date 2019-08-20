<?php

namespace Siaoynli\AliCloud\Mts;

use Illuminate\Support\ServiceProvider;

class LaravelAliMtsServerProvider extends ServiceProvider
{
    protected $defer = true;
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('mts', function ($app) {
            return new Mts($app['config']);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }

    public function provides()
    {
        return ['mts'];
    }

}
