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
	static $sanbox_clientId = "";
	static $sanbox_clientSecret = "";
	static $log = true;
	static $baseURLTest;

	function __construct(){
		self::$baseURLTest = url('skygdi/paypal/test/');
	}

    function test(){
    	if( !isset($_ENV['PAYPAL_SANBOX_CLIENTID']) ){
			abort(450,"Please define PAYPAL_SANBOX_CLIENTID in .env file");
		}
		if( !isset($_ENV['PAYPAL_SANBOX_CLIENTSECRET']) ){
			abort(450,"Please define PAYPAL_SANBOX_CLIENTSECRET in .env file");
		}

    	self::$sanbox_clientId = $_ENV['PAYPAL_SANBOX_CLIENTID'];
		self::$sanbox_clientSecret = $_ENV['PAYPAL_SANBOX_CLIENTSECRET'];

    	return view('paypal::test')
    		->with('PAYPAL_ENV',"sandbox")
    		->with('baseURLTest',self::$baseURLTest);
    }

    function TestExecute(Request $request){
    	self::$sanbox_clientId = $_ENV['PAYPAL_SANBOX_CLIENTID'];
		self::$sanbox_clientSecret = $_ENV['PAYPAL_SANBOX_CLIENTSECRET'];

    	$this->InitializeApiContext(self::$sanbox_clientId,
	    		self::$sanbox_clientSecret,
	    		"sandbox"
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
    	self::$sanbox_clientId = $_ENV['PAYPAL_SANBOX_CLIENTID'];
		self::$sanbox_clientSecret = $_ENV['PAYPAL_SANBOX_CLIENTSECRET'];

    	$this->InitializeApiContext(self::$sanbox_clientId,
	    		self::$sanbox_clientSecret,
	    		"sandbox"
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
			->setInvoiceNumber("sandbox".$shopping_cart_id);

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
