@inject('ProductHelper', 'budisteikul\toursdk\Helpers\ProductHelper')
@extends('coresdk::layouts.app')
@section('content')
<script language="javascript">
function CREATE()
    {
        $.fancybox.open({
            type: 'ajax',
            src: '{{ route('route_toursdk_booking.create') }}',
            touch: false,
            modal: true,
        }); 
    }
</script>
<div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Checkout Booking</div>
                <div class="card-body">


	<div class="row">
		<div class="col-lg-12 col-md-12 mx-auto">
			<div class="row" style="padding-bottom:0px;">
				<div class="col-lg-12 text-left">
				
            	<div class="row mb-2">  
				<div class="col-lg-6 col-lg-auto mb-6 mt-4">
                
<!-- ################################################################### -->  
<script language="javascript">
function REMOVE(id)
{
	$('#remove-'+id).attr("disabled", true);
	$('#remove-'+id).html('<i class="fa fa-spinner fa-spin"></i>');
	
	$.ajax({
		data: {
        	"_token": $("meta[name=csrf-token]").attr("content"),
			"bookingId": id,
            "sessionId": '{{$shoppingcart->session_id}}',
        },
		type: 'POST',
		url: '/snippets/activity/remove'
		}).done(function( data ) {
			if(data.id=="1")
			{
				window.location.href = '{{ route('route_toursdk_booking.index') }}/checkout';
			}
			else
			{
				$('#remove-'+id).attr("disabled", false);
				$('#remove-'+id).html('<i class="fa fa-trash-alt"></i>');
			}
		});
	
	
	return false;
}
</script>
                <div class="card">
                <?php
				$grand_subtotal = 0;
				$grand_discount = 0;
				$grand_total = 0;
				?>
                @foreach($shoppingcart->shoppingcart_products()->get() as $shoppingcart_product)
                <!-- Product booking -->
                <div class="card-body">
                            <!-- Product detail booking -->
							<div class="row mb-4">
                				<div class="col-8">
                    				<b>{{ $shoppingcart_product->title }}</b>
                    			</div>
                    			<div class="col-4 text-right">
                                	<?php
									$product_subtotal = 0;
									$product_discount = 0;
									$product_total = 0;
									foreach($shoppingcart_product->shoppingcart_rates()->where('type','product')->get() as $shoppingcart_rates)
									{
										$product_subtotal += $shoppingcart_rates->subtotal;
										$product_discount += $shoppingcart_rates->discount;
										$product_total += $shoppingcart_rates->total;
									}
									?>
                                    @if($product_discount>0)
                                    	<strike class="text-muted">{{ $product_subtotal }}</strike><br><b>{{ $product_total }}</b>
                                    @else
                    					<b>{{ $product_total }}</b>
                    				@endif
                                </div>
                			 </div>
                    
                    		 <div class="row mb-4">
                             <div class="col-10 row">
                				<div class="ml-4 mb-2">
                               		@if(isset($shoppingcart_product->image))
                    				<img class="img-fluid" width="55" src="{{ $shoppingcart_product->image }}">
                                	@endif
                    			</div>
                    			<div class="col-8" style="font-size:12px; margin-left:-5px">
                                	{{ $ProductHelper->datetotext($shoppingcart_product->date) }}
                                	<br>
                                    {{ $shoppingcart_product->rate }}
                                    <br>
                                    @foreach($shoppingcart_product->shoppingcart_rates()->where('type','product')->get() as $shoppingcart_rates)
                                    	
                                        	{{ $shoppingcart_rates->qty }} x {{ $shoppingcart_rates->unitPrice }} ({{ $shoppingcart_rates->price }})
                                    	
                                        <br>
                                    @endforeach
                                </div>
                			</div>
                            <div class="col text-right">
                            	<button id="remove-{{ $shoppingcart_product->booking_id }}" onClick="REMOVE({{ $shoppingcart_product->booking_id }});" class="btn btn-sm btn-danger"><i class="fa fa-trash-alt fa-sm"></i></button>
                            </div>
                            </div>
                            <!-- Product detail booking -->
                            <!-- Pickup booking $activity -->
                            @php
							$pickups = $shoppingcart_product->shoppingcart_rates()->where('type','pickup')->get();
                            @endphp
                            @if(count($pickups))
                            <div class="card mb-2">
                        		<div class="card-body">
                               		@foreach($pickups as $shopppingcart_rates)
									<div class="row mb-2">
                						<div class="col-8">
                                        <strong style="font-size:12px;">Pick-up and drop-off services</strong>
                                        <br>
                                        <span style="font-size:12px;">{{ $shopppingcart_rates->unitPrice }}</span>
                    					</div>
                    					<div class="col-4 text-right">
                    						@if($shopppingcart_rates->discount > 0)
                                            	<strike class="text-muted">{{ $shopppingcart_rates->subtotal }}</strike><br><b>{{ $shopppingcart_rates->total }}</b>
                                            @else
                                            	<b>${{ $shopppingcart_rates->subtotal }}</b>
                    						@endif
                                        </div>
                					</div>
                               		@endforeach
								</div>
                   			</div>
							@endif
                            <!-- Pickup booking $activity -->
							
                            <!-- Extra booking $activity -->
                            @php
                            $extra = $shoppingcart_product->shoppingcart_rates()->where('type','extra')->get();
                            @endphp
                            @if(count($extra))
							<div class="card mb-2">
                            
                        		<div class="card-body">
                                <div class="row col-12 mb-2">
                            		<strong>Extras</strong>
                            	</div>
                                @foreach($extra as $shoppingcart_rates)
									<div class="row mb-2">
                						<div class="col-8">
										{{ $shoppingcart_rates->title }}
                    					</div>
                    					<div class="col-4 text-right">
                                        	@if($shopppingcart_rates->discount > 0)
                                            	<strike class="text-muted">{{ $shopppingcart_rates->subtotal }}</strike><br><b>{{ $shopppingcart_rates->total }}</b>
                                            @else
                    							<b>${{ $shoppingcart_rates->subtotal }}</b>
                                            @endif
                    					</div>
                					</div>
                               @endforeach
								</div>
                   			</div>
							<!-- Extra booking -->
                            @endif
                            
				</div>
                <!-- Product booking -->
                <?php
				$grand_subtotal += $shoppingcart_product->subtotal;
				$grand_discount += $shoppingcart_product->discount;
				$grand_total += $shoppingcart_product->total;
				?>
                
                
                @endforeach
                <div class="card-body pt-0 mt-0">
                    <hr>
                    <div class="row mb-2">
                        <div class="col-8">
                            <span style="font-size:18px">Items</span>
                        </div>
                        <div class="col-4 text-right">
                            <span style="font-size:18px">{{ $grand_subtotal }}</span>
                        </div>
                    </div>
                    @if($grand_discount>0)
                    <div class="row mb-2">
                        <div class="col-8">
                            <span style="font-size:18px">Discount</span>
                        </div>
                        <div class="col-4 text-right">
                            <span style="font-size:18px">{{ $grand_discount }}</span>
                        </div>
                    </div>
                    @endif
                    <div class="row mb-2">
                        <div class="col-8">
                            <b style="font-size:18px">Total ({{ $shoppingcart->currency }})</b>
                        </div>
                        <div class="col-4 text-right">
                            <b style="font-size:18px">{{ $grand_total }}</b>
                        </div>
                    </div>
                </div>
                
                @if($shoppingcart->due_on_arrival>0)
                <div class="card-body pt-0">
                    <hr class="mt-0"> 
                    <div class="row mb-2 mt-0">
                        <div class="col-8">
                            <b style="font-size:18px">Due now ({{ $shoppingcart->currency }})</b>
                        </div>
                        <div class="col-4 text-right">
                           <b style="font-size:18px">{{ $shoppingcart->due_now }}</b>
                        </div>
                    </div>
                    <div class="row mb-4 mt-0">
                        <div class="col-8">
                            <span style="font-size:18px">Due on arrival ({{ $shoppingcart->currency }})</span>
                        </div>
                        <div class="col-4 text-right">
                            <span style="font-size:18px">{{ $shoppingcart->due_on_arrival }}</span>
                        </div>
                    </div>
                </div>
                @endif

                </div>
