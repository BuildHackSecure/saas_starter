<?php
if( !isset($_GET["no_headers"])) {
    View::page('Core/app/logged_out/header',array(
        'title'         =>  'Invoice Overdue'
));} ?>





<div class="alert alert-danger" role="alert">
    <p>Your account is now overdue and a payment of <strong>&pound;<?php echo $data["total"]; ?></strong> is required to keep on using the service</p>
    <p>Please enter your payment details below to continue</p>
</div>

<?php if( count($data["errors"]) > 0 ){ ?>
    <div class="alert alert-danger" role="alert">
        <?php foreach( $data["errors"] as $e ) echo '<p>'.$e.'</p>' ; ?>
    </div>
<?php } ?>


<form action="/payment" method="post" id="payment-form">
    <input type="hidden" name="csrf" value="<?php echo $data["csrf"]; ?>">
    <script src="https://js.stripe.com/v3/"></script>
    <div class="panel panel-default">
        <div class="panel-heading">Payment Card Details</div>
        <div class="panel-body">
            <div class="form-row">
                <label for="card-element">
                    Credit or debit card
                </label>
                <div id="card-element">
                    <!-- A Stripe Element will be inserted here. -->
                </div>
                <!-- Used to display form errors. -->
                <div id="card-errors" role="alert"></div>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary pull-right">Pay Invoice</button>
</form>



<?php
if( !isset($_GET["no_headers"])) {
    View::page('Core/app/logged_out/footer');
}
?>


<script>

    var stripe = Stripe('<?php echo Config::getStripePublicKey(); ?>');

    function stripeTokenHandler(token) {
        // Insert the token ID into the form so it gets submitted to the server
        var form = document.getElementById('payment-form');
        var hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'stripeToken');
        hiddenInput.setAttribute('value', token.id);
        form.appendChild(hiddenInput);
        form.submit();
    }

    // Create an instance of Elements.
    var elements = stripe.elements();

    // Custom styling can be passed to options when creating an Element.
    // (Note that this demo uses a wider set of styles than the guide below.)
    var style = {
        base: {
            color: '#32325d',
            lineHeight: '18px',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    };

    // Create an instance of the card Element.
    var card = elements.create('card', {style: style});

    // Add an instance of the card Element into the `card-element` <div>.
    card.mount('#card-element');

    // Handle real-time validation errors from the card Element.
    card.addEventListener('change', function(event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    // Handle form submission.
    var form = document.getElementById('payment-form');
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        stripe.createToken(card).then(function(result) {
            if (result.error) {
                // Inform the user if there was an error.
                //var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
            } else {
                // Send the token to your server.
                stripeTokenHandler(result.token);
            }
        });


    });

</script>