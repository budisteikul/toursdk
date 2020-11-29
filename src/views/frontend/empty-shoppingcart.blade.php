@extends('toursdk::layouts.frontend.app')
@section('title','Shopping Cart')
@section('content')
@include('toursdk::layouts.loading')


<section id="booking" style="background-color:#ffffff">
<div class="container">
	<div class="row">
		<div class="col-lg-12 col-md-12 mx-auto">
			<div class="row" style="padding-bottom:0px;">
				<div class="col-lg-12 text-left">
					<div style="height:77px;"></div>
           			<div class="card mb-8 shadow p-2">
                     	<div class="card-body" style="padding-left:10px;padding-right:10px;padding-top:10px;padding-bottom:15px;">
                 			<div class="text-right">
		   		
				 			</div>
				 			<!-- ##### -->
							
					
                 			<!-- ##### -->
                 			<div class="row col-md-12  mx-auto d-flex justify-content-center">
								<div class="textwidget my-auto" style=" min-height:450px;">
                                <div style="height:50px;"></div>
					 			<!-- ##### content ############################################################## -->
                                <br>
                                <br>
                                <center>
                                <i class="fas fa-shopping-cart fa-10x"></i><br><br>
                                <b>Your shopping cart is empty</b>
                            </center>
					 			<!-- ##### content ############################################################## -->
                                <div style="height:50px;"></div>
								</div>
							</div>
                 			<!-- ##### --> 
						</div>
					</div>
					<div style="height:40px;"></div>		
				</div>
			</div>
		</div>
	</div>
</div>
</section>
@endsection
