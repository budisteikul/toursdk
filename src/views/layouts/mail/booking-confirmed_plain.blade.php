Hi {{$shoppingcart->shoppingcart_questions()->select('answer')->where('type','mainContactDetails')->where('question_id','firstName')->first()->answer}},

Have a good day,
Thank you for your booking with {{env('APP_NAME')}}. 
You can find booking receipt and e-ticket on attachments and present it to our team on location 
If you have any question, feel free to contact us.
Thanks again, and enjoy your time in Indonesia :) 

Regards,
{{env('APP_NAME')}} team


{{env('APP_NAME')}}
Jl. Abiyoso VII No.190 Bantul ID
Website : www.vertikaltrip.com
Whatsapp : +6285743112112
Email : guide@vertikaltrip.com