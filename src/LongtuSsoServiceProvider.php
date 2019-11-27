<?php

namespace Longtugme\Sso;

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
        // 发布配置文件
        $this->publishes([
            __DIR__ . '/config/sso.php' => config_path('sso.php'),
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton('sso', function ($app) {
            return new LongtuSso($app['config']);
        });
    }
}
