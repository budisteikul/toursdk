@inject('ProductHelper', 'budisteikul\toursdk\Helpers\ProductHelper')
@inject('BookingHelper', 'budisteikul\toursdk\Helpers\BookingHelper')
@php
  $main_contact = $BookingHelper->get_answer_contact($shoppingcart_product->shoppingcart);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ticket-{{ $shoppingcart_product->product_confirmation_code }}</title>
<style type="text/css" media="all">
body {
  
  color: #555555;
  background: #FFFFFF; 
  font-family: Arial, Helvetica, sans-serif;
  font-size: 13px; 
}

.card_divider {
  position: relative;
  width: 100%;
}
.card_divider .divider_left {
  left: -15px;
  top: -5px;
}
.card_divider .divider_hole {
  position: absolute;
  padding: 0px;
  height: 27px;
  width: 27px;
  border-radius: 100%;
  background: #ffffff;
}

.card_divider .divider_right {
  right: -15px;
  top: -5px;
}

.card {
  width: 300px;
  border-radius: 20px;
  background: #4d1532;
  display: inline-block;
  margin: 10px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.4);
}
.aa-theme {
  background: #0087C3;
}
.card_logo {
  background: #ffffff;
  border-radius: 20px 20px 0 0;
  border-right:2px solid #0087C3;
  border-left:2px solid #0087C3;
  border-top:2px solid #0087C3;
  text-align:center;
  padding-top:10px;
  padding-bottom:10px;
}
.card_heading .title {
  padding-top: 5px;
  margin-top: 5px;
  text-align: center;
  color:#FFFFFF;
  font-size:13px;
  margin-left:20px;
  margin-right:20px;
  opacity:0.8;
  text-transform:uppercase;
}
.card_heading .qrcode {
  padding-top: 5px;
  margin-top: 5px;
  text-align: center;
  color:#FFFFFF;
  font-size:13px;
  margin-left:15px;
  margin-right:15px;
}

.text-no-opacity
{
  color:#FFFFFF;
  font-size:13px;
  width:50%;
  vertical-align:top;
}

.text-opacity
{
  color:#FFFFFF;
  font-size:13px;
  opacity:0.8;
  width:50%;
  vertical-align:top;
}
</style>
</head>
<body>

<div class="card aa-theme">
        <div class="card_heading">
        			<div class="card_logo">
						<img src="{{url('/img/logo-blue.jpg')}}" height="35" style="max-width:200px;" />
					</div>
          @php
          $image = url('/img/blank-300.jpg');
          if($shoppingcart_product->image!="")
          {
            $image = str_ireplace("w=80","w=300",$shoppingcart_product->image);
            $image = str_ireplace("h=80","h=200",$image);
            $image = str_ireplace("w_80","w_300",$image);
            $image = str_ireplace("h_80","h_200",$image);
          }
					@endphp
					<img class="product-image" src="{{ $image }}" width="300">
					<div class="card_divider" style="margin-top:-7px;">
							<div class="divider_left divider_hole"></div>
							<hr style="border:dashed #FFFFFF thin; opacity:0.8;">
							<div class="divider_right divider_hole"></div>
					</div>   
                    <div class="title">
                    	{{ $shoppingcart_product->title }}
                    	<br>
                      @if($shoppingcart_product->title!=$shoppingcart_product->rate)
                    	{{ $shoppingcart_product->rate }} 
                      @endif
                      <br>
                      @php
                        $people = 0;
                      @endphp
                      @foreach($shoppingcart_product->shoppingcart_product_details as $shoppingcart_product_detail)
                        @php
                          $people += $shoppingcart_product_detail->people;
                        @endphp
                      @endforeach
                      @if($people>0)
                      {{ $people .' Person' }}
                      @endif
                    </div>
                    <div class="qrcode">
						
            <img width="111" src="data:image/png;base64, {{ $qrcode }} ">  
					</div>
                    <div class="qrcode" style="margin-top:0px;">
                    	{{ $shoppingcart_product->product_confirmation_code }}
                    </div>
                     
		</div>
        
        <div class="card_divider">
			<div class="divider_left divider_hole"></div>
			<hr style="border:dashed #FFFFFF thin; opacity:0.8;">
			<div class="divider_right divider_hole"></div>
		</div>
        
        <div style="margin-top:15px; margin-bottom:20px; padding-left:20px; padding-right:20px;">
          <table width="100%" border="0" align="center" cellspacing="0" cellpadding="0">
       	    <tbody>
                @php
                $thetanggal = $ProductHelper->datetotext($shoppingcart_product->date);
                @endphp
        	      <tr>
        	        <td class="text-opacity">NAME</td>
        	        <td class="text-opacity">
                  @if($thetanggal!=null)
                    DATE
                  @endif
                  </td>
      	        </tr>
        	      <tr>
        	        <td class="text-no-opacity">
                @php
                $rev_shoppingcarts = $shoppingcart_product->shoppingcart()->first();
                @endphp
                {{ $main_contact->firstName }}
                {{ $main_contact->lastName }} 
                    </td>
        	        <td class="text-no-opacity">
                @if($thetanggal!=null)   
                @php
                $thetanggal = str_ireplace("@","<br>",$thetanggal);
                @endphp
                {!! $thetanggal !!}
                @endif
                    </td>
      	        </tr>
   	        </tbody>
   	      </table>
        </div>
        
</div>

</body>
</html>