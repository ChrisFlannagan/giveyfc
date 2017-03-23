<?php
/**
 * Class FormRequest
 *
 * Deliver a form requested
 */

class FormRequest {
    public function __construct( $api_request = array(), $head = '', $foot = '' ) {

        // Log the user into their WordPress account
        $user_id       = $api_request[1];
        $give_id       = $api_request[2];
        $fname         = '';
        $lname         = '';
        $email         = '';

        if ( get_user_meta( intval( $user_id ), '_user_security_pin_g8js3', true ) == md5( GA_API_HASH . $api_request[3] )
            || ( ! isset( $api_request[5] ) && get_user_meta( intval( $user_id ), '_user_security_pin_g8js3', true ) == $api_request[3] ) ) : // Backwards compatibility

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


        $results = '';
        $form = ( isset( $api_request[4] ) ? intval( $api_request[4] ) : '1786' );
        if ( 0 < intval( $api_request[1] ) ) {
            $temp = template_parts();
            echo $temp['head'];
            ?>
            <body>
                <div class="give-app-api form">
                    <div id="allforms">
                        <select name="theforms" id="selectform" onchange="window.location.href='<?php
                        echo site_url() . '/give-app-api/donate/' . $user_id . '/' . $give_id . '/' . $api_request[3] . '/'; ?>'+this.options[this.selectedIndex].value+'<?php
                        if ( isset( $api_request[5] ) ) echo '/' . $api_request[5];
                        ?>';">
                            <?php
                            $form_list = get_posts( array(
                                'post_type'        => 'give_forms',
                                'posts_per_page'   => -1,
                                'orderby' => 'post_title',
                                'order' => 'ASC',
                            ) );

                            if ( $form_list ) : foreach ( $form_list as $form_info ) : ?>
                                <option value="<?php echo $form_info->ID; ?>"<?php
                                if ( strval( $form_info->ID ) == $form ) : ?>
                                    selected="selected"
                                <?php endif; ?>><?php echo $form_info->post_title; ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                <?php

                echo do_shortcode( '[give_form id="' . esc_attr( $form ) . '"]' );
                ?>

            <style>
                <?php if ( $fname != '' && $lname != '' && $email != '' ) : ?>
                #give_checkout_user_info {
                    display: none;
                }
            <?php endif; ?>
            </style>
            <?php
            echo $temp['foot'];
            exit();
        }
    }


}