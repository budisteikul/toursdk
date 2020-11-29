@inject('Category', budisteikul\toursdk\Models\Category)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@hasSection('description')@yield('description')@else @if(str_ireplace("www.","",$_SERVER['HTTP_HOST'])=="jogjafoodtour.com")Enjoy Jogja in Local Ways. Join us on this experience to try authentic Javanese dishes, play traditional games, travel on a becak, learn interesting fun facts about city, interact with locals and many more. @else Hi we are from the Vertikal Trip team, we will give you complete Yogyakarta atmosphere, tradition, food, and culture. Along the journey we will accompany you so you can feel the real with locals experience with us, share our stories, experiences and traditions.@endif @endif">
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
    <title>@hasSection('title')@yield('title')@endif</title>
    <link href="https://fonts.googleapis.com/css?family=Barlow:400,700" rel="stylesheet" type="text/css" media="screen,handheld">
	<script src="/js/vertikaltrip-3.1.1.js"></script>
	<link href="/css/vertikaltrip-3.1.4.css" rel="stylesheet" media="screen,handheld">
    <script
    src="https://www.paypal.com/sdk/js?client-id={{ env('PAYPAL_CLIENT_ID') }}&intent=authorize&currency={{ env('BOKUN_CURRENCY') }}"  data-csp-nonce="xyz-123">
</script>
    @stack('scripts')
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class=" bg-white">
@if(str_ireplace("www.","",$_SERVER['HTTP_HOST'])=="jogjafoodtour.com")
<nav class="navbar navbar-default navbar-expand-lg navbar-dark fixed-top shadow mb-5" id="mainNav-back">
    <div class="container">
        <a href="/"><img src="/img/jogjafoodtour.png" alt="JOGJA FOOD TOUR" height="50"  style="margin-top:9px;margin-bottom:9px;"></a>
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse stroke" id="navbarResponsive">
            <ul class="navbar-nav text-uppercase ml-auto mb-1">
                <li class="nav-item">
                    <a class="nav-link js-scroll-trigger" href="/#services">Why Jogja Food Tour?</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link js-scroll-trigger" href="/#about">The Tour</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link js-scroll-trigger" href="/#gallery">Snapshot</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link js-scroll-trigger" href="/#guide">Tour Guide</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link js-scroll-trigger" href="/#review">Reviews</a>
                </li>

               

            </ul>
        </div>
    </div>
</nav>    
@else
<nav class="navbar navbar-default navbar-expand-lg navbar-dark fixed-top shadow mb-5" id="mainNav-back">
    <div class="container">
        
        <a href="/"><img src="/img/logo.png" alt="VERTIKAL TRIP" height="50"  style="margin-top:9px;margin-bottom:9px;"></a>
        

        <!-- ##############################################################  -->
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span> <span style="font-size:13px; color:#FFFFFF">TOURS</span>
        </button>
        <div class="collapse navbar-collapse stroke" id="navbarResponsive">
            <ul class="navbar-nav text-uppercase ml-auto mb-1">
            
            @foreach($Category->get() as $category)
            <li class="nav-item">
               <a class="nav-link menu-hover" href="/tours/{{ $category->slug }}">{{ $category->name }}</a>
            </li>
            @endforeach

            
            

            </ul>
        </div>
        <!-- ##############################################################  -->
    </div>
</nav>
@endif
<div style="height:25px;"></div>
	@yield('content')
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
                        @if(str_ireplace("www.","",$_SERVER['HTTP_HOST'])=="jogjafoodtour.com")
                        <small style="font-size:11px;"> 2020 &copy; JOGJA FOOD TOUR is part of VERTIKAL TRIP</small>
                        @else
                        <small style="font-size:11px;"> 2020 &copy; VERTIKAL TRIP</small>
                        @endif
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