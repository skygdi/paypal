<!DOCTYPE html>
<html>
<head>
	<title>Test</title>

	<script
		src="/cdn/jquery/js/jquery-3.1.1.min.js"
		crossorigin="anonymous"></script>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="/cdn/bootstrap-3.3.7-dist/css/bootstrap.min.css"  crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="/cdn/bootstrap-3.3.7-dist/css/bootstrap-theme.min.css" crossorigin="anonymous">

	<!-- Latest compiled and minified JavaScript -->
	<script src="/cdn/bootstrap-3.3.7-dist/js/bootstrap.min.js"  crossorigin="anonymous"></script>

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

	<script src="https://www.paypalobjects.com/api/checkout.js"></script>

</head>

<script>
    

    var CREATE_PAYMENT_URL  = '{{ $baseURLTest }}/create';
    var EXECUTE_PAYMENT_URL = '{{ $baseURLTest }}/execute';

    paypal.Button.render({
        env: '{{$PAYPAL_ENV}}', // 'production' Or 'sandbox'
        commit: true, // Show a 'Pay Now' button
        payment: function() {
            return paypal.request({
                method: 'post',
                url: CREATE_PAYMENT_URL,
                headers: {
                    'x-csrf-token': '{{ csrf_token() }}'
                }
            }).then(function(data) {
                return data.id;
            });
        },

        onAuthorize: function(data) {
            return paypal.request({
                method: 'post',
                url: EXECUTE_PAYMENT_URL,
                headers: {
                    'x-csrf-token': '{{ csrf_token() }}'
                },
                data:{
                    'paymentID': data.paymentID,
                    'payerID':   data.payerID
                }
            }).then(function(data) {
                if( data.error!=null ){
                    //alert(data.error.msg+"! Please contact our administrator for help");
                    toastr.error('Error', data.error.msg);
                    return;
                }
                else{
                    //alert("Thank you!");
                    toastr.success('Thank you!', 'see you soon!');
                }
                // The payment is complete!
                // You can now show a confirmation message to the customer
            });
        }

    }, '#paypal-button');

    
</script>

<body>
<div class="ccontainer">
	<div class="row">
		<div class="col-md-12" style="text-align: center;margin-top:50px;">
			<div id="paypal-button"></div>
		</div>
	</div>
</div>
</body>
</html>