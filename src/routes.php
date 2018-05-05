<?php

/*
Route::get('skygdi/test/paypal', function(){
	echo 'Hello from the calculator package!';
});
*/

Route::get('skygdi/paypal/test', 'skygdi\paypal\CommonController@test');
Route::post('skygdi/paypal/test/create', 'skygdi\paypal\CommonController@TestCreate');
Route::post('skygdi/paypal/test/execute', 'skygdi\paypal\CommonController@TestExecute');
Route::post('skygdi/paypal/test/paid', 'skygdi\paypal\CommonController@TestPaid');