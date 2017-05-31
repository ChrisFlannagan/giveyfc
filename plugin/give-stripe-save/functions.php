<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function gss_add_settings( $settings ) {

    $gss_settings = array(
        array(
            'name' => __( 'Stripe Customer Settings', 'give-stripe-save' ),
            'desc' => '<hr>',
            'id'   => 'give_title_stripe_save',
            'type' => 'give_title'
        ),
        array(
            'name' => __( 'Live Secret Key', 'give-stripe-save' ),
            'desc' => __( 'Enter your live secret key, found in your Stripe Account Settings.', 'give-stripe-customer' ),
            'id'   => 'live_secret_key_save',
            'type' => 'api_key',
        ),
        array(
            'name' => __( 'Live Publishable Key', 'give-stripe-save' ),
            'desc' => __( 'Enter your live publishable key, found in your Stripe Account Settings.', 'give-stripe-customer' ),
            'id'   => 'live_publishable_key_save',
            'type' => 'text'
        ),
        array(
            'name' => __( 'Test Secret Key', 'give-stripe-save' ),
            'desc' => __( 'Enter your test secret key, found in your Stripe Account Settings.', 'give-stripe-customer' ),
            'id'   => 'test_secret_key_save',
            'type' => 'api_key'
        ),
        array(
            'name' => __( 'Test Publishable Key', 'give-stripe-save' ),
            'desc' => __( 'Enter your test publishable key, found in your Stripe Account Settings.', 'give-stripe-customer' ),
            'id'   => 'test_publishable_key_save',
            'type' => 'text',
        ),
    );

    return array_merge( $settings, $gss_settings );

}

function process_donation( $donation_data ) {

    $sk = give_get_option( 'live_secret_key_save' );
    if ( give_is_test_mode() ) {
        $sk = give_get_option( 'test_secret_key_save' );
    }

    \Stripe\Stripe::setApiKey( $sk );
    \Stripe\Stripe::setAppInfo("WordPress Give Gateway Save Customer", "1.0", "https://whoischris.com");

    $customer_id = get_user_meta( get_current_user_id(), '_give_stripe_save_customer_id', true );

    if ( '' != $customer_id ) {
        try {
            $charge = \Stripe\Charge::create( array(
                    "amount"   => intval( $donation_data['price'] * 100 ), // Amount in cents
                    "currency" => "usd",
                    "customer" => $customer_id,
                )
            );

            return array( true, $charge );
        } catch ( \Stripe\Error\Card $e ) {

            delete_user_meta( get_current_user_id(), '_get_stripe_save_customer_id' );

            return array( false, $e->getMessage() );
        }
    } elseif ( isset( $donation_data['savecard'] ) && is_user_logged_in() ) {
        try {
            $customer = \Stripe\Customer::create( array(
                    "source"      => $donation_data['token'],
                    "email"       => $donation_data['user_email'],
                    "description" => "Donor " . get_current_user_id() . ': ' . $donation_data['user_email'],
                )
            );

            // Charge the Customer instead of the card
            $charge = \Stripe\Charge::create( array(
                    "amount"   => intval( $donation_data['price'] * 100 ), // Amount in cents
                    "currency" => "usd",
                    "customer" => $customer->id,
                )
            );

            update_user_meta( get_current_user_id(), '_give_stripe_save_customer_id', $customer->id );

            return array( true, $charge );
        } catch ( \Stripe\Error\Card $e ) {
            return array( false, $e->getMessage() );
        }
    } else {
        try {
            $charge = \Stripe\Charge::create(array(
                    "amount" => intval( $donation_data['price'] * 100 ), // Amount in cents
                    "currency" => "usd",
                    "source" => $donation_data['token'],
                    "description" => "App Donation",
                )
            );
            return array( true, $charge );
        } catch ( \Stripe\Error\Card $e ) {
            return array( false, $e->getMessage() );
        }
    }
}