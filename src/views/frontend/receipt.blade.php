@extends('toursdk::layouts.frontend.app')
@section('title','Receipt')
@section('content')
<section id="booking" style="background-color:#ffffff">
<div class="container">
	<div class="row">
		<div class="col-lg-12 col-md-12 mx-auto">
			<div class="row" style="padding-bottom:0px;">
				<div class="col-lg-12 text-left">
				<div style="height:70px;"></div>
			
			<div class="card mb-8 shadow p-2 mt-2">
 				 <div class="card-body" style="padding-left:10px;padding-right:10px;padding-top:10px;padding-bottom:15px;">
				 <div class="col-md-12  mx-auto text-left">
				 <p>
                        <h4>Your booking references is {{ $shoppingcart->confirmation_code }}</h4>
						
						Thank you for your booking with <b>{{env('APP_NAME')}}</b>, a confirmation will be sent to your email address.
						</p>
				</div>
				 </div>
			</div>

			
			<div class="row mb-2">
			<!-- ################################################################### --> 
			<div class="col-lg-6 col-lg-auto mb-6 mt-4">
            	  
                <div class="card shadow">
					<div class="card-header bg-dark text-white pb-1">
						<h4><i class="fas fa-user-tie"></i> Customer Info</h4>
					</div>
                
					<div class="card-body">
                		
                       
                        <p>
						<h3>Name</h3>
						{{ $shoppingcart->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','firstName')->first()->answer }}
                        {{ $shoppingcart->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','lastName')->first()->answer }} 
                        <h3>Phone</h3>
						{{ $shoppingcart->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','phoneNumber')->first()->answer }} 
                        <h3>Email</h3>
						{{ $shoppingcart->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','email')->first()->answer }} 
                        </p>
						
					</div>
					
				</div>
			</div>
			<!-- ################################################################### -->
			<div class="col-lg-6 col-lg-auto mb-6 mt-4">
            	  
                <div class="card shadow">
					<div class="card-header bg-dark text-white pb-1">
						<h4><i class="fas fa-file"></i> Travel Documents</h4>
					</div>
                
					<div class="card-body">
                	 
                        <p>
						<h3>Receipt</h3>
						<a target="_blank" class="text-theme" href="/snippets/pdf/invoice/{{ $shoppingcart->session_id }}/Invoice-{{ $shoppingcart->confirmation_code }}.pdf"><i class="fas fa-file-invoice"></i> Invoice-{{ $shoppingcart->confirmation_code }}.pdf</a>
						<h3>Experience tickets</h3>
                       	@foreach($shoppingcart->shoppingcart_products()->get() as $shoppingcart_product)
                        <a target="_blank" class="text-theme" href="/snippets/pdf/ticket/{{$shoppingcart->session_id}}/Ticket-{{$shoppingcart_product->product_confirmation_code}}.pdf"><i class="fas fa-ticket-alt"></i> Ticket-{{ $shoppingcart_product->product_confirmation_code }}.pdf</a>
                        <br>
                        @endforeach
                        </p>
							
					</div>
					
				</div>
			</div>
			<!-- ################################################################### -->			
			</div>
				<div style="height:70px;"></div>		
				</div>
			</div>
        </div>
	</div>
</div>
</section>
@endsection