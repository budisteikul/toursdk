<?php

namespace budisteikul\toursdk\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade as PDF;
use budisteikul\toursdk\Helpers\BookingHelper;

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
        $notice = '';

        if($shoppingcart->currency!=$shoppingcart->shoppingcart_payment->currency)
        {
            $notice .= 'Rate : '. BookingHelper::get_rate($shoppingcart);
        }

        $invoice = PDF::setOptions(['tempDir' => storage_path(),'isRemoteEnabled' => true])->loadView('toursdk::layouts.pdf.invoice', compact('shoppingcart','notice'))->setPaper('a4', 'portrait');

        $mail = $this->view('toursdk::layouts.mail.booking-confirmed')
                    ->text('toursdk::layouts.mail.booking-confirmed_plain')
                    ->subject('Booking Confirmation')
                    ->with('shoppingcart',$shoppingcart)
                    ->attachData($invoice->output(), 'Invoice-'. $shoppingcart->confirmation_code .'.pdf', ['mime' => 'application/pdf']);

        if($shoppingcart->shoppingcart_payment->payment_status!=4)
        {
            foreach($shoppingcart->shoppingcart_products()->get() as $shoppingcart_product)
            {
                $customPaper = array(0,0,300,540);
                $ticket = PDF::setOptions(['tempDir' => storage_path(),'isRemoteEnabled' => true])->loadView('toursdk::layouts.pdf.ticket', compact('shoppingcart_product'))->setPaper($customPaper);
                $mail->attachData($ticket->output(), 'Ticket-'. $shoppingcart_product->product_confirmation_code .'.pdf', ['mime' => 'application/pdf']);
            }
        }
        

    }
}
