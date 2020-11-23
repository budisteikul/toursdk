@extends('coresdk::layouts.app')
@section('content')

<script src="{{ asset('assets/javascripts/widgets/687035c46b475965b2131d0e804b858e-widget-utils.js') }}"></script>
<div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Create Booking for {{$contents->title}}</div>
                <div class="card-body">

<div class="widget-body" id="WidgetContent">
<div class="widget">
	<div id="ActivityBookingWidget"></div>
    <script>
    window.priceFormatter = new WidgetUtils.PriceFormatter({
        currency: '{{ $currency }}',
        language: '{{ $lang }}',
        decimalSeparator: '.',
        groupingSeparator: ',',
        symbol: '{{ $currency }}'
    });

	window.i18nLang = '{{ $lang }}';
    window.ActivityBookingWidgetConfig = {
        currency: '{{ $currency }}',
        language: '{{ $lang }}',
        embedded: {!! $embedded !!},
        priceFormatter: window.priceFormatter,
        invoicePreviewUrl: '/snippets/activity/invoice-preview',
        addToCartUrl: '/snippets/widget/cart/session/<?= $sessionId ?>/activity',
        calendarUrl: '/snippets/activity/{id}/calendar/json/{year}/{month}',
        activities: [],
        pickupPlaces: [],
        dropoffPlaces: [],
        showOnRequestMessage: false,
        showCalendar: true,
        showUpcoming: false,
        displayOrder: 'Calendar',
        selectedTab: 'all',
        hideExtras: false,
        showActivityList: false,
        showFewLeftWarning: false,
        warningThreshold: 10,
        displayStartTimeSelectBox: false,
        displayMessageAfterAddingToCart: false,
        defaultCategoryMandatory: true,
        defaultCategorySelected: true,
        affiliateCodeFromQueryString: true,
        affiliateParamName: 'trackingCode',
        affiliateCode: '',
        onAfterRender: function() {
            if ( window.widgetIframe != undefined ) { window.widgetIframe.autoResize(); }
            setTimeout(function() {
                if ( window.widgetIframe != undefined ) { window.widgetIframe.autoResize(); }
            }, 200);

            if (typeof onWidgetRender !== 'undefined') {
                onWidgetRender();
            }
        },
        onAvailabilitySelected: function(selectedRate, selectedDate, selectedAvailability) {
        },
        onAddedToCart: function(cart) {
				$('.btn-primary').attr("disabled",true);
				$('.btn-primary').html(' <i class="fa fa-spinner fa-spin fa-fw"></i>  processing... ');
				
                $.ajax({
                    data: {
                        "_token": $("meta[name=csrf-token]").attr("content"),
                        "sessionId": '<?= $sessionId ?>',
                    },
                    type: 'POST',
                    url: '/snippets/shoppingcart'
                    }).done(function( data ) {
            
                        if(data.id=="1")
                        {
                            window.location.href = '{{route('route_toursdk_booking.index')}}/checkout';
                        }
                        else
                        {
                            $("#submit").attr("disabled", false);
                            $('.btn-primary').html(' Book now ');
                        }
                    });
                /*
                var successUrl = '{{route('route_toursdk_booking.index')}}/shoppingcart';
                if ( successUrl.indexOf('?') == -1 ) {
                  successUrl += '?sessionId=<?= $sessionId ?>';
                } else {
                  successUrl += '&sessionId=<?= $sessionId ?>';
                }
                if ( window.widgetIframe != undefined ) {
                  window.widgetIframe.redirectParent(successUrl);
                } else {
                  window.location = successUrl;
                }
                */
            
        },
        
        calendarMonth: {!!$month!!},
        calendarYear: {!!$year!!},
        loadingCalendar: true,
        
        activity: {!! json_encode($contents) !!},
        
        upcomingAvailabilities: [],
        
        firstDayAvailabilities: {!! $firstavailability !!}
    }; 
    </script>
</div>
<div id="generic-loading-template" style="display:none">
	<div class="well well-large well-transparent lead">
		<i class="fa fa-spinner icon-spin icon-2x pull-left"></i> processing...
	</div>
</div>
</div>

                </div>
            </div>
        </div>
 </div>
@endsection
