<?php

namespace Longtugame\Sso;

use Illuminate\Support\ServiceProvider;

class LongtuSsoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('longtusso', function ($app) {
            return new LongtuSso($app['config']);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // 发布配置文件
        $this->publishes([
            __DIR__ . '/config/sso.php' => config_path('sso.php'),
        ]);
    }
}
