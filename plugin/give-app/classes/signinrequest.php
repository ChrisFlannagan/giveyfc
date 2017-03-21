<?php
/**
 * class SignInRequest
 *
 * Registers or signs in the user with valid credentials
 */

class SignInRequest {
    public function __construct( $api_request = array() ) {


        
        if ( ! empty( $req ) ) {

            $username_gen = $api_request[1] . rand();
            $user_id      = username_exists( $username_gen );

            if ( ! $user_id && email_exists( $api_request[2] ) == false ) {

                $results         = Give()->customers->add( array(
                    'email' => $api_request[2],
                    'name'  => str_replace( '%20', '', $api_request[1] )
                ) );

                $real_name       = explode( '%20', $api_request[1] );
                $first_name      = $real_name[0];
                $last_name       = ( isset( $real_name[1] ) ? $real_name[1] : 'Lastname' );

                $random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
                $user_id         = wp_create_user( $username_gen, $random_password, $api_request[2] );

                update_user_meta( $user_id, '_user_security_pin_g8js3', $api_request[3] );
                update_user_meta( $user_id, 'first_name', $first_name );
                update_user_meta( $user_id, 'last_name', $last_name );

                $results .= "|SP|" . $user_id . "|SP|" . $random_password;

            } else {

                $customers = Give()->customers->get_customers( array( 'email' => $api_request[2] ) );
                if ( isset( $customers[0] ) ) {
                    $results = $customers[0]->id;
                    $user    = get_user_by( 'email', $api_request[2] );
                    if ( get_user_meta( $user->id, '_user_security_pin_g8js3', true ) == $api_request[3] ) {
                        $results .= "|SP|" . $user->id . "|SP|nup|" . md5( GA_API_HASH . get_user_meta( $user->id, '_user_security_pin_g8js3', true ) );
                    } else {
                        $results = 'Incorrect PIN given for user';
                    }
                } else {
                    $results = 'Give customer was not found';
                }

            }
        }
    }
}