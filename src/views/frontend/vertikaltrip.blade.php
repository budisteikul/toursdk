@inject('BokunHelper', budisteikul\toursdk\Helpers\BokunHelper)
@inject('ImageHelper', budisteikul\toursdk\Helpers\ImageHelper)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@hasSection('description')@yield('description')@else Hi we are from the Vertikal Trip team, we will give you complete Yogyakarta atmosphere, tradition, food, and culture. Along the journey we will accompany you so you can feel the real with locals experience with us, share our stories, experiences and traditions. @endif">
    <meta name="author" content="Vertikal Trip">
    <meta name="robots" content="all,index,follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/manifest.json">
  <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#2e3237">
  <meta name="msapplication-TileColor" content="#2e3237">
  <meta name="theme-color" content="#2e3237">
    <title>Book Amazing Things to Do With VERTIKAL TRIP</title>
    <link href="https://fonts.googleapis.com/css?family=Barlow:400,700" rel="stylesheet" type="text/css" media="screen,handheld">
  <script src="/js/vertikaltrip-3.1.1.js"></script>
  <link href="/css/vertikaltrip-3.1.4.css" rel="stylesheet" media="screen,handheld">
  <script
    src="https://www.paypal.com/sdk/js?client-id={{ env('PAYPAL_CLIENT_ID') }}&intent=authorize&currency={{ env('BOKUN_CURRENCY') }}"  data-csp-nonce="xyz-123">
</script>  
   
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class=" bg-white">

<!-- Navigation -->
<nav class="navbar navbar-default navbar-expand-lg navbar-dark fixed-top mb-5" id="mainNav">
  <div class="container">
    <a href="/"><img id="brand" src="/img/logo.png" alt="VERTIKAL TRIP" height="50"  style="margin-top:2px;margin-bottom:2px;"></a>
    <!-- ##############################################################  -->
    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span> <span style="font-size:13px; color:#FFFFFF">TOURS</span>
    </button>
      <div class="collapse navbar-collapse stroke" id="navbarResponsive">
      <ul class="navbar-nav text-uppercase ml-auto mb-1">
            @foreach($categories as $category)
            <li class="nav-item">
               <a class="nav-link menu-hover" href="/tours/{{ $category->slug }}">{{ $category->name }}</a>
            </li>
            @endforeach
            
      </ul>
    </div>
    <!-- ##############################################################  -->
    </div>
</nav>

<!-- Header Section -->
<header id="page-top" class="intro-header" style="background-image: url('/img/background.jpg'); background-color: #000000">
	<div class="col-lg-8 col-md-10 mx-auto">
		<div class="site-heading text-center ">
			<div class="transbox" style=" min-height:100px; padding-top:5px; padding-bottom:35px; padding-left:10px; padding-right:10px;">
				<h1 id="title" style="text-shadow: 2px 2px #555555; font-size:36px">Book Amazing Things to Do With VERTIKAL TRIP</h1>
                <hr class="hr-theme">
                <a class="btn btn-lg btn-theme js-scroll-trigger" href="/#tour" style="border-radius:0;">DISCOVER TOURS</a>
			</div>
            <i class="fa fa-angle-down infinite animated fadeInDown" style="font-size: 50px; color:#FFFFFF; margin-top:30px"></i>
		</div>
    </div>
</header>


<section class="page-section bg-light" id="services">
    <div class="container">
      <div style="height:25px;"></div>
        <div class="row">
          <div class="col-lg-3 col-md-6 text-center">
              <div class="mt-5 mb-2">
                <i class="fa fa-4x fa-bolt text-theme mb-2"></i>
                <h3 class="h4 mb-2">Instant Confirmation</h3>
                <p class="text-muted mb-0">To secure your spot while keeping your plans flexible. Your booking are confirmed automatically!</p>
              </div>
          </div>
          <div class="col-lg-3 col-md-6 text-center">
              <div class="mt-5 mb-2">
          <i class="fas fa-4x fa-phone-alt text-theme mb-2"></i>
                <h3 class="h4 mb-2">24/7 Support</h3>
                <p class="text-muted mb-0">Stay Connected with us! With 24/7 Support.</p>
              </div>
          </div>
          <div class="col-lg-3 col-md-6 text-center">
              <div class="mt-5 mb-2">
                <i class="fas fa-4x fa-history text-theme mb-2"></i>
                <h3 class="h4 mb-2">Free Cancellation</h3>
                <p class="text-muted mb-0">Have your plans changed? No worries! You can cancel the booking anytime up to 24 hours before your experience!</p>
              </div>
          </div>
          <div class="col-lg-3 col-md-6 text-center">
              <div class="mt-5 mb-2">
                <i class="fab fa-4x fa-paypal text-theme mb-2"></i>
                <h3 class="h4 mb-2">Secure Payments</h3>
                <p class="text-muted mb-0">You can pay online using your credit card or Paypal. We will make it secure and simple!</p>
              </div>
          </div>
        </div>
        <div style="height:45px;"></div>  
  </div>
