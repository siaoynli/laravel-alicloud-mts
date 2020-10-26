<?php

namespace Siaoynli\AliCloud\Mts;

use Illuminate\Contracts\Support\DeferrableProvider;
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
            return new Sms($app['config']);
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
            __DIR__ . '/../config/alicloud-mts.php' => config_path('alicloud-mts.php'),
        ]);
    }

    public function provides()
    {
        return ['mts'];
    }

}