<!-- ################################################################### -->
@if(!isset($shoppingcart->promo_code))
<script language="javascript">
function PROMOCODE()
{
	$('#alert-promocode-success').fadeOut("slow");
	$('#alert-promocode-failed').fadeOut("slow");
	$("#apply").attr("disabled", true);
	$("#promocode").attr("disabled", true);
	$('#apply').html('<i class="fa fa-spinner fa-spin"></i>');
	
	$.ajax({
		data: {
        	"_token": $("meta[name=csrf-token]").attr("content"),
			"promocode": $('#promocode').val(),
            "sessionId": '{{$shoppingcart->session_id}}',
        },
		type: 'POST',
		url: '/snippets/promocode'
		}).done(function( data ) {
			if(data.id=="1")
			{
				window.location.href = '{{route('route_toursdk_booking.index')}}/checkout';
				$('#alert-promocode').hide();
                $('#alert-promocode').html('<div id="alert-promocode-success" class="alert alert-primary text-center" role="alert"><i class="far fa-smile"></i> Promo code applied</div>');
                $('#alert-promocode').fadeIn("slow");
			}
			else
			{
				$('#promocode').val('');
                $('#alert-promocode').hide();
                $('#alert-promocode').html('<div id="alert-promocode-failed" class="alert alert-danger text-center" role="alert"><i class="far fa-frown"></i> Promo code not valid</div>');
                $('#alert-promocode').fadeIn("slow");
                $("#promocode").attr("disabled", false);
                $("#apply").attr("disabled", false);
                $('#apply').html('Apply');
			}
		});
	
	
	return false;
}
</script>
<!-- ################################################################### -->

                <div class="card mt-4">
                	<div class="card-body">
                    		<div id="alert-promocode"></div>
                    	<form onSubmit="PROMOCODE(); return false;" class="form-inline">
  							<div class="form-row align-items-center">
    							<div class="col-auto">
      								<input type="text" class="form-control" id="promocode" placeholder="Promo code" required>
    							</div>
    							<div class="col-auto">
      								<button id="apply" type="submit" class="btn btn-secondary ">Apply</button>
    							</div>
  							</div>
						</form>
                	</div>
                </div>
 <!-- ################################################################### --> 
 @else
 <script>
