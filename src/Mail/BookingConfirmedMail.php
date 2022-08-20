<?php

namespace budisteikul\toursdk\Mail;

use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\GeneralHelper;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BookingConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($shoppingcart)
    {
        $this->shoppingcart = $shoppingcart;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $shoppingcart = $this->shoppingcart;
        
        //$invoice = BookingHelper::create_invoice_pdf($shoppingcart);

        /*
        $mail = $this->view('toursdk::layouts.mail.booking-confirmed')
                    ->text('toursdk::layouts.mail.booking-confirmed_plain')
                    ->subject('Booking Confirmation')
                    ->with('shoppingcart',$shoppingcart);
        */

    }
}
