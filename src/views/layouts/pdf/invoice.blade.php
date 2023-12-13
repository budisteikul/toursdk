@inject('GeneralHelper', 'budisteikul\toursdk\Helpers\GeneralHelper')
@inject('BookingHelper', 'budisteikul\toursdk\Helpers\BookingHelper')
@inject('SettingHelper', 'budisteikul\toursdk\Helpers\SettingHelper')
@inject('PaymentHelper', 'budisteikul\toursdk\Helpers\PaymentHelper')
@inject('ChannelHelper', 'budisteikul\toursdk\Helpers\ChannelHelper')

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
  padding: 6px 6px 6px 6px;
  border: 1px dashed #CCCCCC;
  width: 40%;
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

.invoice-hilang
{
  display: none;
}

.invoice-color-danger
{
  color: #d9534f;
  font-weight: bold;
}

.invoice-color-success
{
  color: #5cb85c;
  font-weight: bold;
}

.invoice-color-warning
{
  color: #f0ad4e;
  font-weight: bold;
}

.invoice-color-info
{
  color: #1c98ae;
  font-weight: bold;
}

.description p
{
   margin-top: 5px;
   font-size:16px;
}
</style>
</head>
<body>
    
      
     <table border="0" cellspacing="0" cellpadding="0" style="margin-bottom:0px;">
       <tbody>
         <tr>
          <td style="background-color:#FFFFFF; text-align:left; padding-left:0px; line-height: 18px; font-size:14px; color:#777777">

                        {!! $SettingHelper->getSetting('company') !!}
                  
           </td>
           <td style="background-color:#FFFFFF; text-align:right; padding-right:0px; line-height: 18px; font-size:14px; color:#777777">
                        <img width="110" src="data:image/png;base64, {{ $qrcode }} ">
           </td>
         </tr>
       </tbody>
     </table>
     
   <hr style="border:none; height:1px; color: #AAA; background-color: #AAA; margin-top:0px; margin-bottom:10px;">  

<table border="0" cellspacing="0" cellpadding="0" style="margin-bottom:20px; margin-top:0px;">
       <tbody>
         <tr>
           <td valign="top" width="50%" style="background-color:#FFFFFF; text-align:left; padding-left:0px; padding-top:0px; ">
           
         <div id="client">
          <div class="to" style="font-size: 14px; color: #0087C3; line-height: 18px; ">BILLED TO</div>

          @if($shoppingcart->booking_channel=="WEBSITE")
          @php
            $main_contact = $BookingHelper->get_answer_contact($shoppingcart);
          @endphp
          <h2 class="name" style=" line-height: 18px; font-size:14px;">{{ $main_contact->firstName }}
                        {{ $main_contact->lastName }} </h2>
          <div class="address" style=" line-height: 18px; font-size:14px;">{{ $main_contact->phoneNumber }}</div>
          <div class="email" style=" line-height: 18px; font-size:14px;">{{ $main_contact->email }}</div>
          @else
          
          <div class="description">{!! $ChannelHelper->getDescription($shoppingcart->booking_channel);  !!}</div>
          
          @endif


        </div>
           				
           </td>
           <td valign="top" style="background-color:#FFFFFF; text-align:right; padding-right:0px; padding-top:0px;">
            <div id="invoice">
          		<!-- h1 style="margin-top: 5px;">INVOICE</h1 -->
              <div class="date" style=" line-height: 18px; font-size:14px;">
                Invoice# : <b>{{ $shoppingcart->confirmation_code }}</b>
              </div>
          		<div class="date" style=" line-height: 18px; font-size:14px;">
                Date of Invoice : {{ Carbon\Carbon::parse($shoppingcart->created_at)->formatLocalized('%d %b %Y  %H:%M') }}</div>
          		
                <div class="date" style=" line-height: 18px; font-size:14px;">Due Date : {{ Carbon\Carbon::parse($BookingHelper->due_date($shoppingcart,"database"))->formatLocalized('%d %b %Y  %H:%M') }}</div>
                
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
                        @foreach($shoppingcart_product->shoppingcart_product_details()->get() as $shoppingcart_product_detail)
                        <tbody>
                        <tr>
           				  <td class="no">{{ sprintf("%02d", $number) }}</td>
           				  <td class="desc"><h3>{{ $shoppingcart_product_detail->title }}</h3>{{ $shoppingcart_product_detail->unit_price }}</td>
           				  <td class="unit">{{ $GeneralHelper->numberFormat($shoppingcart_product_detail->price) }}</td>
           				  <td class="qty">{{ $shoppingcart_product_detail->qty }}</td>
           				  <td class="total">{{ $GeneralHelper->numberFormat($shoppingcart_product_detail->subtotal) }}</td>
          				</tr>
                        </tbody>
                        <?php
						$number += 1;
						$subtotal += $shoppingcart_product_detail->subtotal;
						$total += $shoppingcart_product_detail->total;
						$discount += $shoppingcart_product_detail->discount;
						?>
                        @endforeach
                        
                        @endforeach
        
        <tfoot>
          @if($discount>0)
          <tr>
            <td colspan="2"></td>
            <td colspan="2">DISCOUNT</td>
            <td>{{ $GeneralHelper->numberFormat($discount) }}</td>
          </tr>
          @endif
          @if($shoppingcart->fee != 0)
          <tr>
            <td colspan="2"></td>
            <td colspan="2">FEE</td>
            @php
              $fee = $shoppingcart->fee;
              if($fee<0) $fee = $fee * -1;
            @endphp
            <td>{{ $GeneralHelper->numberFormat($fee) }}</td>
          </tr>
          @endif
          <tr>
            <td colspan="2"></td>
            <td colspan="2">TOTAL</td>
            <td>{{ $GeneralHelper->numberFormat($total) }}</td>
          </tr>
          @if($shoppingcart->due_on_arrival>0)
          <tr>
            <td colspan="2"></td>
            <td colspan="2">DUE ON ARRIVAL ({{$shoppingcart->currency}})</td>
            <td>{{ $GeneralHelper->numberFormat($shoppingcart->due_on_arrival) }}</td>
          </tr>
          @endif
          <tr>
            <td colspan="2"></td>
            <td colspan="2">DUE NOW ({{$shoppingcart->currency}})</td>
            <td>{{ $GeneralHelper->numberFormat($shoppingcart->due_now) }}</td>
          </tr>
          
          
        </tfoot>
</table>

<br />

@if($shoppingcart->shoppingcart_payment->payment_provider!="none")
<div id="notices" style="margin-top: 10px;float:right;">
  
  <div style="font-size: 14px; color: #AAAAAA; line-height: 18px; font-weight: bold; ">PAYMENT STATUS</div>  
  <div class="notice"><small>{!! $PaymentHelper->get_paymentStatus($shoppingcart) !!}</small></div>
  
</div>
@endif

@if($shoppingcart->shoppingcart_payment->payment_provider=="none")
<h2 style="margin-bottom:3px;">Payment Details</h2>
<strong>Bank : </strong>BCA (Bank Central Asia)<br />
<strong>Bank Swift : </strong>CENAIDJA<br />
<strong>Account Name : </strong>VERTIKAL TRIP INDONESIA<br />
<strong>Account Number : </strong>1690423860<br />
@endif
<div style="clear: both;"></div>
<footer>
	Invoice was generated on a server and is valid without the signature and seal. Scan qrcode to validation.
</footer>
</body>
</html>