$( document ).ready(function() {
	$('#alert-promocode-failed').hide();
});
</script>
<script language="javascript">
function DELETE()
{
	$("#apply").attr("disabled", true);
	$('#apply').html('<i class="fa fa-spinner fa-spin"></i>');
	
	$.ajax({
		data: {
        	"_token": $("meta[name=csrf-token]").attr("content"),
            "sessionId": '{{$shoppingcart->session_id}}',
        },
		type: 'POST',
		url: '/snippets/promocode/remove'
		}).done(function( data ) {
			if(data.id=="1")
			{
				window.location.href = '{{route('route_toursdk_booking.index')}}/checkout';
				$('#alert-promocode').hide();
                $('#alert-promocode').html('<div id="alert-promocode-failed" class="alert alert-danger text-center" role="alert"><i class="far fa-frown"></i> Promo code removed</div>');
                $('#alert-promocode').fadeIn("slow");
			}
		});
	
	
	return false;
}
</script>
<div class="card shadow mt-4">
	<div class="card-body">
            <div id="alert-promocode"></div>
    	<div class="row mb-2">
        	<div class="col-8 my-auto">
				<strong>Promo code : {{ $shoppingcart->promo_code }}</strong>
			</div>
			<div class="col-4 my-auto text-right">
				<button id="apply" type="button" onClick="DELETE();" class="btn btn-sm btn-danger"><i class="fa fa-trash-alt"></i></button>
			</div>
		</div>	
	</div>
