<?php

namespace skygdi\paypal;

use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Api\OpenIdTokeninfo;
use PayPal\Api\OpenIdUserinfo;

use Illuminate\Support\ServiceProvider;

class PayPalProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        include __DIR__.'/routes.php';

        $this->loadViewsFrom(__DIR__.'/views', 'paypal');

        $this->publishes([
            __DIR__.'/views/paypal_button.blade.php' => base_path('resources/views/vendor/skygdi/paypal_button.blade.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // register our controller
        $this->app->make('skygdi\paypal\CommonController');
        
    }
}
