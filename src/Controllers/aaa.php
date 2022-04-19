
<form id="payment-form"><label for="card-element">Card</label><div id="card-element"></div><div id="card-errors" role="alert"></div><button id="submit">Pay</button></form>

$.ajax({
    data: {
          "_token": $("meta[name=csrf-token]").attr("content"),
          "name": $('#name').val(),
        },
    type: 'POST',
    url: '{{ route('route_tourcms_channel.store') }}'
    }).done(function( data ) {
      
      
    });



public function stripe_jscript($sessionId)
    {
        $jscript = '
        jQuery(document).ready(function($) {
            $("#submitCheckout").slideUp("slow");
            $("#paymentContainer").html(\'<form id="payment-form"><label for="card-element">Card</label><div id="card-element"></div><div id="card-errors" role="alert"></div><button id="submit">Pay</button></form>\');




            document.addEventListener(\'DOMContentLoaded\', async () => {
                const stripe = Stripe(\''. env("STRIPE_PUBLISHABLE_KEY") .'\', {
                    apiVersion: \'2020-08-27\',
                });
                const elements = stripe.elements();
                const cardElement = elements.create(\'card\');
                cardElement.mount(\'#card-element\');

                const paymentForm = document.querySelector(\'#payment-form\');
                paymentForm.addEventListener(\'submit\', async (e) => {
                    // Avoid a full page POST request.
                    e.preventDefault();

                    // Disable the form from submitting twice.
                    paymentForm.querySelector(\'button\').disabled = true;

                    // Confirm the card payment that was created server side:
                    const {error, paymentIntent} = await stripe.confirmCardPayment(
                    \'<?= $paymentIntent->client_secret; ?>\', {
                        payment_method: {
                            card: cardElement,
                        },
                    },
                );
                if(error) {
                    addMessage(error.message);

                    // Re-enable the form so the customer can resubmit.
                    paymentForm.querySelector(\'button\').disabled = false;
                    return;
                }
            addMessage(`Payment (${paymentIntent.id}): ${paymentIntent.status}`);
            });
            });




            

        });
        ';
    }