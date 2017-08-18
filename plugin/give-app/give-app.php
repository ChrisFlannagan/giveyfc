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
require __DIR__ . '/includes/functions.php';

class Give_App_API {

	public function __construct() {
		/**
		 * For development purposes set an email to test all emails sent through system
		 */
		if ( strpos( $_SERVER['HTTP_HOST'], '.dev' ) !== false ) {
			add_filter( 'wp_mail', function ( $args ) {
				$args['to'] = 'chris@flowpress.com';

				return $args;
			} );

			add_filter( 'wp_mail_from', function ( $original_email_address ) {
				return 'chris@flowpress.com';
			} );
		}

		if ( ! class_exists( 'Give' ) ) {
			return;
		}

		add_filter( 'show_admin_bar', '__return_false' );

		add_action( 'init', array( $this, 'add_endpoints' ), 2 );
		add_action( 'template_redirect', array( $this, 'choose_endpoint' ) );

		add_action( 'init', array( $this, 'add_tribe_to_json_api' ), 30 );
		add_action( 'rest_api_init', [ $this, 'add_fields' ] );
	}

	public function add_fields() {
		register_rest_field( 'tribe_events', 'datesort', [
			'get_callback' => [ $this, 'date_sort_field' ],
		] );
		register_rest_field( 'tribe_events',
			'event_details',
			[
				'get_callback'    => array( $this, 'slug_get_address' ),
				'update_callback' => null,
				'schema'          => null,
			]
		);
	}

	public function date_sort_field( $object, $field_name, \WP_REST_Request $request ) {
		$start_date = get_post_meta( $object['id'], '_EventStartDate', true );
		return strtotime( $start_date );
	}

	function add_tribe_to_json_api() {
		global $wp_post_types;
		$wp_post_types['tribe_events']->show_in_rest = true;
	}

	function slug_get_address( $object, $field_name, $request ) {
		$meta     = [
			'image'    => '',
			'venue'    => '',
			'address'  => '',
			'address2' => '',
			'start'    => get_post_meta( $object['id'], '_EventStartDate', true ),
			'end'      => get_post_meta( $object['id'], '_EventEndDate', true ),
		];
		$venue_id = intval( get_post_meta( $object['id'], '_EventVenueID', true ) );
		if ( 0 < $venue_id ) {
			$address  = get_post_meta( $venue_id, '_VenueAddress', true );
			$address2 = get_post_meta( $venue_id, '_VenueCity', true );
			$address2 .= ', ' . get_post_meta( $venue_id, '_VenueState', true );
			$address2 .= ' ' . get_post_meta( $venue_id, '_VenueZip', true );

			$meta = wp_parse_args( [
				'address'  => $address,
				'address2' => $address2,
				'venue'    => get_the_title( $venue_id ),
			], $meta );
		}

		$thumbnail = get_post_thumbnail_id( $object['id'] );
		if ( $thumbnail && $thumbnail > 0 ) {
			$meta['image'] = wp_get_attachment_url( $thumbnail );
		}

		return $meta;
	}

	public function add_endpoints( $rewrite_rules ) {
		add_rewrite_endpoint( 'give-app-api', EP_ALL );

		if ( ! get_option( 'add_endpoint_give_app_api' || isset( $_GET['reset_gapp'] ) ) ) {
			add_option( 'add_endpoint_give_app_api', true );
			flush_rewrite_rules();
		}

		add_rewrite_endpoint( 'give-app-history', EP_ALL );

		if ( ! get_option( 'add_endpoint_give_app_history' ) || isset( $_GET['reset_gapp'] ) ) {
			add_option( 'add_endpoint_give_app_history', true );
			flush_rewrite_rules();
		}

		add_rewrite_endpoint( 'give-app-resetpin', EP_ALL );

		if ( ! get_option( 'add_endpoint_give_app_resetpin' ) || isset( $_GET['reset_gapp'] ) ) {
			add_option( 'add_endpoint_give_app_resetpin', true );
			flush_rewrite_rules();
		}
	}

	public function choose_endpoint() {
		global $wp_query;
		$api_request = array();
		if ( isset( $wp_query->query_vars['give-app-api'] ) ) {
			$api_request = explode( '/', $wp_query->query_vars['give-app-api'] );
			switch ( $api_request[0] ) {
				case 'register':
				case 'login' :
					include __DIR__ . '/classes/signinrequest.php';
					$signin = new SignInRequest( $api_request );
					break;
				case 'donate' :
					include __DIR__ . '/classes/formrequest.php';
					$form = new FormRequest( $api_request );
					break;
			}

			return;
		}

		if ( isset( $wp_query->query_vars['give-app-history'] ) ) {
			include __DIR__ . '/classes/historyrequest.php';
			$api_request = explode( '/', $wp_query->query_vars['give-app-history'] );
			$history     = new HistoryRequest( $api_request );

			return;
		}

		if ( isset( $wp_query->query_vars['give-app-resetpin'] ) ) {
			include __DIR__ . '/classes/resetpin.php';
			$api_request = explode( '/', $wp_query->query_vars['give-app-resetpin'] );
			$resetpin    = new ResetPin( $api_request );
		}

		return;
	}
}

add_action( 'plugins_loaded', function () {
	if ( class_exists( 'Give_App_API' ) ) {
		$Give_App_API = new Give_App_API();
	}
} );