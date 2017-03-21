<?php

/**
 * Donation history endpoint
 */

class HistoryRequest
{
    public function __construct( $api_request )
    {
        $userid = $api_request[0];
        $pin = $api_request[1];
        $donations = give_get_users_purchases( intval( $userid ), 20, true, 'any' );
        $donations_return = array();
        if ($donations) :
            foreach ($donations as $post) : setup_postdata( $post );

                $donation_data = give_get_payment_meta( $post->ID );
                $type = 'One Time Donation';
                if ( $post->post_status != 'publish' && $post->post_status != 'subscription' ) :
                    $type = 'One Time Donation';
                else :
                    $type = 'Recurring';
                endif;
                $donations_return[] = array(
                    'id' => give_get_payment_number( $post->ID ),
                    'data' => date_i18n(get_option( 'date_format' ), strtotime( get_post_field( 'post_date', $post->ID) ) ),
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