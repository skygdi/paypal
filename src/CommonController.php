<?php

namespace skygdi\paypal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use View;

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
	static $client_ID = "";
	static $client_secret = "";
	static $log = true;
	

	function __construct(){
	}

    function test(){
		return view('paypal::test');
    }

    function Create(Request $request){
    	$this->InitializeApiContext();

		$total = $request->get('order_total');
		$shopping_cart_id = $request->get('order_id');

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
			->setInvoiceNumber($_ENV['PAYPAL_ENV'].$shopping_cart_id);

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

    function TestCreate(Request $request){
    	$this->InitializeApiContext();

		$total = $request->get('order_total');
		$shopping_cart_id = $request->get('order_id');
		

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
			->setInvoiceNumber($_ENV['PAYPAL_ENV'].$shopping_cart_id);

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

    function TestExecute(Request $request){
    	$this->InitializeApiContext();
        if( Session::has('ordering_id') ){
        	//Mark order as paying
        }

        if( !$request->has("paymentID") || !$request->has("payerID") ) return ["state"=>"error","text"=>"parameter required"];

        $p = $this->Execute($request);
        if( isset($p->state) && $p->state=="approved" ){
        	//Mark order as finished
			return ["state"=>"success"];
        }
    }

    public function Execute(Request $request){
		
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

    public function InitializeApiContext(){

    	if( !isset($_ENV['PAYPAL_SANBOX_CLIENTID']) ){
			abort(450,"Please define PAYPAL_SANBOX_CLIENTID in .env file");
		}
		if( !isset($_ENV['PAYPAL_SANBOX_CLIENTSECRET']) ){
			abort(450,"Please define PAYPAL_SANBOX_CLIENTSECRET in .env file");
		}
		if( !isset($_ENV['PAYPAL_CLIENTID']) ){
			abort(450,"Please define PAYPAL_CLIENTID in .env file");
		}
		if( !isset($_ENV['PAYPAL_CLIENTSECRET']) ){
			abort(450,"Please define PAYPAL_CLIENTSECRET in .env file");
		}
		if( !isset($_ENV['PAYPAL_ENV']) ){
			abort(450,"Please define PAYPAL_ENV in .env file");
		}

		//production or sandbox
		if( $_ENV['PAYPAL_ENV']=="production" ){
			self::$client_ID 		= $_ENV['PAYPAL_CLIENTID'];
			self::$client_secret 	= $_ENV['PAYPAL_CLIENTSECRET'];
		}
		else{
			self::$client_ID 		= $_ENV['PAYPAL_SANBOX_CLIENTID'];
			self::$client_secret 	= $_ENV['PAYPAL_SANBOX_CLIENTSECRET'];
		}

		$this->apiContext = new \PayPal\Rest\ApiContext(
		    new \PayPal\Auth\OAuthTokenCredential(
		        self::$client_ID,     // ClientID
		        self::$client_secret  // ClientSecret
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

	    if( $_ENV['PAYPAL_ENV']=="production" ){
		    $this->apiContext->setConfig([
	        	'mode' => 'live',
		    ]);
		}
	}
}