</div>
 @endif         
<!-- ################################################################### -->

                <button type="button" class="btn btn-secondary mt-4"  onclick="CREATE(); return false;"><b class="fa fa-plus-square"></b> Add product to Booking</button>
            	</div>
                

            <div class="col-lg-6 col-lg-auto mb-6 mt-4">
            <div class="card mb-8 p-2">
 				 <div class="card-body" style="padding-left:10px;padding-right:10px;padding-top:10px;padding-bottom:15px;">
                 
<form onSubmit="STORE(); return false;">             
<!-- ########################################### -->
<h3>Booking Channel</h3>
<div class="form-group">
<label for="bookingChannel"><strong>Channel</strong></label>
<select style="font-size:16px;height:47px;"  class="form-control" id="bookingChannel" name="bookingChannel">
        <option value="Internal Booking">Internal Booking</option>
        @foreach($channels as $channel)
        <option value="{{$channel->name}}">{{$channel->name}}</option>
        @endforeach
</select>
</div>
<h3>Main Contact</h3>   
	@php
    	$main_contacts = $shoppingcart->shoppingcart_questions()->where('type','mainContactDetails')->orderBy('order')->get()
    @endphp
    @foreach($main_contacts as $main_contact)        
<div class="form-group">
	<label for="{{ $main_contact->id }}"><strong>{{ $main_contact->label }}</strong></label>
    @if($main_contact->data_format=="EMAIL_ADDRESS")
	<input name="{{ $main_contact->id }}" value="{{ $main_contact->answer }}" type="email" class="form-control" id="{{ $main_contact->id }}" style="height:47px;">
    @elseif($main_contact->data_format=="PHONE_NUMBER")
    <input name="{{ $main_contact->id }}" value="{{ $main_contact->answer }}" type="tel" class="form-control" id="{{ $main_contact->id }}" style="height:47px;">
    @else
    @if($main_contact->selectOption)
    <select style="font-size:16px;height:47px;"  class="form-control" id="{{ $main_contact->id }}" name="{{ $main_contact->id }}">
    	<option value=""></option>
    	@foreach($main_contact->shoppingcart_question_options()->orderBy('order')->get() as $shoppingcart_question_option)
    	<option value="{{ $shoppingcart_question_option->value }}" {{ $shoppingcart_question_option->answer==1 ? "selected" : "" }}>{{ $shoppingcart_question_option->label }}</option>
        @endforeach
    </select>
    @else
    <input name="{{ $main_contact->id }}" value="{{ $main_contact->answer }}" type="text" class="form-control" id="{{ $main_contact->id }}" style="height:47px;">
    @endif
    @endif
</div>
	@endforeach
<!-- ########################################### --> 
    @foreach($shoppingcart->shoppingcart_products()->get() as $shoppingcart_products)
    @php
    	$activityBookings = $shoppingcart->shoppingcart_questions()->where('booking_id',$shoppingcart_products->booking_id)->whereNotNull('booking_id')->orderBy('order')->get();
    @endphp
    @if(count($activityBookings))
    <h2>{{ $shoppingcart_products->title }}</h2>
    
    @foreach($activityBookings as $activityBooking)
    <div class="form-group">
	<label for="{{ $activityBooking->id }}"><strong>{{ $activityBooking->label }}</strong></label>
    @if($activityBooking->selectOption)
    <select style="font-size:16px;height:47px;" class="form-control" id="{{ $activityBooking->id }}" name="{{ $activityBooking->id }}">
    	<option value=""></option>
    	@foreach($activityBooking->shoppingcart_question_options()->orderBy('order')->get() as $shoppingcart_question_option)
    	<option value="{{ $shoppingcart_question_option->value }}" {{ $shoppingcart_question_option->answer==1 ? "selected" : "" }}>{{ $shoppingcart_question_option->label }}</option>
        @endforeach
    </select>
    @else
    <input type="text" id="{{ $activityBooking->id }}" value="{{ $activityBooking->answer }}" style="height:47px;" name="{{ $activityBooking->id }}" class="form-control">
    @endif
    @if(isset($activityBooking->help))
    <small class="form-text text-muted">{{$activityBooking->help}}</small>
    @endif
	</div>
    @endforeach
    @endif
    @endforeach
