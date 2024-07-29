<?php

namespace PrevailExcel\Flick;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/*
 * This file is part of the Laravel Flick package.
 *
 * (c) Prevail Ejimadu <prevailexcellent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class FlickServiceProvider extends ServiceProvider
{

    /*
    * Indicates if loading of the provider is deferred.
    *
    * @var bool
    */
    protected $defer = false;

    /**
     * Publishes all the config file this package needs to function
     */
    public function boot()
    {
        $config = realpath(__DIR__ . '/../utils/config/flick.php');

        $this->publishes([
            $config => config_path('flick.php')
        ]);
        $this->mergeConfigFrom(
            __DIR__ . '/../utils/config/flick.php',
            'flick'
        );
        if (File::exists(__DIR__ . '/../utils/helpers/flick.php')) {
            require __DIR__ . '/../utils/helpers/flick.php';
        }

        /**
         * @param  array|string $controller
         * @param  string|null  $class
         * */
        Route::macro('flick_callback', function ($controller, string $class = 'handleGatewayCallback') {
            return Route::any('flick/callback', [$controller, $class])->name("flick.lara.callback");
        });
        Route::macro('flick_webhook', function ($controller, string $class = 'handleWebhook') {
            return Route::post('flick/webhook', [$controller, $class])->name("flick.lara.webhook");
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind('laravel-flick', function () {
            return new Flick;
        });
    }

    /**
     * Get the services provided by the provider
     * @return array
     */
    public function provides()
    {
        return ['laravel-flick'];
    }
}