</section>


@foreach($categories as $category)
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
                            <a href="/tour/{{ $product->slug }}" class="text-decoration-none"><img class="card-img-top image-book" src="{{ $ImageHelper->cover($product) }}" alt="{{ $product->title }}"></a>
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
              <div class="card-footer bg-white pt-0" style="border:none">
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
                                      <a href="/tour/{{ $product->slug }}" class="btn btn-theme btn-md"><i class="fas fa-info-circle"></i> More info</a>
                                    </div>
                </div>
              </div>
                  
            </div>
            </div>
          @endforeach
        </div>
        <div style="height:45px;"></div>    
        </div>
      </div>
    </div>
  </div>
</div>
</section>
@endforeach


<script type="text/javascript">
jQuery(document).ready(function($) {  
var table = $('#dataTables-example').DataTable(
{
  "processing": true,
  "serverSide": true,
  "ajax": 
  {
    "url": "/reviews",
    "type": "POST",
    "headers": {
          'X-CSRF-TOKEN': $("meta[name=csrf-token]").attr("content")
        }
  },
  "scrollX": true,
  "language": 
  {
    "paginate": 
    {
      "previous": "<i class='fa fa-step-backward'></i>",
      "next": "<i class='fa fa-step-forward'></i>",
      "first": "<i class='fa fa-fast-backward'></i>",
      "last": "<i class='fa fa-fast-forward'></i>"
    },
    "aria": 
    {
      "paginate": 
      {
        "first":    'First',
        "previous": 'Previous',
        "next":     'Next',
        "last":     'Last'
      }
    }
  },
  "pageLength": 5,
  "order": [[ 0, "desc" ]],
  "columns": [
    {data: 'date', name: 'date', orderable: true, searchable: false, visible: false},
    {data: 'style', name: 'style', className: 'auto', orderable: false},
    ],
  "dom": 'rtp',
  "pagingType": "full_numbers",
  "fnDrawCallback": function () {
          
  }
});
      
  var table = $('#dataTables-example').DataTable();
  $('#dataTables-example').on('page.dt', function(){
  var target = $('#review');
  target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
  if (target.length) {
  $('html, body').animate({
    scrollTop: (target.offset().top - 54)
  }, 1000, "easeInOutExpo");
    return false;
  }
  });
      
      });     
</script>


<section id="review" style="background-color:#ffffff">
<div class="container mb-6">
  <div class="row">
      <div class="col-lg-8 col-md-10 mx-auto">
      <div class="col-lg-12 text-center">
        <h3 class="section-heading" style="margin-top:50px;">How Our New Friend Talk About The Tour</h3>
        <h4 class="section-subheading text-muted"><a href="/review" target="_blank" class="text-theme"><i class="fab fa-tripadvisor" aria-hidden="true"></i>  Review us on Trip Advisor</a></h4>
        <strong> Rating :</strong>
        <span class="text-warning">
          <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i> <span class="text-secondary" itemprop="ratingValue">(4.9)</span>
        </span>‎
        <br>
        <small class="form-text text-muted">Based on <span itemprop="reviewCount">{{ $count }}</span> our new friend reviews</small>
        <hr class="hr-theme">
      </div>
      <table id="dataTables-example" style="width:100%">
        <tbody>           
        </tbody>
      </table>
    </div>
    </div>
</div>
<div style="height:50px;"></div>
</section>


