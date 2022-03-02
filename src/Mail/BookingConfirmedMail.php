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
        
        $invoice = BookingHelper::create_invoice_pdf($shoppingcart);

        $mail = $this->view('toursdk::layouts.mail.booking-confirmed')
                    ->text('toursdk::layouts.mail.booking-confirmed_plain')
                    ->subject('Booking Confirmation')
                    ->with('shoppingcart',$shoppingcart);
                    
                    //->attachData($invoice->output(), 'Invoice-'. $shoppingcart->confirmation_code .'.pdf', ['mime' => 'application/pdf']);

        /*
        if($shoppingcart->shoppingcart_payment->payment_type=="bank_transfer")
        {
            $instruction = BookingHelper::create_instruction_pdf($shoppingcart);
            $mail->attachData($instruction->output(), 'Instruction-'. $shoppingcart->confirmation_code .'.pdf', ['mime' => 'application/pdf']);
        }
        */

        /*
        if($shoppingcart->shoppingcart_payment->payment_status!=4)
        {
            foreach($shoppingcart->shoppingcart_products()->get() as $shoppingcart_product)
            {
                
                $ticket = BookingHelper::create_ticket_pdf($shoppingcart_product);
                
                $mail->attachData($ticket->output(), 'Ticket-'. $shoppingcart_product->product_confirmation_code .'.pdf', ['mime' => 'application/pdf']);
            }
        }
        */

    }
}
