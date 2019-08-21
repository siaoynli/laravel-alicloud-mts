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
        $this->publishes([
            __DIR__ . '/../config/alimts.php' => config_path('alimts.php'),
        ]);
    }

    public function provides()
    {
        return ['mts'];
    }

}
