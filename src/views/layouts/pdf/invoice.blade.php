<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">   
<title>Invoice-{{ $shoppingcart->confirmation_code }}</title>
<style type="text/css" media="all">
body {
  margin: 0 auto; 
  color: #555555;
  background: #FFFFFF; 
  font-family: Arial, Helvetica, sans-serif;
  font-size: 12px; 
}

table {
  width: 100%;
  border-collapse: collapse;
  border-spacing: 0;
  margin-bottom: 20px;
}

table th,
table td {
  padding: 20px;
  background: #EEEEEE;
  text-align: center;
  border-bottom: 1px solid #FFFFFF;
  border-top: 1px solid #FFFFFF;
}

table th {
  white-space: nowrap;        
  font-weight: normal;
}

table td {
  text-align: right;
}

table td h3{
  color: #0087C3;
  font-size: 1.2em;
  font-weight: normal;
  margin: 0 0 0.2em 0;
}

table .no {
  color: #FFFFFF;
  font-size: 1.6em;
  background: #0087C3;
  -webkit-print-color-adjust: exact; 
}

table .desc {
  text-align: left;
  -webkit-print-color-adjust: exact; 
}

table .unit {
  background: #DDDDDD;
  -webkit-print-color-adjust: exact; 
}

table .qty {
	-webkit-print-color-adjust: exact; 
}

table .total {
  background: #0087C3;
  color: #FFFFFF;
  -webkit-print-color-adjust: exact; 
}

table td.unit,
table td.qty,
table td.total,
table tbody tr:last-child td {
  border-bottom: 1px solid #FFFFFF;
  border-top: 1px solid #FFFFFF;
}

table tfoot td {
  padding: 10px 20px;
  background: #FFFFFF;
  border-bottom: none;
  font-size: 1.2em;
  white-space: nowrap; 
  border-top: none; 
}

table tfoot tr:first-child td {
  border-top: none; 
}

table tfoot tr:last-child td {
  color: #0087C3;
  font-size: 1.4em;
  border-top: 1px solid #0087C3; 

}

table tfoot tr td:first-child {
  border: none;
}

a {
  color: #0087C3;
  text-decoration: none;
}

#client {
  padding-left: 6px;
  border-left: 6px solid #0087C3;
}

#client .to {
  color: #777777;
}

h2.name {
  font-size: 1.4em;
  font-weight: normal;
  margin: 0;
}




#invoice {
  text-align: right;
}

#invoice h1 {
  color: #0087C3;
  font-size: 2.4em;
  line-height: 1em;
  font-weight: normal;
  margin-bottom:7px;
}

#invoice .date {
  font-size: 1.1em;
  color: #777777;
}

#thanks{
  font-size: 1.0em;
  margin-bottom: 20px;
}

#notices{
  padding-left: 6px;
  border-left: 6px solid #0087C3;  
}

#notices .notice {
  font-size: 1.2em;
}

footer {
  color: #777777;
  width: 100%;
  height: 30px;
  border-top: 1px solid #AAAAAA;
  padding: 8px 0;
  text-align: center;
  
  display: block;
    position: fixed;
    bottom: 0;
}
</style>
</head>
<body>
    
      
     <table border="0" cellspacing="0" cellpadding="0" style="margin-bottom:0px;">
       <tbody>
         <tr>
           <td style="background-color:#FFFFFF; text-align:left; padding-left:0px;">
           <img src="data:image/png;base64, {!! base64_encode(QrCode::errorCorrection('H')->format('png')->size(111)->margin(0)->generate(url('/booking/receipt/'. $shoppingcart->id .'/'.$shoppingcart->session_id))) !!} ">
           				
           </td>
           <td style="background-color:#FFFFFF; text-align:right; padding-right:0px; line-height: 18px; font-size:14px; color:#777777">
                        <img src="{{asset('/img/logo-blue.png')}}" height="30" />
                        <div style="margin-top:3px;">Jl. Abiyoso VII No.190 Bantul ID</div>
                        <div>Telp: +62 857 43 112 112</div>
                        <div>Email: guide@vertikaltrip.com</div>
           </td>
         </tr>
       </tbody>
     </table>
     
   <hr style="border:none; height:1px; color: #AAA; background-color: #AAA; margin-top:0px; margin-bottom:0px;">  

