### Quick paypal button deploy for Laravel
1.Install:
```php
composer require skygdi/paypal
```

2.Add paypal configuration into your .env file:
```php
PAYPAL_SANBOX_CLIENTID=your_paypal_client_ID
PAYPAL_SANBOX_CLIENTSECRET=your_paypal_client_secret
PAYPAL_CLIENTID=your_paypal_client_ID
PAYPAL_CLIENTSECRET=your_paypal_client_secret
PAYPAL_ENV=sandbox
```
[ sandbox  production ]

3.Public button template:
```php
php artisan vendor:publish --provider="skygdi\paypal\PayPalProvider"
```
4.include the template partial where ever you wanted and edit as needed:
```php
@include('vendor.skygdi.paypal_button')
```
Change the #order_id input and #order_total value as your logic needed before clicking the paypal checkout button.

5. Create your order status logic, for example in web.php:
```php
use Session;
use Illuminate\Http\Request;
use skygdi\paypal\CommonController;

Route::post('paypal/execute', function (Request $request) {
	$obj = new \skygdi\paypal\CommonController();

	$obj->InitializeApiContext();
    if( Session::has('ordering_id') ){
    	//Mark order as paying
    }

    if( !$request->has("paymentID") || !$request->has("payerID") ) return ["state"=>"error","text"=>"parameter required"];

    $p = $obj->Execute($request);
    if( isset($p->state) && $p->state=="approved" ){
    	//Mark order as finished
		return ["state"=>"success"];
    }
    else{
    	return $p;
    }
});
```
___
Quick test url: 
##### yourURL/skygdi/paypal/test

test
