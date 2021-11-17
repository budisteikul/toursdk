<?php

namespace budisteikul\toursdk\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade as PDF;
use budisteikul\toursdk\Helpers\BookingHelper;
use budisteikul\toursdk\Helpers\GeneralHelper;
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
        $notice = '';
        $qrcode = base64_encode(QrCode::errorCorrection('H')->format('png')->size(111)->margin(0)->generate( env('APP_URL') .'/booking/receipt/'.$shoppingcart->id.'/'.$shoppingcart->session_id  ));

        if($shoppingcart->currency!=$shoppingcart->shoppingcart_payment->currency)
        {
            $notice .= 'Pay : '.$shoppingcart->shoppingcart_payment->currency.' '. $shoppingcart->shoppingcart_payment->amount .'<br />';
            $notice .= 'Rate : '. BookingHelper::get_rate($shoppingcart) .'<br />';
        }

        if($shoppingcart->due_on_arrival>0)
        {
            $notice .= 'Due on arrival : '.$shoppingcart->currency.' '. GeneralHelper::numberFormat($shoppingcart->due_on_arrival) .'<br />';
        }

        $invoice = PDF::setOptions(['tempDir' => storage_path(),'isRemoteEnabled' => true])->loadView('toursdk::layouts.pdf.invoice', compact('shoppingcart','notice','qrcode'))->setPaper('a4', 'portrait');

        $mail = $this->view('toursdk::layouts.mail.booking-confirmed')
                    ->text('toursdk::layouts.mail.booking-confirmed_plain')
                    ->subject('Booking Confirmation')
                    ->with('shoppingcart',$shoppingcart)
                    ->attachData($invoice->output(), 'Invoice-'. $shoppingcart->confirmation_code .'.pdf', ['mime' => 'application/pdf']);

        if($shoppingcart->shoppingcart_payment->payment_provider=="midtrans" && $shoppingcart->shoppingcart_payment->payment_type=="bank_transfer")
        {
            $customPaper = array(0,0,430,2032);
            $instruction = PDF::setOptions(['tempDir' => storage_path(),'isRemoteEnabled' => true])->loadView('toursdk::layouts.manual.bank_transfer', compact('shoppingcart','qrcode'))->setPaper($customPaper,'portrait');
            $mail->attachData($instruction->output(), 'Instruction-'. $shoppingcart->confirmation_code .'.pdf', ['mime' => 'application/pdf']);
        }

        if($shoppingcart->shoppingcart_payment->payment_status!=4)
        {
            foreach($shoppingcart->shoppingcart_products()->get() as $shoppingcart_product)
            {
                $customPaper = array(0,0,300,540);
                $ticket = PDF::setOptions(['tempDir' => storage_path(),'isRemoteEnabled' => true])->loadView('toursdk::layouts.pdf.ticket', compact('shoppingcart_product','qrcode'))->setPaper($customPaper);
                $mail->attachData($ticket->output(), 'Ticket-'. $shoppingcart_product->product_confirmation_code .'.pdf', ['mime' => 'application/pdf']);
            }
        }
        

    }
}
