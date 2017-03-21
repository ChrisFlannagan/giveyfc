<?php
/*
Plugin Name: Give App API
Plugin URI: http://whoischris.com
Description:  Access Give Features via REST API
Author: Chris Flannagan
Version: 2.0
Author URI: http://whoischris.com/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/includes/constants.php';

class Give_App_API {
    public function __construct() {

        if ( ! class_exists( 'Give' ) ) {
            return;
        }

		add_action( 'init', array( $this, 'add_endpoint' ) );
		add_action( 'init', array( $this, 'add_history_endpoint' ) );
		add_action( 'template_redirect', array( $this, 'choose_endpoint' ) );
	}

    public function add_endpoints( $rewrite_rules ) {
        add_rewrite_endpoint( 'give-app-api', EP_ALL );

        if ( ! get_option( 'add_endpoint_give_app_api' ) ) {
            add_option( 'add_endpoint_give_app_api', true );
            flush_rewrite_rules();
        }

        add_rewrite_endpoint( 'give-app-history', EP_ALL );

        if ( ! get_option( 'add_endpoint_give_app_history' ) ) {
            add_option( 'add_endpoint_give_app_history', true );
            flush_rewrite_rules();
        }
    }

	public function choose_endpoint() {
        global $wp_query;
        if ( ! isset( $wp_query->query_vars['give-app-api'] ) ) {
            return;
        }

		if ( isset( $wp_query->query_vars['give-app-api'] ) ) {
            $api_request = explode( '/', $wp_query->query_vars['give-app-api'] );
            switch ( $api_request[0] ) {
                case 'register' || 'login' :
                    include __DIR__ . '/classes/signinrequest.php';
                    $signin = new SignInRequest();
                    break;
                case 'donate' :
                    include __DIR__ . '/classes/formrequest.php';
                    $signin = new SignInRequest();
                    break;
            }
		}

		if ( isset( $wp_query->query_vars['give-app-history'] ) ) {
			$this->give_app_history_return();
		}
	}

	/**
	 * Api form endpoint
	 */

	public function give_app_api_return() {
		ob_start();
		global $wp_query;
		$results = '';

		if ( ! isset( $wp_query->query_vars['give-app-api'] ) ) {
			return;
		}

		if ( class_exists( 'Give' ) ) {
			/**
			 * This is the meat of the API.  Give must be installed.
			 * Here we check what the routing is and based on the url structure perform
			 * different asks
			 */

			$api_request = explode( '/', $wp_query->query_vars['give-app-api'] );

			/** REGISTER A NEW USER FROM APP HOME SCREEN */
			if ( 'register' == $api_request[0] ) {
			}

			/** DISPLAY CUSTOMER FORM */
			if ( 'donate' == $api_request[0] && intval( $api_request[1] ) ) {
				// Setup our customers information
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
					if ( get_user_meta( intval( $user_id ), '_user_security_pin_g8js3', true ) == $api_request[3] ) {
						$user = get_user_by( 'id', $user_id );
						if ( $user ) {
							wp_set_current_user( $user_id, $user->user_login );
							wp_set_auth_cookie( $user_id );
							do_action( 'wp_login', $user->user_login );
							$personal_info = get_userdata( $user_id );
							$fname         = $personal_info->first_name;
							$lname         = $personal_info->last_name;
							$email         = $personal_info->user_email;
						}

					}

					echo do_shortcode( '[give_form id="1786"]' );
					?>

				</div><!-- #container -->
				<style>
					<?php
						if ( $fname != '' && $lname != '' && $email != '' ) {
							?>
					#give_checkout_user_info {
						display: none;
					}

					<?php
						}
					?>
				</style>
				<?php echo remove_template_parts(); ?>
				<?php wp_footer(); ?>
				</body>
				</html>
				<?php
			}
		}
		ob_flush();
		echo $results;
		exit;
	}

	/**
	 * Donation history endpoint
	 */

	public function give_app_history_return() {
		global $wp_query;
		if ( ! isset( $wp_query->query_vars['give-app-history'] ) ) {
			return;
		}

		$api_request = explode( '/', $wp_query->query_vars['give-app-history'] );
		$userid = $api_request[0];
		$pin = $api_request[1];
		$donations = give_get_users_purchases( intval( $userid ), 20, true, 'any' );
		$donations_return = array();
		if ( $donations ) :
			foreach ( $donations as $post ) : setup_postdata( $post );

				$donation_data = give_get_payment_meta( $post->ID );
				$type = 'One Time Donation';
				if ( $post->post_status != 'publish' && $post->post_status != 'subscription' ) :
					$type = 'One Time Donation';
				else :
					$type = 'Recurring';
				endif;
				$donations_return[] = array(
					'id' => give_get_payment_number( $post->ID ),
					'data' => date_i18n( get_option( 'date_format' ), strtotime( get_post_field( 'post_date', $post->ID ) ) ),
					'amt' => give_currency_filter( give_format_amount( give_get_payment_amount( $post->ID ) ) ),
					'type' => $type,
				);

			endforeach;
			wp_reset_postdata();
		endif;

		echo json_encode( $donations_return );

		exit;
	}
}

if ( class_exists( 'Give_App_API' ) ) {
	$Give_App_API = new Give_App_API();
}

add_action( 'init', 'add_anuncios_to_json_api', 30 );
function add_anuncios_to_json_api() {
	global $wp_post_types;
	$wp_post_types['tribe_events']->show_in_rest = true;
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