<?php

/**
 * Reset users pin for app
 */

class SaveSurvey
{
    public function __construct( $api_request = array() )
    {
	    $results = array( 'success' => '0', 'msg' => 'Error encountered' );
	    $user    = get_user_by( 'email', $api_request[0] );
	    $survey_results = [
	    	'q1' => $api_request[1],
		    'q2' => $api_request[2],
		    'q3' => $api_request[3],
	    ];
	    if ( $user ) {
		    update_user_meta( $user->ID, 'survey_results', $survey_results );
	    } else {
	    	$randoms = get_option( 'random_survey_results' );
	    	if ( ! $randoms ) {
	    		$randoms = [ $survey_results ];
		    } else {
			    $randoms[] = $survey_results;
		    }
		    update_option( 'random_survey_results', $randoms );
	    }
	    $results['success'] = '1';
	    $results['msg'] = "Survey Results Saved!";
	    wp_send_json($results);
        exit;
    }
}