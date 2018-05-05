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
        $this->loadViewsFrom(__DIR__.'/views', 'paypal');
    }
}
