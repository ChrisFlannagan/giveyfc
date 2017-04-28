<?php

function give_stripe_save_credit_card_form( $form_id ) {

    $pk = give_get_option( 'live_publishable_key_save' );
    if ( give_is_test_mode() ) {
        $pk = give_get_option( 'test_publishable_key_save' );
    }

    ob_start();

    do_action( 'give_before_cc_fields', $form_id );

    if ( is_user_logged_in() ) : ?>

    <div class="give-recurring-donors-choice">

        <input id="give-stripe-save-info" name="give-stripe-save-info" type="checkbox" checked="checked" />
        <label for="give-stripe-save-info">
            <?php esc_html_e( 'Securely Save Payment Information?', 'give-stripe-save' ); ?></label>

    </div>

    <?php endif; ?>

    <fieldset id="give_cc_fields" class="give-do-validate">

        <legend><?php esc_html_e( 'Credit Card Info', 'give-stripe-save' ); ?></legend>

        <?php if ( is_ssl() ) : ?>
            <div id="give_secure_site_wrapper">
                <span class="give-icon padlock"></span>
                <span><?php esc_html_e( 'This is a secure SSL encrypted payment.', 'give-stripe-save' ); ?></span>
            </div>
        <?php endif; ?>

        <p id="give-card-number-wrap" class="form-row form-row-two-thirds form-row-responsive">
            <label for="card_number" class="give-label">
                <?php esc_html_e( 'Card Number', 'give-stripe' ); ?>
                <span class="give-required-indicator">*</span>
                <span class="give-tooltip give-icon give-icon-question"
                      data-tooltip="<?php esc_attr_e( 'The (typically) 16 digits on the front of your credit card.', 'give-stripe' ); ?>"></span>
                <span class="card-type"></span>
            </label>
            <input type="tel" autocomplete="off" name="card_number" id="card_number" class="card-number give-input required"
                   placeholder="<?php esc_attr_e( 'Card number', 'give-stripe-save' ); ?>"/>
        </p>

        <p id="give-card-cvc-wrap" class="form-row form-row-one-third form-row-responsive">
            <label for="card_cvc" class="give-label">
                <?php esc_html_e( 'CVC', 'give-stripe' ); ?>
                <span class="give-required-indicator">*</span>
                <span class="give-tooltip give-icon give-icon-question"
                      data-tooltip="<?php esc_attr_e( 'The 3 digit (back) or 4 digit (front) value on your card.', 'give-stripe' ); ?>"></span>
            </label>
            <input type="tel" size="4" autocomplete="off" name="card_cvc" id="card_cvc"
                   class="card-cvc give-input required"
                   placeholder="<?php esc_attr_e( 'Security code', 'give-stripe-save' ); ?>"/>
        </p>

        <p id="give-card-name-wrap" class="form-row form-row-two-thirds form-row-responsive">
            <label for="card_name" class="give-label">
                <?php esc_html_e( 'Name on the Card', 'give-stripe' ); ?>
                <span class="give-required-indicator">*</span>
                <span class="give-tooltip give-icon give-icon-question"
                      data-tooltip="<?php esc_attr_e( 'The name printed on the front of your credit card.', 'give-stripe' ); ?>"></span>
            </label>

            <input type="text" autocomplete="off" name="card_name" id="card_name"
                   class="card-name give-input required"
                   placeholder="<?php esc_attr_e( 'Card name', 'give-stripe-save' ); ?>"/>
        </p>

        <?php do_action( 'give_before_cc_expiration' ); ?>

        <p class="card-expiration form-row form-row-one-third form-row-responsive">
            <label for="card_expiry" class="give-label">
                <?php esc_html_e( 'Expiration', 'give-stripe-save' ); ?>
                <span class="give-required-indicator">*</span>
                <span class="give-tooltip give-icon give-icon-question"
                      data-tooltip="<?php esc_attr_e( 'The date your credit card expires, typically on the front of the card.', 'give-stripe' ); ?>"></span>
            </label>

            <input type="hidden" id="card_exp_month" name="card_exp_month" class="card-expiry-month"/>
            <input type="hidden" id="card_exp_year" name="card_exp_year" class="card-expiry-year"/>

            <input type="tel" autocomplete="off" name="card_expiry" id="card_expiry"
                   class="card-expiry give-input required"
                   placeholder="<?php esc_attr_e( 'MM/YY', 'give-stripe-save' ); ?>"/>
        </p>

        <?php do_action( 'give_after_cc_expiration', $form_id ); ?>

    </fieldset>

    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script type="text/javascript">
        Stripe.setPublishableKey('<?php echo $pk; ?>');
        (function($) {
            $(function () {
                var $form = $('#give-form-<?php echo $form_id; ?>');
                $form.submit(function (event) {
                    // Disable the submit button to prevent repeated clicks:
                    $form.find('.submit').prop('disabled', true);

                    $form.addClass('stripe-checkout');

                    give_stripe_process_card($form);

                    // Prevent the form from being submitted:
                    return false;
                });
            });
            function stripeResponseHandler(status, response) {
                // Grab the form:
                var $form = $('#give-form-<?php echo $form_id; ?>');

                if (response.error) { // Problem!
                    //the error
                    var error = '<div class="give_errors"><p class="give_error">' + response.error.message + '</p></div>';

                    // show the errors on the form
                    $form.find('[id^=give-stripe-save-payment-errors]').html(error);

                } else { // Token was created!

                    // Get the token ID:
                    var token = response.id;

                    // Insert the token ID into the form so it gets submitted to the server:
                    $form.append($('<input type="hidden" name="stripeToken">').val(token));

                    // Submit the form:
                    $form.get(0).submit();
                }
            }
            ;

            function give_stripe_process_card($form) {

                // disable the submit button to prevent repeated clicks
                $form.find('[id^=give-purchase-button]').attr('disabled', 'disabled');


                var card_state = '';

                if ($form.find('.billing_country').val() == 'US') {
                    state = $form.find('[id^=card_state_us]').val();
                } else if ($form.find('.billing_country').val() == 'CA') {
                    state = $form.find('[id^=card_state_ca]').val();
                } else {
                    state = $form.find('[id^=card_state_other]').val();
                }

                //Validate card state & country data if present
                if (typeof $form.find('[id^=card_state_us]').val() != 'undefined') {

                    if ($form.find('.billing_country').val() == 'US') {
                        state = $form.find('[id^=card_state_us]').val();
                    } else if ($form.find('.billing_country').val() == 'CA') {
                        state = $form.find('[id^=card_state_ca]').val();
                    } else {
                        state = $form.find('[id^=card_state_other]').val();
                    }

                } else {
                    state = $form.find('.card_state').val();
                }

                // createToken returns immediately - the supplied callback submits the form if there are no errors
                Stripe.createToken({
                    number: $form.find('.card-number').val().replace(/\s+/g, ''),
                    name: $form.find('.card-name').val(),
                    cvc: $form.find('.card-cvc').val().replace(/\s+/g, ''),
                    exp_month: $form.find('.card-expiry-month').val(),
                    exp_year: $form.find('.card-expiry-year').val(),
                    address_line1: $form.find('.card-address').val(),
                    address_line2: $form.find('.card-address-2').val(),
                    address_city: $form.find('.card-city').val(),
                    address_state: state,
                    address_zip: $form.find('.card-zip').val(),
                    address_country: $form.find('[id^=billing_country]').val()
                }, stripeResponseHandler );

                return false; // submit from callback
            }
        })(jQuery);
    </script>
    <?php

    remove_action( 'give_after_cc_fields', 'give_default_cc_address_fields' );
    do_action( 'give_after_cc_fields', $form_id );

    $form = ob_get_clean();
    echo $form;
}


function give_stripe_save_add_stripe_errors() {
    echo '<div id="give-stripe-save-payment-errors"></div>';
}
add_action( 'give_after_cc_fields', 'give_stripe_save_add_stripe_errors', 999 );