<!-- ########################################### -->    


<button id="submit" type="submit" style="height:47px;" class="btn btn-lg btn-block btn-primary"><i class="fas fa-save"></i> Save</button>
</form>


			</div>
            </div>
            </div>
        	</div>
				<div style="height:40px;"></div>		
				</div>
			</div>
        </div>
	</div>
<script language="javascript">
function STORE()
{
	var error = false;
	$("#submit").attr("disabled", true);
	$('#submit').html('<i class="fa fa-spinner fa-spin"></i>');
	var input = [
				
				@php
    			$main_contacts = $shoppingcart->shoppingcart_questions()->where('type','mainContactDetails')->orderBy('order')->get()
    			@endphp
    			@foreach($main_contacts as $main_contact)
					"{{ $main_contact->id }}",
				@endforeach
				
				@php
    			$activityBookings = $shoppingcart->shoppingcart_questions()->where('type','activityBookings')->orderBy('order')->get();
    			@endphp
				@if(count($activityBookings))
    				@foreach($activityBookings as $activityBooking)
						"{{ $activityBooking->id }}",
					@endforeach
				@endif
				@php
    			$pickup_questions = $shoppingcart->shoppingcart_questions()->where('type','pickupQuestions')->orderBy('order')->get();
    			@endphp
    			@if(count($pickup_questions))
					@foreach($pickup_questions as $pickup_question)
					"{{ $pickup_question->id }}",
					@endforeach
				@endif
				
	];
	
	$.each(input, function( index, value ) {
  		$('#'+ value).removeClass('is-invalid');
  		$('#span-'+ value).remove();
	});
	
	
	$.ajax({
		data: {
        	"_token": $("meta[name=csrf-token]").attr("content"),
            "bookingChannel": $("#bookingChannel").val(),
            "skip_payment": true,
            "sessionId": '{{$shoppingcart->session_id}}',
			
				@php
    			$main_contacts = $shoppingcart->shoppingcart_questions()->where('type','mainContactDetails')->orderBy('order')->get()
    			@endphp
    			@foreach($main_contacts as $main_contact)
					"{{ $main_contact->id }}": $('#{{ $main_contact->id }}').val(),
				@endforeach
				
				@php
    			$activityBookings = $shoppingcart->shoppingcart_questions()->where('type','activityBookings')->orderBy('order')->get();
    			@endphp
				@if(count($activityBookings))
    				@foreach($activityBookings as $activityBooking)
						"{{ $activityBooking->id }}": $('#{{ $activityBooking->id }}').val(),
					@endforeach
				@endif
				@php
    			$pickup_questions = $shoppingcart->shoppingcart_questions()->where('type','pickupQuestions')->orderBy('order')->get();
    			@endphp
    			@if(count($pickup_questions))
					@foreach($pickup_questions as $pickup_question)
					"{{ $pickup_question->id }}": $('#{{ $pickup_question->id }}').val(),
					@endforeach
				@endif
			
        },
		type: 'POST',
		url: '/snippets/shoppingcart/checkout'
		}).done(function( data ) {
			
			if(data.id=="1")
			{
				window.location.href = '{{route('route_toursdk_booking.index')}}';
			}
			else
			{
				$.each( data, function( index, value ) {
					$('#'+ index).addClass('is-invalid');
						if(value!="")
						{
							$('#'+ index).after('<span id="span-'+ index  +'" class="invalid-feedback" role="alert"><strong>'+ value +'</strong></span>');
						}
					});
					
				$("#submit").attr("disabled", false);
				$('#submit').html('<i class="fas fa-save"></i> Save');
				
			}
		});
	
	
	return false;
}
</script>


		
                </div>
            </div>
        </div>
 </div>
@endsection
