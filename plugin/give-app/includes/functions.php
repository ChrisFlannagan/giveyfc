<?php

function template_parts() {

    /** Generate header */

    ob_start(); ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Donate</title>
        <script
                src="https://code.jquery.com/jquery-3.1.1.min.js"
                integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
                crossorigin="anonymous"></script>
        <?php /* <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css"/>
            <link rel="stylesheet"
                  href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css"/>
            <link rel="stylesheet"
                  href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css"/> */ ?>
        <style>
            body {
                padding: 10px;
            }
        </style>
        <?php wp_head(); ?>
    </head><?php
    $t_head = ob_get_clean();



    /** Generate footer */

    ob_start();
    echo remove_template_parts();
    wp_footer(); ?>
    </div>
    </body>
    </html><?php
    $t_foot = ob_get_clean();
    return array( 'head' => $t_head, 'foot' => $t_foot );
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
        #selectform {
            font-size: 25px;
        }

        /** STRIPE MESSAGES */
        .give_error, .give_success {
            color: Red;
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

        .give-clearfix:after, .give-clearfix:before {
            clear: both;
        }

        .give-recurring-donors-choice label {
            color: #FFF;
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