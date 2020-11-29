@extends('toursdk::layouts.frontend.app')
@section('title',$product->name)
@section('content')
<section id="booking" style="background-color:#ffffff">
<div class="container">
	<div class="row">
		<div class="col-lg-12 mx-auto">
			<div class="row" style="padding-bottom:0px;">
				<div class="col-lg-12 text-left">
				<div style="height:77px;"></div>
				
           <div class="card mb-12 shadow">
  				<div class="card-header bg-dark text-white pb-1">
    				<h4><i class="fas fa-calendar-alt"></i> Select Date and Travelers</h4>
  				</div>
 				 <div class="card-body" style="padding-left:10px;padding-right:10px;padding-top:15px;padding-bottom:15px;">
                
                @include('toursdk::frontend.calendar')
				
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