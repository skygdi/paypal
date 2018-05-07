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

class TestController extends CommonController
{
    function test(){
		return view('paypal::test');
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

    
}
