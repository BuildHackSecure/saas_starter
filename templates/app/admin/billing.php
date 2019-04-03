<?php
if( !isset($_GET["no_headers"])) {
    View::page('Core/app/header',array(
        'title'         =>  'Billing Details',
        'level'    =>  $data["level"]
    ));} ?>


<style>

    .credit_card {
        display: inline-block;
        height:100px;
        border-radius: 10px;
        background-image:url(/assets/img/card/cc.png);
        background-color:#2b5294;
        max-width:170px;
        min-width:170px;
    }


    .credit_card .card_number {
        padding-top:20px;
        font-family: "Century Gothic", "Helvetica", sans-serif;
        text-align:center;
        font-size:15px;
        color: #cecece;
        text-shadow: 0px 1px 0px rgba(0, 0, 0, 1); /* 50% white from bottom */
    }

    .credit_card .expiry_date {
        font-family: "Century Gothic", "Helvetica", sans-serif;
        text-align:right;
        font-size:12px;
        color: #cecece;
        padding-right:27px;
        text-shadow: 0px 1px 0px rgba(0, 0, 0, 1); /* 50% white from bottom */
    }

    .credit_card .supplier {
        text-align: right;
        padding-right:8px;
        padding-top: 8px;
    }


</style>

    <h1>Billing</h1>


    <?php if( count($data["error"]) > 0 ){ ?>

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="alert alert-danger">
                    <?php foreach( $data["error"] as $e ) echo '<p>'.$e.'</p>' ?>
                </div>
            </div>
        </div>

    <?php } ?>


    <?php if( $data["cancelled"] ){ ?>


        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="alert alert-warning">
                    Your account is due to close on <?php echo date("d/m/Y",$data["next_payment"]); ?>  and no more payments will be taken, you can still use this service up until this point. After that point all your data will be deleted. If you wish to reverse this action, please <a href="#" class="uncancel_account">click here</a>.
                </div>
            </div>
        </div>

    <?php }else{ ?>

        <?php if( $data["next_payment"] < ( date("U") - 86400 ) ){ ?>

            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div class="alert alert-danger">
                        Your account payment is overdue, please enter your billing information below. Unpaid accounts will be deleted after 28 days.
                    </div>
                </div>
            </div>

        <?php }else{ ?>

            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div class="alert alert-success">
                        Your account is subscribed until <?php echo date("d/m/Y",$data["next_payment"]); ?> which is when your next payment will be taken. If you wish to cancel your account, please <a href="#" class="cancel_account">click here</a>.
                    </div>
                </div>
            </div>

        <?php } ?>



    <?php } ?>







    <h3>Billing Card</h3>


<style>

    .StripeElement {
        background-color: white;
        height: 40px;
        padding: 10px 12px;
        border-radius: 4px;
        border: 1px solid transparent;
        box-shadow: 0 1px 3px 0 #e6ebf1;
        -webkit-transition: box-shadow 150ms ease;
        transition: box-shadow 150ms ease;
    }

    .StripeElement--focus {
        box-shadow: 0 1px 3px 0 #cfd7df;
    }

    .StripeElement--invalid {
        border-color: #fa755a;
    }

    .StripeElement--webkit-autofill {
        background-color: #fefde5 !important;
    }

</style>

<div class="modal fade" id="uncancelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Re-Activate Account</h4>
            </div>
            <form action="/admin/billing" method="post">
                <input type="hidden" name="uncancel" value="1">
                <input type="hidden" name="csrf" value="<?php echo $data["csrf"]; ?>">
                <div class="modal-body">
                    <div><label>Please enter your password below to confirm you wish to reactivate your account.</label></div>
                    <div><input class="form-control" type="password" name="c_password"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Re-Activate Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Cancel Account</h4>
            </div>
            <form action="/admin/billing" method="post">
                <input type="hidden" name="cancel" value="1">
                <input type="hidden" name="csrf" value="<?php echo $data["csrf"]; ?>">
                <div class="modal-body">

                    <div><label>Please enter your password below to confirm you wish to cancel your account. You account will still be active up until <?php echo date("d/m/Y",$data["next_payment"]); ?> </label></div>
                    <div><input class="form-control" type="password" name="c_password"></div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Payment Card Details</h4>
            </div>
            <form action="/admin/billing" method="post" id="payment-form">
                <input type="hidden" name="csrf" value="<?php echo $data["csrf"]; ?>">
                <div class="modal-body">
                    <script src="https://js.stripe.com/v3/"></script>
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>


    <?php if( $data["card"] ){
        $data["card"]["type"] = ( strstr($data["card"]["type"],'american') ) ? 'amex' : $data["card"]["type"];
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-2 col-sm-offset-5" style="text-align: center">
                <div class="credit_card">
                    <div class="supplier"><img src="/assets/img/card/<?php echo $data["card"]["type"]; ?>.gif"></div>
                    <div class="card_number">**** **** **** <?php echo $data["card"]["ending"]; ?></div>
                    <div class="expiry_date"><?php echo substr($data["card"]["expiry"],0,2).'/'.substr($data["card"]["expiry"],2,2); ?></div>
                </div>
                <div style="margin-top:10px">
                <input type="button" class="btn btn-success addCard" value="Update Card">
                </div>
            </div>
        </div>
    <?php }else{ ?>
    <div style="text-align: center">
        <input type="button" class="btn btn-success addCard" value="Add Card Details">
    </div>
    <?php } ?>



    <?php if( Config::getChargeLine() ){ ?>
        <div class="row" style="margin-top:20px">
            <div class="col-md-6 col-md-offset-3">
                <div class="alert alert-success">
                    <p><?php echo Config::getChargeLine(); ?></p>
                </div>
            </div>
        </div>
    <?php } ?>

    <h3>Invoices</h3>


    <?php if( count($data["invoices"]) == 0 ){ ?>

    <div style="text-align: center">
        <strong>No Invoices Created</strong>
    </div>

    <?php }else{ ?>

    <table class="table">
        <tr>
            <th>No.</th>
            <th>Date</th>
            <th>Amount</th>
            <th>Status</th>
        </tr>

        <?php foreach( $data["invoices"] as $invoice ){ ?>
        <tr>
            <td><a href="/admin/billing/invoice/<?php echo $invoice["id"]; ?>" target="_blank"><?php echo str_pad( $invoice["id"],6,'0',STR_PAD_LEFT); ?></a></td>
            <td><?php echo $invoice["date"]; ?></td>
            <td><?php echo $invoice["amount"]; ?></td>
            <td><?php echo $invoice["status"]; ?></td>
        </tr>
        <?php } ?>

    </table>

    <?php } ?>



<?php
if( !isset($_GET["no_headers"])) {
    View::page('Core/app/footer');
}
?>

<script>

    $('.addCard').click( function(){
       $('#myModal').modal('show');
    });


    $('.cancel_account').click( function(){
        $('#cancelModal').modal('show');
    });

    $('.uncancel_account').click( function(){
        $('#uncancelModal').modal('show');
    });



</script>

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