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

        add_action( 'init', array( $this, 'add_tribe_to_json_api' ), 30 );
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
        $api_request = array();

        if ( ! isset( $wp_query->query_vars['give-app-api'] ) ) {
            $api_request = explode( '/', $wp_query->query_vars['give-app-api'] );
            switch ( $api_request[0] ) {
                case 'register' || 'login' :
                    include __DIR__ . '/classes/signinrequest.php';
                    $signin = new SignInRequest( $api_request );
                    break;
                case 'donate' :
                    include __DIR__ . '/classes/formrequest.php';
                    $form = new FormRequest( $api_request );
                    break;
            }
        }

        if ( ! isset( $wp_query->query_vars['give-app-history'] ) ) {
            include __DIR__ . '/classes/historyrequest.php';
            $api_request = explode( '/', $wp_query->query_vars['give-app-history'] );
            $history = new HistoryRequest();
        }

        return;
	}

    function add_tribe_to_json_api() {
        global $wp_post_types;
        $wp_post_types['tribe_events']->show_in_rest = true;
    }
}

if ( class_exists( 'Give_App_API' ) ) {
	$Give_App_API = new Give_App_API();
}