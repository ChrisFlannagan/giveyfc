<?php
/**
 * class SignInRequest
 *
 * Registers or signs in the user with valid credentials
 */

class SignInRequest {
    public function __construct( $api_request = array() ) {
        $results = array( 'success' => '0' );

        if ( ! empty( $api_request ) ) {

            $username_gen = $api_request[1] . rand();
            $user_id      = username_exists( $username_gen );

            if ( ! $user_id && email_exists( $api_request[2] ) == false ) {

                $giveid         = Give()->customers->add( array(
                    'email' => $api_request[2],
                    'name'  => str_replace( '%20', '', $api_request[1] )
                ) );

                $real_name       = explode( '%20', $api_request[1] );
                $first_name      = $real_name[0];
                $last_name       = ( isset( $real_name[1] ) ? $real_name[1] : 'Lastname' );

                $random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
                $user_id         = wp_create_user( $username_gen, $random_password, $api_request[2] );

                update_user_meta( $user_id, '_user_security_pin_g8js3', md5( GA_API_HASH . $api_request[3] ) );
                update_user_meta( $user_id, 'first_name', $first_name );
                update_user_meta( $user_id, 'last_name', $last_name );

                $results['userid'] = (String)$user_id;
                $results['giveid'] = (String)$giveid;
                $results['success'] = '1';

            } else {
                $customers = Give()->customers->get_customers( array( 'email' => $api_request[2] ) );
                if ( isset( $customers[0] ) ) {
                    $user    = get_user_by( 'email', $api_request[2] );

                    $hash_pin = md5( GA_API_HASH . $api_request[3] );
                    if ( get_user_meta( $user->id, '_user_security_pin_g8js3', true ) == $hash_pin ) {
                        $results['userid'] = (String)$user->id;
                        $results['giveid'] = (String)$customers[0]->id;
                        $results['success'] = '1';
                    } else {

                        /** Backwards compatible, remove in three months */
                        if ( ! isset( $api_request[4] ) && get_user_meta( $user->id, '_user_security_pin_g8js3', true ) == $api_request[3] ) {
                            $results['userid'] = (String)$user->id;
                            $results['giveid'] = (String)$customers[0]->id;
                            $results['success'] = '1';
                        }
                        $results['msg'] = 'Incorrect PIN given for user';
                    }
                } else {
                    $results['msg'] = 'Give customer was not found';
                }

            }
        }
        if ( isset( $api_request[4] ) && 'v2' == $api_request[4] ) {
            wp_send_json($results);
        } elseif ( $results['success'] == 0 ) {
            echo '0';
        } elseif ( $results['success'] == 1 ) {
            echo $results['giveid'] . '|SP|' . $results['userid'] . '|SP|nup' . $results['success'];
        }
        exit();
    }
}