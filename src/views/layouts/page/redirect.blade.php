<!DOCTYPE html>
<html>
<head>
<meta http-equiv="refresh" content="0;url={!!$shoppingcart->shoppingcart_payment->redirect!!}" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
</head>
<body onload="window.location.replace('{!!$shoppingcart->shoppingcart_payment->redirect!!}')">
<script type="text/javascript">
    window.onload = function() {
        setTimeout(function() {
            window.location = "{!!$shoppingcart->shoppingcart_payment->redirect!!}";
        }, 5000);
    };
</script>
<p>Please click <a href="{!! $app_url .'/booking/receipt/'. $shoppingcart->session_id .'/'. $shoppingcart->confirmation_code !!}">here</a> if you are not redirected within a few seconds</p>

</body>
</html>