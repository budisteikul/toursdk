@inject('BokunHelper', budisteikul\toursdk\Helpers\BokunHelper)
@inject('ImageHelper', budisteikul\toursdk\Helpers\ImageHelper)
@extends('toursdk::layouts.frontend.app')
@section('title',$category->name)
@section('content')
<section id="tour" style="background-color:#ffffff">
<div class="container">
	<div class="row">
		<div class="col-lg-12 col-md-12 mx-auto">
            <div class="row" style="padding-bottom:0px;">
                <div class="col-lg-12 text-center">
                    <div style="height:70px;"></div>
                    <h3 class="section-heading" style="margin-top:0px;">{{ $category->name }}</h3>
                    <hr class="hr-theme">
                    <div style="height:30px;"></div>
                </div>
            </div>
			<div class="row" style="padding-bottom:0px;">
				<div class="col-lg-12 text-center">	
        			<div class="row">
        				@foreach($category->products as $product)
                        @php
                            $content = $BokunHelper->get_product($product->bokun_id);
                        @endphp
        					<div class="col-lg-4 col-md-6 mb-4">
    							<div class="card h-100 shadow card-block rounded">
                            		
  				 					<div class="container-book">
                                        <a href="/tour/{{ $product->slug }}" class="text-decoration-none"><img class="card-img-top image-book" src="{{ $ImageHelper->cover($product) }}" alt="{{ $product->name }}"></a>
                                    <div class="middle-book">
                                            <a href="/tour/{{ $product->slug }}" class="btn btn-theme btn-md p-3" style="border-radius:0;">BOOK NOW</a>
                                    </div>
                                    </div>
                                    
                                    
  									<div class="card-header bg-white border-0 text-left pb-0">
        								<h3 class="mb-4"><a href="/tour/{{ $product->slug }}" class="text-dark text-decoration-none">{{ $product->name }}</a></h3>
      								</div>
                            		@if($content->excerpt!="")
									<div class="card-body pt-0">
										<p class="card-text text-left">{!!$content->excerpt!!}</p>
  									</div>
                                	@endif
									<div class="card-body pt-0">
    									<p class="card-text text-left text-muted"><i class="far fa-clock"></i> Duration : {{ $content->durationText }}</p>
  									</div>
  									<div class="card-footer bg-white pt-0" style="border:none;">
                                		<div class="d-flex align-items-end mb-2">
  											<div class="p-0 ml-0">
                                    			<div class="text-left">
                                    				<span class="text-muted">Price from</span>
                                    			</div>
                                    			<div>
                                    				<b style="font-size: 24px;">{{$content->nextDefaultPriceAsText}}</b>
                                    			</div>
                                    		</div>
  											<div class="ml-auto p-0">
                                    			<a href="/tour/{{ $product->slug }}" class="btn btn-theme btn-md "><i class="fas fa-info-circle"></i> More info</a>
                                    		</div>
										</div>
  									</div>
								</div>
    						</div>
							@endforeach
					</div>
					<div style="height:25px;"></div>		
				</div>
			</div>
		</div>
	</div>
</div>
</section>
@endsection