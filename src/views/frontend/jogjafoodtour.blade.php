<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@hasSection('description')@yield('description')@else Enjoy Jogja in Local Ways. Join us on this experience to try authentic Javanese dishes, play traditional games, travel on a becak, learn interesting fun facts about city, interact with locals and many more. @endif">
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
    <title>Yogyakarta Night Walking and Food Tours</title>
    <link href="https://fonts.googleapis.com/css?family=Barlow:400,700" rel="stylesheet" type="text/css" media="screen,handheld">
  <script src="/js/vertikaltrip-3.1.1.js"></script>
  <link href="/css/vertikaltrip-3.1.4.css" rel="stylesheet" media="screen,handheld">
  <script
    src="https://www.paypal.com/sdk/js?client-id={{ env('PAYPAL_CLIENT_ID') }}&intent=authorize&currency={{ env('BOKUN_CURRENCY') }}"  data-csp-nonce="xyz-123">
</script>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "Product",
      "name": "Yogyakarta Night Walking and Food Tours",
      "image": [
        "https://www.jogjafoodtour.com/img/67328563_375493963154576_6016721914330873856_n.jpg",
        "https://www.jogjafoodtour.com/img/72972035_417301452307160_8303451640472535040_n.jpg",
        "https://www.jogjafoodtour.com/img/67707843_379659329404706_5297007346822676480_n.jpg"
       ],
      "description": "See a different side of Yogyakarta, Indonesia’s cultural capital, on this fun night tour jam-packed with street food delights. Join your guide and no more than seven other travelers in the city center, then board a “becak” rickshaw to tour the sights. Savor the light, sweet flavors of Javanese cuisine; soak up the vibrant atmosphere of this university city; try traditional games; and enjoy fairground rides at Alun-Alun Kidul.",
      "sku": "110844P2",
      "mpn": "208273",
      "brand": {
        "@type": "Thing",
        "name": "JOGJA FOOD TOUR"
      },
      "review": {
        "@type": "Review",
        "reviewRating": {
          "@type": "Rating",
          "ratingValue": "4.9",
          "bestRating": "5"
        },
        "author": {
          "@type": "Person",
          "name": "Airbnb and Trip Advisor user"
        }
      },
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.9",
        "reviewCount": "{{ $count }}"
      },
      "offers": {
        "@type": "Offer",
        "url": "https://example.com/anvil",
        "priceCurrency": "USD",
        "price": "40",
        "priceValidUntil": "2020-12-31",
        "itemCondition": "https://schema.org/UsedCondition",
        "availability": "https://schema.org/InStock",
        "seller": {
          "@type": "Organization",
          "name": "VERTIKAL TRIP"
        }
      }
    }
