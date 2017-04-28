<?php
/**
 * Plugin Name: Give - Stripe Save Payment
 * Author: Chris Flannagan
 * Author URI: https://whoischris.com
**/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GSS_DIR', dirname( __FILE__ ) );

include( GSS_DIR . '/vendor/autoload.php' );
include( GSS_DIR . '/functions.php' );
include( GSS_DIR . '/includes/form.php' );

add_action( 'give_give_stripe_save_billing_fields', '__return_false' );
add_action( 'give_give_stripe_save_cc_address_fields', '__return_false' );
add_action( 'give_give_stripe_save_cc_form', 'give_stripe_save_credit_card_form' );

add_filter( 'give_payment_gateways', function() {
    $gateways['give_stripe_save'] = array(
        'admin_label'    => esc_attr__( 'Stripe Save Customer', 'give-stripe-save' ),
        'checkout_label' => esc_attr__( 'Stripe Save Customer', 'give-stripe-save' )
    );
    return $gateways;
} );

add_action( 'give_gateway_give_stripe_save', function( $purchase_data ) {
    $payment_data = array(
        'price'           => $purchase_data['price'],
        'give_form_title' => $purchase_data['post_data']['give-form-title'],
        'give_form_id'    => intval( $purchase_data['post_data']['give-form-id'] ),
        'give_price_id'   => isset( $purchase_data['post_data']['give-price-id'] ) ? $purchase_data['post_data']['give-price-id'] : '',
        'date'            => $purchase_data['date'],
        'user_email'      => $purchase_data['user_email'],
        'purchase_key'    => $purchase_data['purchase_key'],
        'currency'        => give_get_currency(),
        'user_info'       => $purchase_data['user_info'],
        'status'          => 'pending',
        'gateway'         => 'give_stripe_save',
        'token'           => $purchase_data['post_data']['stripeToken'],
    );

    if ( isset( $purchase_data['post_data']['give-stripe-save-info'] ) ) {
        $payment_data['savecard'] = true;
    }

    $process = process_donation( $payment_data );

    if ( $process[0] ) {
        $payment = give_insert_payment( $payment_data );
        if ( $payment ) {
            give_update_payment_status($payment, 'publish');
            give_send_to_success_page();
            give_set_error( 'stripe_error', 'There was a problem with your transaction, your card was not charged.' );
            give_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['give-gateway'] );
        } else {

        }
    } else {
        //Something went wrong outside of Stripe.
        $e = $process[1];
        give_record_gateway_error( esc_html__( 'Stripe Error', 'give-stripe' ), sprintf( esc_html__( 'The Stripe Gateway returned an error while processing a donation. Details: %s', 'give-stripe' ), $e->getMessage() ) );
        give_set_error( 'stripe_error', esc_html__( 'An error occurred while processing the donation. Please try again.', 'give-stripe' ) );
        give_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['give-gateway'] );
    }
} );

if ( is_admin() ) {
    add_filter( 'give_settings_gateways', 'gss_add_settings' );
}