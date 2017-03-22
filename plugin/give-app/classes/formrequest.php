<?php
/**
 * Class FormRequest
 *
 * Deliver a form requested
 */

class FormRequest {
    public function __construct( $api_request = array() ) {
        $results = '';
        $form = ( isset( $api_request[4]) ? intval( $api_request[4] ) : '1786' );
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
                <div class="give-app-api form">
                    <div id="allforms">
                        <select name="theforms" id="selectform">
                            <?php
                            $form_list = get_posts( array(
                                'post_type'        => 'give_forms',
                                'posts_per_page'   => -1,
                                'orderby' => 'post_title',
                                'order' => 'ASC',
                            ) );

                            if ( $form_list ) : foreach ( $form_list as $form_info ) : ?>
                                <option value="<?php echo $form_info->ID; ?>"<?php
                                if ( strval( $form_info->ID ) == $api_request[4] ) : ?>
                                    selected="selected"
                                <?php endif; ?>><?php echo $form_info->post_title; ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
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

                echo do_shortcode( '[give_form id="' . esc_attr( $form ) . '"]' );
                ?>

            <style>
                <?php if ( $fname != '' && $lname != '' && $email != '' ) : ?>
                #give_checkout_user_info {
                    display: none;
                }
            <?php endif; ?>
            </style>
            <?php echo $this->remove_template_parts(); ?>
            <?php wp_footer(); ?>
            </div>
            </body>
            </html>
            <?php
            exit();
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
            #allforms {
                margin-bottom: 30px;
            }

            /** DONATION AMOUNT INPUTS  */
            form[id*=give-form] .give-donation-amount .give-currency-symbol {
                background-color: #f2f2f2;
                border-top: 1px solid #ccc;
                border-bottom: 1px solid #ccc;
                color: #333;
                margin: 0;
                padding: 0 12px;
                height: 51px;
                line-height: 35px;
                font-size: 48px;
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box;
                float: none;
            }
            form[id*=give-form] .give-donation-amount #give-amount, form[id*=give-form] .give-donation-amount #give-amount-text {
                border: 1px solid #ccc;
                background: #FFF;
                border-radius: 0;
                height: 60px;
                line-height: 35px;
                padding: 0 12px;
                margin: 0;
                font-size: 42px;
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box;
                min-width: 125px;
                float: none;
            }


            html form[id*=give-form] #give-final-total-wrap .give-final-total-amount {
                color: #333;
            }
            form[id*=give-form] .give-donation-amount #give-amount, form[id*=give-form] .give-donation-amount #give-amount-text {
                display: inline-block;
            }
            .give-submit-button-wrap .give-submit, [id^=give-user-login-submit] .give-submit {
                border: solid 3px #fff;
                background: #0c6e69;
                color: #fff;
            }
            .give-recurring-donors-choice {
                border: 1px solid #333;
                background: #333;
            }
            #give_purchase_submit {
                text-align: center;
            }
            #give-final-total-wrap {
                text-align: center;
            }
            .give-app-api.form {
                background: #333;
                padding: 30px;
                left: 0;
                top: 0;
                position: absolute;
                width: 100%;
                z-index: 99999;
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