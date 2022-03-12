<!DOCTYPE html>
<html>
<head>
<meta http-equiv="refresh" content="0;url={!!$shoppingcart->shoppingcart_payment->redirect!!}" />
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<p>Please click <a href="{!! $app_url .'/booking/receipt/'. $shoppingcart->session_id .'/'. $shoppingcart->confirmation_code !!}">here</a> if you are not redirected within a few seconds</p>

</body>
</html>