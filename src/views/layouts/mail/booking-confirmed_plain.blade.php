@inject('BookingHelper', 'budisteikul\toursdk\Helpers\BookingHelper')
@php
  $main_contact = $BookingHelper->get_answer_contact($shoppingcart);
@endphp
Hi {{$main_contact->firstName}},

Have a good day,
Thank you for your booking with {{env('APP_NAME')}}.
Follow link below to view status of your booking.

{{ env('APP_URL') .'/booking/receipt/'.$shoppingcart->session_id.'/'.$shoppingcart->confirmation_code  }}

If you have any question, feel free to contact us.
Thanks again, and enjoy your time in Indonesia :) 

Regards,
{{env('APP_NAME')}} team


{{env('APP_NAME')}}
Jl. Abiyoso VII No.190 Bantul ID
Website : www.vertikaltrip.com
Whatsapp : +6285743112112
Email : guide@vertikaltrip.com