<table border="0" cellspacing="0" cellpadding="0" style="margin-bottom:20px; margin-top:0px;">
       <tbody>
         <tr>
           <td style="background-color:#FFFFFF; text-align:left; padding-left:0px; padding-top:0px; width:40%">
           
         <div id="client">
          <div class="to" style=" line-height: 18px; font-size:14px;">INVOICE TO:</div>
          <h2 class="name" style=" line-height: 18px; font-size:14px;">{{ $shoppingcart->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','firstName')->first()->answer }}
                        {{ $shoppingcart->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','lastName')->first()->answer }} </h2>
          <div class="address" style=" line-height: 18px; font-size:14px;">{{ $shoppingcart->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','phoneNumber')->first()->answer }}</div>
          <div class="email" style=" line-height: 18px; font-size:14px;"><a href="mailto:{{ $shoppingcart->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','email')->first()->answer }} ">{{ $shoppingcart->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','email')->first()->answer }} </a></div>
        </div>
           				
           </td>
           <td style="background-color:#FFFFFF; text-align:right; padding-right:0px; padding-top:0px;">
            <div id="invoice">
          		<h1>INVOICE {{ $shoppingcart->confirmationCode }}</h1>
          		<div class="date" style=" line-height: 18px; font-size:14px;">Date of Invoice: {{ Carbon\Carbon::parse($shoppingcart->created_at)->formatLocalized('%d %b %Y') }}</div>
          		@php
                	$min_date = $shoppingcart->shoppingcart_products()->orderBy('date','asc')->first()->date;
                @endphp
                <div class="date" style=" line-height: 18px; font-size:14px;">Due Date: {{ Carbon\Carbon::parse($min_date)->formatLocalized('%d %b %Y') }}</div>
          		<div class="date" style=" line-height: 18px; font-size:14px;">Status: {{ $shoppingcart->booking_status }}</div>
        	</div>           
           </td>
         </tr>
       </tbody>
     </table>

<table border="0" cellspacing="0" cellpadding="0">
  <thead>
          <tr>
            <th class="no">#</th>
            <th class="desc" width="50%"><strong>DESCRIPTION</strong></th>
            <th class="unit"><strong>UNIT PRICE</strong></th>
            <th class="qty"><strong>QUANTITY</strong></th>
            <th class="total">TOTAL</th>
          </tr>
  </thead>
        
          <?php
						$grantTotal = 0;
						$number = 1;
						$total = 0;
						$discount = 0;
						?>
                        @foreach($shoppingcart->shoppingcart_products()->get() as $shoppingcart_product)
                        <?php
						$subtotal = 0;
						?>
                        @foreach($shoppingcart_product->shoppingcart_rates()->get() as $shoppingcart_rate)
                        <tbody>
                        <tr>
           				  <td class="no">{{ sprintf("%02d", $number) }}</td>
           				  <td class="desc"><h3>{{ $shoppingcart_rate->title }}</h3>{{ $shoppingcart_rate->unit_price }}</td>
           				  <td class="unit">{{ number_format((float)$shoppingcart_rate->price, 2, '.', '') }}</td>
           				  <td class="qty">{{ $shoppingcart_rate->qty }}</td>
           				  <td class="total">{{ number_format((float)$shoppingcart_rate->subtotal, 2, '.', '') }}</td>
          				</tr>
                        </tbody>
                        <?php
						$number += 1;
						$subtotal += $shoppingcart_rate->subtotal;
						$total += $shoppingcart_rate->total;
						$discount += $shoppingcart_rate->discount;
						?>
                        @endforeach
                        
                        @endforeach
        
        <tfoot>
          @if($discount>0)
          <tr>
            <td colspan="2"></td>
            <td colspan="2">DISCOUNT</td>
            <td>{{ number_format((float)$discount, 2, '.', '') }}</td>
          </tr>
          @endif
          <tr>
            <td colspan="2"></td>
            <td colspan="2">TOTAL ({{$shoppingcart->currency}})</td>
            <td>{{ number_format((float)$total, 2, '.', '') }}</td>
          </tr>
          @php
            $refunded = $total * -1;
            $currency = $shoppingcart->currency;
            if(!empty($shoppingcart->shoppingcart_payment->currency))
            {
              $currency = $shoppingcart->shoppingcart_payment->currency;
            }
            if(!empty($shoppingcart->shoppingcart_payment->amount))
            {
              $total = $shoppingcart->shoppingcart_payment->amount;
            }
          @endphp
          @if($shoppingcart->booking_status=="CANCELED")
          <tr>
            <td colspan="2"></td>
            <td colspan="2">REFUNDED</td>
            <td>{{ number_format((float)$refunded, 2, '.', '') }}</td>
          </tr>
          <tr>
            <td colspan="2"></td>
            <td colspan="2">AMOUNT DUE ({{$currency}})</td>
            <td>0</td>
          </tr>
          @else
          <tr>
            <td colspan="2"></td>
            <td colspan="2">AMOUNT DUE ({{$currency}})</td>
            <td>{{ number_format((float)$total, 2, '.', '') }}</td>
          </tr>
          @endif
        </tfoot>
</table>
<div id="thanks">Thank you for your booking with {{env('APP_NAME')}}</div>   
<div id="notices">
<div>NOTICE:</div>
<div class="notice"><small>&nbsp;{{$notice}}</small></div>
</div>
<footer>
	Invoice was created on a computer and is valid without the signature and seal.
</footer>
</body>
</html>