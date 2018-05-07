<?php

/*
Route::get('skygdi/test/paypal', function(){
	echo 'Hello from the calculator package!';
});
*/

Route::get('skygdi/paypal/test', 'skygdi\paypal\CommonController@test');

Route::post('skygdi/paypal/test/create', 'skygdi\paypal\TestController@TestCreate');
Route::post('skygdi/paypal/test/execute', 'skygdi\paypal\TestController@TestExecute');

Route::post('paypal/create', 'skygdi\paypal\CommonController@Create');
