<?php
/**
 * Class FormRequest
 *
 * Deliver a form requested
 */

class FormRequest {
    public function __construct( $api_request = array() ) {
        $results = '';
        $form = '1786';
        if ( intval( $api_request[1] ) ) {
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>Donate</title>
                <script
                    src="https://code.jquery.com/jquery-3.1.1.min.js"
                    integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
                    crossorigin="anonymous"></script>
                <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css"/>
                <link rel="stylesheet"
                      href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css"/>
                <link rel="stylesheet"
                      href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css"/>
                <style>
                    body {
                        padding: 10px;
                    }
                </style>
                <?php wp_head(); ?>
            </head>
            <body>
            <div id="container">

                <h3 style="color: #ccc;">Give to EAYFC</h3>
                <?php
                // Log the user into their WordPress account
                $user_id       = $api_request[1];
                $give_id       = $api_request[2];
                $personal_info = array();
                $fname         = '';
                $lname         = '';
                $email         = '';
                if ( get_user_meta( intval( $user_id ), '_user_security_pin_g8js3', true ) == $api_request[3] ) :
                    $user = get_user_by( 'id', $user_id );
                    if ( $user ) :
                        wp_set_current_user( $user_id, $user->user_login );
                        wp_set_auth_cookie( $user_id );
                        do_action( 'wp_login', $user->user_login );
                        $personal_info = get_userdata( $user_id );
                        $fname         = $personal_info->first_name;
                        $lname         = $personal_info->last_name;
                        $email         = $personal_info->user_email;
                    endif;
                endif;

                echo do_shortcode( '[give_form id="' . esc_attr( $form ) . ']"]' );
                ?>

            </div><!-- #container -->
            <style>
                <?php if ( $fname != '' && $lname != '' && $email != '' ) : ?>
                #give_checkout_user_info {
                    display: none;
                }
            <?php endif; ?>
            </style>
            <?php echo $this->remove_template_parts(); ?>
            <?php wp_foot(); ?>
            </body>
            </html>
            <?php
        }
    }

    function remove_template_parts() {
        ob_start(); ?>
        <style>
            body {
                background: #606960;
                color: #FFF;
                font-size: 12px;
                font-weight: bold;
                font-family: Arial, SansSerif;
                text-align: center;
            }

            legend {
                display: none;
            }

            .wide-input {
                width: 90%;
            }

            input {
                font-size: 18pt;
                color: #000;
            }

            .give-input {
                width: 90%;
            }

            .form-row label {
                color: #FFF;
            }

            .give-currency-symbol {
                font-size: 20px;
            }

            .give-form-title {
                display: none;
            }

            .wpadminbar {
                display: none;
            }

            .submit {
                width: 100%;
                background: #333;
                color: #FFF;
                font-size: 18pt;
            }

            @media screen and (max-width: 767px) and (min-width: 320px) {
                html #give_purchase_submit .give-final-total-wrap {
                    margin-top: 0px;
                }
            }
        </style>
        <?php
        return ob_get_clean();
    }
}