<?php

namespace skygdi\paypal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Session;

use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Api\OpenIdTokeninfo;
use PayPal\Api\OpenIdUserinfo;

class CommonController extends Controller
{
	static $PAYPAL_ENV = "sandbox";
	static $sanbox_clientId = "AQoXozL0vqCmDF2M47DHbx_4P_J_RV5R1v4zwpb0ges92qZz9A2Z9jVx43A1GWjR3Rx5muNgB41T_jj6";
	static $sanbox_clientSecret = "EC5eEcxlEQonzP18hpcDa-mUiUuWjXlUOOtKLAcR0Si99uvLOcZX2K-UDU3Y00eaPBXvwAg6WlTjlE6o";
	static $log = true;
	static $baseURLTest;

	function __construct(){
		self::$baseURLTest = url('skygdi/paypal/test/');
	}

    function test(){
    	return view('paypal::test')
    		->with('PAYPAL_ENV',self::$PAYPAL_ENV)
    		->with('baseURLTest',self::$baseURLTest);
    }

    function TestExecute(Request $request){
    	$this->InitializeApiContext(self::$sanbox_clientId,
	    		self::$sanbox_clientSecret,
	    		self::$PAYPAL_ENV
		);
		/*
		$_POST["paymentID"] = "PAY-7V134197HY484634PLHTB35A";
        $_POST["payerID"] = "VLXCYC4VLPCKC";
        */
        //exit();
        if( Session::has('ordering_id') ){
        	//Mark order as paying
        }

        if( !$request->has("paymentID") || !$request->has("payerID") ) return ["state"=>"error","text"=>"parameter required"];

        $p = $this->ExecuteBase($request);
        if( $p->state=="approved" ){
        	//Mark order as finished
			return ["state"=>"success"];
        }
    }

    public function ExecuteBase($request){
		
		$paymentID = $request->get('paymentID');
		$payerID = $request->get('payerID');

		$payment = Payment::get($paymentID, $this->apiContext);
		
		try{
	    	$execution = new PaymentExecution();
	    	$execution->setPayerId($payerID);
	    	$result = $payment->execute($execution, $this->apiContext);
	    	$p = Payment::get($paymentID, $this->apiContext);
	    	return $p;
	    }
	    catch (\PayPal\Exception\PayPalConnectionException $ex) {
		    $ex_json = json_decode( $ex->getData(),true ); // Prints the detailed error message 
		    return ["state"=>"error","text"=>$ex_json["name"]];
		} 
	    catch (Exception $ex) {
		    return ["state"=>"error","text"=>"unknow"];
		    exit();
    	}
	}

    function TestCreate(){
    	$this->InitializeApiContext(self::$sanbox_clientId,
	    		self::$sanbox_clientSecret,
	    		self::$PAYPAL_ENV
		);

		$total = 0.12;
		$shopping_cart_id = time();
		

		//Paypal
		//$i = $this->CreateBase($total,$shopping_cart_id);
		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl($_ENV['APP_URL']."/payment/paypal/return")
		    ->setCancelUrl($_ENV['APP_URL']."/payment/paypal/cancel");
		$amount = new Amount();
		$amount->setCurrency("USD")
		    ->setTotal($total);

		$transaction = new Transaction();
		$transaction->setAmount($amount)
			->setDescription($shopping_cart_id)
			->setInvoiceNumber(self::$PAYPAL_ENV.$shopping_cart_id);

		$payer = new Payer();
		$payer->setPaymentMethod("paypal");

		$obj_payment = new Payment();
		$obj_payment->setPayer($payer)
			->setIntent("sale")
			->setRedirectUrls($redirectUrls)
		    ->setTransactions([$transaction]);
		$i = $obj_payment->create($this->apiContext);

		//$_SESSION["ordering_id"] = $shopping_cart_id;
		Session::put('ordering_id', $shopping_cart_id);

		echo json_encode( array("id"=>$i->id) );
    }

    private function InitializeApiContext($clientId,$clientSecret,$PAYPAL_ENV){
		$this->apiContext = new \PayPal\Rest\ApiContext(
		    new \PayPal\Auth\OAuthTokenCredential(
		        $clientId,     // ClientID
		        $clientSecret  // ClientSecret
		    )
		);

		if( self::$log ){
			$this->apiContext->setConfig(
		        array(
		            'log.LogEnabled' => true,
		            'log.FileName' => storage_path().'/logs/PayPal.log',
		            'log.LogLevel' => 'DEBUG',
		        )
		    );
	    }

	    if( $PAYPAL_ENV=="production" ){
		    $this->apiContext->setConfig(
		      array(
		        'mode' => 'live',
		      )
			);
		}
	}
}
