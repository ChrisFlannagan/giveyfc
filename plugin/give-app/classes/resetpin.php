<?php

/**
 * Reset users pin for app
 */

class ResetPin
{
    public function __construct( $api_request = array() )
    {
	    $results = array( 'success' => '0', 'msg' => 'Error encountered' );
	    $user    = get_user_by( 'email', $api_request[0] );
	    if ( $user ) {
	    	$new_pin = rand( 1000, 9999 );
		    update_user_meta( $user->ID, '_user_security_pin_g8js3', md5( GA_API_HASH . $new_pin ) );
		    wp_mail( $api_request[0], 'Your new PIN number', 'Your new PIN number for the giving app is: ' . $new_pin );
		    $results['success'] = '1';
		    $results['msg'] = 'Check email ' . $api_request[0] . ' for new Pin';
	    } else {
			$results['msg'] = 'User email not found.';
	    }
	    wp_send_json($results);
        exit;
    }
}