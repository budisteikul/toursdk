@inject('BookingHelper', 'budisteikul\toursdk\Helpers\BookingHelper')
@inject('Content', 'budisteikul\toursdk\Helpers\ContentHelper')
@php
  $main_contact = $BookingHelper->get_answer_contact($shoppingcart);
@endphp
Hi {{$main_contact->firstName}},

Have a good day,
Thank you for your booking with {{env('APP_NAME')}}.

Your booking number is : {{$shoppingcart->confirmation_code}}

{!! $Content->view_product_detail($shoppingcart,true) !!}

Follow link below to know way to the meeting point.
https://linktr.ee/foodtour

Our guide will contact you in the day of the tour. If you have any question, feel free to contact us.
See you there :) 

Regards,
The {{env('APP_NAME')}} team


{{env('APP_NAME')}}
Website : www.jogjafoodtour.com
Whatsapp : +6285743112112
Email : guide@vertikaltrip.com