</script>
<script type="text/javascript">
      jQuery(document).ready(function($) {  
      var table = $('#dataTables-example').DataTable(
      {
        
        "processing": true,
            "serverSide": true,
            "ajax": {
                  "url": "/reviews",
                  "type": "POST",
            "headers": {
                  'X-CSRF-TOKEN': $("meta[name=csrf-token]").attr("content")
              }
              },
        "scrollX": true,
        "language": {
            "paginate": {
                "previous": "<i class='fa fa-step-backward'></i>",
            "next": "<i class='fa fa-step-forward'></i>",
            "first": "<i class='fa fa-fast-backward'></i>",
            "last": "<i class='fa fa-fast-forward'></i>"
            },
          "aria": {
                  "paginate": {
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
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class=" bg-white">
  
<div style="background-color:#FFFFFF"> 
    
<!-- Navigation -->
<nav class="navbar navbar-default navbar-expand-lg navbar-dark fixed-top shadow mb-5" id="mainNav">
	<div class="container">

@if(str_ireplace("www.","",$_SERVER['HTTP_HOST'])=="aaa.com")
<noscript><a href="https://jogjafoodtour.eventbrite.com" rel="noopener noreferrer" target="_blank"></noscript>
<button class="btn btn-danger text-white" id="eventbrite-widget-modal-trigger-77732854059" type="button" style="margin-top:10px;margin-bottom:10px;" ><i class="fa fa-ticket-alt"></i> <span style="font-family: 'Barlow','Helvetica Neue',Arial,sans-serif;"><strong>Book now</strong></span></button>
<noscript></a>Book now on Eventbrite</noscript>
<script src="https://www.eventbrite.com/static/widgets/eb_widgets.js"></script>
<script type="text/javascript">
    var exampleCallback = function() {
        console.log('Order complete!');
    };

    window.EBWidgets.createWidget({
        widgetType: 'checkout',
        eventId: '77732854059',
        modal: true,
        modalTriggerElementId: 'eventbrite-widget-modal-trigger-77732854059',
        onOrderComplete: exampleCallback
    });
</script>
@else
<a class="btn btn-danger text-white " href="/booking/yogyakarta-night-walking-and-food-tours/" style="margin-top:10px;margin-bottom:10px;border-radius:0;" ><i class="fa fa-ticket-alt"></i> <span style="font-family: 'Barlow','Helvetica Neue',Arial,sans-serif;"><strong>Book now</strong></span></a>	

@endif

		<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		
		<div class="collapse navbar-collapse stroke" id="navbarResponsive">
			<ul class="navbar-nav text-uppercase ml-auto mb-1">
				<li class="nav-item">
					<a class="nav-link js-scroll-trigger" href="#services">Why Jogja Food Tour?</a>
				</li>
                
                <li class="nav-item">
					<a class="nav-link js-scroll-trigger" href="#about">The Tour</a>
				</li>
                
				<li class="nav-item">
					<a class="nav-link js-scroll-trigger" href="#gallery">Snapshot</a>
				</li>
                
                <li class="nav-item">
					<a class="nav-link js-scroll-trigger" href="#guide">Tour Guide</a>
				</li>
                
                <li class="nav-item">
					<a class="nav-link js-scroll-trigger" href="#review">Reviews</a>
				</li>
        
        
				
				
			</ul>
		</div>
		
		
		
		
    </div>
	
  </nav>

<header id="page-top" class="intro-header" style="background-image: url('/img/tugu-night.jpg'); background-color: #000000; background-repeat: no-repeat; background-size: cover ;">
	<div class="col-lg-8 col-md-10 mx-auto">
		<div class="site-heading text-center">
			<div class="transbox" style=" min-height:100px; padding-top:20px; padding-bottom:5px; padding-left:10px; padding-right:10px;">
            	<img alt="Yogyakarta Night Walking and Food Tours" src="/img/jogjafoodtour.png" width="250">
                <hr style="max-width:50px;border-color: #c03b44;border-width: 3px;">
				<h1 id="title" style="text-shadow: 2px 2px #555555;">Yogyakarta Night Walking and Food Tours</h1>
				<p class="text-faded">
                	Join us on this experience to try authentic Javanese dishes, play traditional games, travel on a becak, learn interesting fun facts about city, interact with locals and many more. <br>
                	GET DISCOUNT 15% WITH PROMOCODE <span class="badge badge-success">HOLIDAY2020</span>
                	<br>
                    Enjoy Jogja in Local Ways!
				</p>
			</div>
            <i class="fa fa-angle-down infinite animated fadeInDown" style="font-size: 50px; color:#FFFFFF; margin-top:30px"></i>
       
		</div>
    </div>
</header>



<!-- Services -->
  <section class="page-section bg-light" id="services">
    <div class="container">
    <div style="height:25px;"></div>
      
      <div class="row">
        <div class="col-lg-3 col-md-6 text-center">
          <div class="mt-5 mb-2">
            <i class="fa fa-4x fa-bolt text-danger mb-2"></i>
            <h3 class="h4 mb-2">Instant Confirmation</h3>
            <p class="text-muted mb-0">To secure your spot while keeping your plans flexible. Your booking are confirmed automatically!</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 text-center">
          <div class="mt-5 mb-2">
            <i class="fas fa-4x fa-phone-alt text-danger mb-2"></i>
            <h3 class="h4 mb-2">24/7 Support</h3>
            <p class="text-muted mb-0">Stay Connected with us! With 24/7 Support.</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 text-center">
          <div class="mt-5 mb-2">
            <i class="fas fa-4x fa-history text-danger mb-2"></i>
            <h3 class="h4 mb-2">Free Cancellation</h3>
            <p class="text-muted mb-0">Have your plans changed? No worries! You can cancel the booking anytime up to 24 hours before your experience!</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 text-center">
          <div class="mt-5 mb-2">
            <i class="fab fa-4x fa-paypal text-danger mb-2"></i>
            <h3 class="h4 mb-2">Secure Payments</h3>
            <p class="text-muted mb-0">You can pay online using your credit card or Paypal. We will make it secure and simple!</p>
          </div>
        </div>
      </div>
      <div style="height:45px;"></div>	
    </div>
  </section>
  
  <section class="page-section" id="press" style="background-color:#f2f2f2">
  
    <div class="container">
      
        	<div class="row" style="padding-bottom:0px;">
				<div class="col-lg-8 text-center mx-auto">
					<h3 class="section-heading" style="margin-top:50px;">Yogyakarta: The way to this city’s heart is through its food</h3>
                    Perhaps better known for being a bastion of history and culture,<br>
Yogyakarta is also the unofficial culinary capital of Indonesia
        <br>
					<hr style="max-width:50px;border-color:#e2433b;border-width:3px;">
				</div>
			</div>
      
      <div class="row text-center">
        
        <div class="col-md-8 mx-auto">
        
        <img src="/img/silkwinds.jpg" class="img-fluid rounded">
        <img src="/img/silkwinds-magazine-logo.png" style="margin-top:4px;" class="img-fluid rounded">
        <span class="caption text-muted"><a class="text-muted"  target="_blank" href="https://www.silverkris.com/yogyakarta-the-way-to-this-citys-heart-is-through-its-food/">Silkwinds Magazine</a></span>
        </div>
        
        
      </div>
    </div>
    <br><br>
  </section>







 <!-- Post Content -->
<article id="about">
<div class="container">
	<div class="row">
		<div class="col-lg-8 col-md-10 mx-auto">
        
        
	  
        <div>
			<div class="row" style="padding-bottom:0px;">
				<div class="col-lg-12 text-center">
					<h3 class="section-heading" style="margin-top:0px;">Explore Yogyakarta Through our Jogja Food Tour</h3>
					<h4 class="section-subheading text-muted">And So Our Adventure Begins</h4>
					<hr style="max-width:50px;border-color:#e2433b;border-width:3px;">
				</div>
			</div>
			
			<p>
            	
				<div>
					<span style="width:30px;" class="fa fa-store"></span><strong> Name :</strong> 
                    <span>Yogyakarta Night Walking and Food Tours</span><br />
                    <span style="width:30px;" class="fa fa-walking"></span><strong> Tour Mode :</strong> Walk and Trishaw<br />
					<span style="width:30px;" class="fa fa-stopwatch"></span><strong> Duration :</strong> 3 ~ 4 hours start at 6.30 pm<br />
					<span style="width:30px;" class="fa fa-bars"></span><strong> Type :</strong> Open Trip<br />
					<span style="width:30px;" class="fa fa-language"></span><strong> Language :</strong> Offered in English<br />
                    <div>
                  
				
    					
                	</div>
                    
                   
                 
                    
                    <br>
                    
                    
                    
                    
				</div>
			</p>
            <p>
            	<div>
				<h2 class="section-heading">Highlights</h2>
				- If you like food and want to experience Jogja culture <br />
				- The walking tour part was a good introduction to the city <br />
				- Travel on a becak (Traditional Public Transportation) <br />
				- Learn interesting fun facts about Yogyakarta <br />
				- Enjoying the nighttime atmosphere of Yogyakarta <br />
                </div>
			</p>
            <p>
            	<div>
				<h2 class="section-heading">Overview</h2>
				See a different side of Yogyakarta, Indonesia’s cultural capital, on this fun night tour jam-packed with street food delights. Join your guide and no more than seven other travelers in the city center, then board a “becak” rickshaw to tour the sights. Savor the light, sweet flavors of Javanese cuisine; soak up the vibrant atmosphere of this university city; try traditional games; and enjoy fairground rides at Alun-Alun Kidul.
                
                </div>
			</p>

			<p>
            	<div>
				<h2 class="section-heading">Inclusions</h2>
				- Local Guide (English Speaking) <span class="fa fa-user"></span><br>
				- Mineral water 600 ml <span class="fa fa-prescription-bottle"></span><br />
				- Fee of all activities at Alun - Alun Kidul (masangin, paddle car, etc) <span class="fa fa-ticket-alt"></span><br />
				- Becak (Yogyakarta traditional trishaw) <span class="fa fa-car"></span><br />
				- Raincoat, if it's rain <span class="fa fa-briefcase"></span><br />
				- Many types of Javanese authentic snack, food and drink <span class="fa fa-utensils"></span><br />
                </div>
			</p>
          
			<p>
            	<div>
				<h2 class="section-heading">Little things to remember</h2>
				- Please be hungry, because a lot of food is to be tried out during this tour.<br />
				- Wear comfortable footwear and relax clothing.<br />
				- And don't forget to bring your camera to take some nice pictures.<br />
                </div>
			</p>
          	
            <p>
            	<div>
				<h2 class="section-heading">Aditional Info</h2>
				- Free for infant (Age under 5 years old) and must be accompanied by adult <br />
				- Not wheelchair accessible <br />
				- No minimum booking number of person <br />
				- This tour/activity will have a maximum of 8 travelers <br />
				- Most travelers can participate <br />
                </div>
			</p>
            
			<center>
				<br>
				<img width="400" alt="Gudeg Jogja | Yogyakarta Night Walking and Food Tours" class="img-fluid rounded" src="/img/gudeg-jogja.jpg">
				<span class="caption text-muted">Gudeg Jogja</span>
			</center>
            
		
        	<br>
            <div class="bd-callout bd-callout-danger w-100" style="margin-right:5px;">
						<span style="width:30px;" class="fa fa-map-marked-alt text-danger"></span><strong class="text-danger"> Meeting/Redemption  point</strong><br>
                        <br>
                        - You will receive a confirmation email and voucher instantly after booking
						<br>
                        - You can present a mobile voucher for this activity to our tour guide
                        <br>
<br>
                        <img src="/img/Google_Maps-Logo.jpg" height="30" style="margin-bottom:5px;" alt="Book Yogyakarta Night Walking and Food Tours via Google Maps"><br>
                        
                        
                        <div class="map-responsive">
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.0649523361567!2d110.36486611421002!3d-7.7829383793810685!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a58373fffffff%3A0xffb2d5ffd8a9bd10!2sTugu%20Pal%20Putih!5e0!3m2!1sen!2sid!4v1566909137586!5m2!1sen!2sid" width="600" height="450" frameborder="0" style="border:0;" allowfullscreen=""></iframe>
						</div>
                        
                        <br>
                        
						Tugu Yogyakarta Monument (Tugu Pal Putih)<br />	
						Cokrodiningratan, Kec. Jetis, Kota Yogyakarta, Daerah Istimewa Yogyakarta 55233
                    	<br>
						<!-- small class="form-text text-muted">
                        <a class="text-muted" href="https://goo.gl/maps/bsk9cGSh9iuUX7e46">
                        You can also buy tickets through on Google Maps via Google Reservation. Click to open Map
                        </a>
                        </small -->
                    </div>
        </div>
        
        
        
        </div>
    </div>
</div>
</article> 



<section id="gallery" style="background-color:#f2f2f2">
<div class="container">
	<div class="row">
		<div class="col-lg-8 col-md-10 mx-auto">
			<div class="row" style="padding-bottom:0px;">
				<div class="col-lg-12 text-center">
					<h3 class="section-heading" style="margin-top:50px;">The Snapshot of Happiness</h3>
					<h4 class="section-subheading text-muted">Enjoy the Little Things</h4>
					<hr style="max-width:50px;border-color:#e2433b;border-width:3px;">
				</div>
			</div>
		</div>
	</div>
</div>
<div class="container">
	<div class="row">
		<div class="col-lg-8 col-md-10 mx-auto">
			<div class="row text-center" style="padding-bottom:0px;">
				
                	
				<div class="col-lg-4 col-sm-6">
					<img class="img-fluid shadow p-1 bg-white rounded" alt="New Friend | Yogyakarta Night Walking and Food Tours" src="/img/67198060_375970766440229_1053478013678649344_n.jpg">
					<br />
					<span class="caption text-muted"></span>
					<div class="mb-4"></div>
				</div>
        
				<div class="col-lg-4 col-sm-6">
					<img class="img-fluid shadow p-1 bg-white rounded" alt="New Friend | Yogyakarta Night Walking and Food Tours" src="/img/67328563_375493963154576_6016721914330873856_n.jpg">
					<br />
					<span class="caption text-muted"></span>
					<div class="mb-4"></div>
				</div>
        
				<div class="col-lg-4 col-sm-6">
					<img class="img-fluid shadow p-1 bg-white rounded" alt="New Friend | Yogyakarta Night Walking and Food Tours" src="/img/67353775_376194963084476_2687305984216399872_n.jpg">
					<br />
					<span class="caption text-muted"></span>
					<div class="mb-4"></div>
				</div>
        
				<div class="col-lg-4 col-sm-6">
					<img class="img-fluid shadow p-1 bg-white rounded" alt="New Friend | Yogyakarta Night Walking and Food Tours" src="/img/67707843_379659329404706_5297007346822676480_n.jpg">
					<br />
					<span class="caption text-muted"></span>
					<div class="mb-4"></div>
				</div>
        
				<div class="col-lg-4 col-sm-6">
					<img class="img-fluid shadow p-1 bg-white rounded" alt="New Friend | Yogyakarta Night Walking and Food Tours" src="/img/70123721_400838900620082_6071757728341032960_n.jpg">
					<br />
					<span class="caption text-muted"></span>
					<div class="mb-4"></div>
				</div>
        
				<div class="col-lg-4 col-sm-6">
					<img class="img-fluid shadow p-1 bg-white rounded" alt="New Friend | Yogyakarta Night Walking and Food Tours" src="/img/70240849_399764170727555_1695349988323753984_n.jpg">
					<br />
					<span class="caption text-muted"></span>
					<div class="mb-4"></div>
				</div>
                
                <div class="col-lg-4 col-sm-6">
					<img class="img-fluid shadow p-1 bg-white rounded" alt="New Friend | Yogyakarta Night Walking and Food Tours" src="/img/71679758_415329815837657_7760984802697674752_n.jpg">
					<br />
					<span class="caption text-muted"></span>
					<div class="mb-4"></div>
				</div>
                
                <div class="col-lg-4 col-sm-6">
					<img class="img-fluid shadow p-1 bg-white rounded" alt="New Friend | Yogyakarta Night Walking and Food Tours" src="/img/72972035_417301452307160_8303451640472535040_n.jpg">
					<br />
					<span class="caption text-muted"></span>
					<div class="mb-4"></div>
				</div>
                
                <div class="col-lg-4 col-sm-6">
					<img class="img-fluid shadow p-1 bg-white rounded" alt="New Friend | Yogyakarta Night Walking and Food Tours" src="/img/IMG-20190822-WA0007.jpg">
					<br />
					<span class="caption text-muted"></span>
					<div class="mb-4"></div>
				</div>
        		
               
			</div>
		</div>
	</div>
</div>
</section>

<section id="guide" style="background-color:#f2f2f2">
<div class="container">
	<div class="row">
		<div class="col-lg-8 col-md-10 mx-auto">
			<div class="row">
				<div class="col-lg-12 text-center">
				<h3 class="section-heading" style="margin-top:50px;">Our Amazing Tour Guide</h3>
				<h4 class="section-subheading text-muted">Wholeheartedly as a Local Friend</h4>
				<hr style="max-width:50px;border-color:#e2433b;border-width:3px;">
				</div>
			</div>
			<br>
		</div>
        
     </div>
     <div class="row justify-content-center"> 
     <div class="row col-8">       
        
            
        	<div class="d-flex flex-wrap justify-content-center col-lg-4 col-md-4 mx-auto">
				<div class="team-member" style="margin-bottom:5px; margin-left:30px; margin-right:30px;">
					<img alt="Tour Guide | Yogyakarta Night Walking and Food Tours" class="mx-auto rounded-circle" width="200" src="/img/11950485_625098100961142_1518701134_n.jpg" >
					<h4>Kalika Prajna</h4>
					<p class="text-muted">Your Local Friend<br /><span class="text-danger">On duty</span></p>
                    
					<br><br>
				</div>
			</div>
           
            
            
            <div class="d-flex flex-wrap justify-content-center col-lg-4 col-md-4 mx-auto">
				<div class="team-member" style="margin-bottom:5px; margin-left:30px; margin-right:30px;">
					<img alt="Tour Guide | Yogyakarta Night Walking and Food Tours" class="mx-auto rounded-circle" width="200" src="/img/12568774_882830958481058_374097774_n.jpg" >
					<h4>Vella Sekar</h4>
					<p class="text-muted">Your Local Friend</p>
					<br><br>
				</div>
			</div>
        	
            
            
        </div></div>
        
	</div>
</div>
</section>

<section id="review" style="background-color:#ffffff">
<div class="container mb-6">
	<div class="row">
    	<div class="col-lg-8 col-md-10 mx-auto">
			
				<div class="col-lg-12 text-center">
					<h3 class="section-heading" style="margin-top:50px;">How Our New Friend Talk About The Tour</h3>
                    <h4 class="section-subheading text-muted"><a href="https://www.tripadvisor.com/UserReviewEdit-g14782503-d15646790-Yogyakarta_Night_Walking_and_Food_Tours-Yogyakarta_Yogyakarta_Region_Java.html" target="_blank" class="text-theme"><i class="fab fa-tripadvisor" aria-hidden="true"></i>  Review us on Trip Advisor</a></h4>
					<strong> Rating :</strong>
					<span class="text-warning">
					<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i> <span class="text-danger">(4.9)</span>
					</span>‎
					<br>
					<small class="form-text text-muted">Based on {{ $count }} our new friend reviews</small>
					<hr style="max-width:50px;border-color:#e2433b;border-width:3px;">
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








</div>

<script>


(function($) {
        
  "use strict"; // Start of use strict
  // Smooth scrolling using jQuery easing
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

 
  // Activate scrollspy to add active class to navbar items on scroll
  $('body').scrollspy({
    target: '#mainNav',
    offset: 75
  });
 
  // Closes responsive menu when a scroll trigger link is clicked
  $('.js-scroll-trigger').click(function() {
    $('.navbar-collapse').collapse('hide');
  });

  // Collapse Navbar
  var navbarCollapse = function() {
    if ($("#mainNav").offset().top > 100) {
      $("#mainNav").addClass("navbar-shrink");
    } else {
      $("#mainNav").removeClass("navbar-shrink");
    }
  };
  
  // Collapse now if page is not at top
  navbarCollapse();
  
  // Collapse the navbar when page is scrolled
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
                        <small style="font-size:11px;"> 2020 &copy; JOGJA FOOD TOUR is part of VERTIKAL TRIP</small>
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