<script>
(function($) {
  "use strict";
  $('a.js-scroll-trigger[href*="#"]:not([href="#"])').click(function() {
    if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
      if (target.length) {
        $('html, body').animate({
          scrollTop: (target.offset().top - 54)
        }, 1000, "easeInOutExpo");
        return false;
      }
    }
  });
  
  $('body').scrollspy({
    target: '#mainNav',
    offset: 75
  });
  
  $('.js-scroll-trigger').click(function() {
    $('.navbar-collapse').collapse('hide');
  });

  var navbarCollapse = function() {
    if ($("#mainNav").offset().top > 100 && $(window).width() > 768) {
      $("#mainNav").addClass("navbar-shrink shadow");
	  //$("#brand").attr("src", "/img/logo-blue.png");
    } else {
      $("#mainNav").removeClass("navbar-shrink shadow");
	  //$("#brand").attr("src", "/img/logo.png");
    }
  };
  
  navbarCollapse();
  
  $(window).scroll(navbarCollapse);
  
})(jQuery);
</script>

<footer class="py-5" style="font-size:16px; background-color:#f2f2f2">
<div class="container">
    <div class="row">
    <div class="row col-md-12">
              <div class="col-sm-4 first-column mb-4">
                  <p class="m-0 text-left text-dark">
            <img src="/img/logo-dark.png" alt="VERTIKAL TRIP LLC" height="50"  style="margin-top:2px;margin-bottom:2px;">
            <br>
            <b>INFO AND RESERVATION</b>
            <br>
            We're happy to help
            <br>
            <span class="fab fa-whatsapp"></span> Whatsapp : <a class="badge badge-theme no-decoration" href="https://wa.me/+6285743112112">+62 857-4311-2112</a> <br>
            <span class="far fa-envelope"></span> Email : <a href="mailto:guide@vertikaltrip.com" class="badge badge-theme no-decoration" target="_blank">guide@vertikaltrip.com</a>
                    </p>
                </div>
                <div class="col-sm-4 second-column mb-4">
                  <p class="m-0 text-left text-dark">
                      <b>TERMS AND POLICY</b>
                      <br>
            <a target="_blank" class="text-theme" href="/page/terms-and-conditions" style="margin-top:10px;">Terms and Conditions</a>
                        <br>
                        <a target="_blank" class="text-theme" href="/page/privacy-policy">Privacy Policy</a>
                        <br>
          </p>
                    <p class="mt-4 text-left text-dark">
                    <div style="margin-bottom:5px;">
            <b>FOLLOW US</b>
          </div>
                    <div>
<a target="_blank" href= 'https://www.tripadvisor.com/Attraction_Review-g14782503-d17523331-Reviews-Vertikal_Trip-Yogyakarta_Yogyakarta_Region_Java.html' class="btn btn-social-icon btn-tripadvisor"><i class="fab fa-tripadvisor fa-2x text-white"></i></a>
<a target="_blank" href='https://www.airbnb.com/users/show/225353316' class="btn btn-social-icon btn-airbnb"><i class="fab fa-airbnb fa-2x text-white"></i></a>
<a target="_blank" href='https://www.facebook.com/vertikaltrip' class="btn btn-social-icon btn-facebook"><i class="fab fa-facebook fa-2x text-white"></i></a>
<a target="_blank" href='https://www.instagram.com/vertikaltrip' class="btn btn-social-icon btn-instagram"><i class="fab fa-instagram fa-2x text-white"></i></a>
          </div>
          </p>
                </div>
                <div class="col-sm-4 second-column mb-4">
                    <p class="text-left text-dark">
                        <b>PAYMENT CHANNEL</b>
                      <br>
            <img class="mb-2 mt-2" src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/cc-badges-ppmcvdam.png" alt="Credit Card Badges">
                        <br>
                        <small style="font-size:11px;"> 2020 &copy; VERTIKAL TRIP</small>
          </p>
                </div>
        </div>
    </div>
</div>
</footer>
<!-- a href="https://wa.me/+6285743112112" class="wa-float text-white" target="_blank">
<i class="fab fa-whatsapp wa-icon-float"></i -->
</a>
</body>
<script src="/assets/javascripts/apps/build/App-3.1.0.js"></script>
<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/5d1810cb22d70e36c2a3697f/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
</html>