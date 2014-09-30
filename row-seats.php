<?php

if (!session_id())
{

    session_start();
	
	
}

/*

Plugin Name: Row Seats Core

Plugin URI: http://www.rowseatsplugin.com

Description: Booking seats is easier with Row Seats plugin.This is a new solution to the increasing request to sell seats.It features shopping cart features, calendar backend function, csv file upload of your seat details. It also handles special seating such as handicap accessability. Just place the shortcode in a page or post and sell your show.

Version: 2.42

Author: GC Development Team

Author URI: http://www.rowseatsplugin.com

*/

define('RSTPLN_URL', plugins_url('/', __FILE__));

define('RSTPLN_COKURL', plugins_url('/jquery-cookie/', __FILE__));

define('RSTPLN_IDLKURL', plugins_url('/idle-counter/', __FILE__));

define('RSTPLN_CALURL', plugins_url('/weekcal/', __FILE__));

define('RSTPLN_FULCKURL', plugins_url('/fullcalendar/', __FILE__));

define('RSTPLN_CKURL', plugins_url('/checkout/', __FILE__));

define('RSTPLN_JALURL', plugins_url('/jalerts/', __FILE__));

define('RSTPLN_JS', plugins_url('/js/', __FILE__));

define('RSTPLN_DIR', dirname(__FILE__));

define('RSTPLN_VERSION', '1.0');

define('RSTPLN_OPTIONS', 'rst_options');

define('RSTFEE_OPTIONS', 'rst_fee_options');

define('RSTLANGUAGES_OPTIONS', 'rst_languages_options');

define('RSTPLN_KEYINFO', 'rst_keyinfo');

define('RSTPLN_PPOPTIONS', 'rst_paypal_options');

define('RSTPLN_NAME', 'Row Seats');

define('RSTPLN_PRIFIX', 'rst_');

define('RSTPLN_CSSURL', plugins_url('/css/', __FILE__));

define('RSTPLN_JSURL', plugins_url('/js/', __FILE__));

if (is_ssl())

    $scheme = 'https';

else

    $scheme = 'http';

define('RSTAJAXURL', home_url('/wp-admin/admin-ajax.php', $scheme));

$wpdb->rst_shows = 'rst_shows';

$wpdb->rst_seats = 'rst_seats';

$wpdb->rst_customer_session = 'rst_customer_session';

$wpdb->rst_bookings = 'rst_bookings';

$wpdb->rst_booking_seats_relation = 'rst_booking_seats_relation';

require_once('row-seats-functions.php');

require_once('sql-scripts.php');

add_shortcode('showseats', 'gettheseatchart');
add_shortcode('rowseatthankspage', 'displaybookingdetails');
add_shortcode('listallevents', 'listalleventsfunction');
add_action("widgets_init", 'widgets_init');



/*

 * wp_ajax_(action) - Authenticated actions

 * wp_ajax_nopriv_(action) - Non-admin actions

 */

add_action('admin_init',                            'registerOptions');
//add_action('init',                                   'wp_row_seats_signup_call');

add_action('admin_init',                            'rst_transaction_details');

add_action('plugins_loaded', 'wp_row_seats_signup_call');

add_action('admin_menu',                            'adminMenu');

add_action('wp_enqueue_scripts',                    'rst_scripts_method'); // For use on the Front end (ie. Theme)

add_action('wp_ajax_nopriv_releasenow',             'rst_ajax_callback');

add_action('wp_ajax_releasenow',                    'rst_ajax_callback');

add_action('wp_ajax_nopriv_refresh',                'rst_ajax_callback');

add_action('wp_ajax_refresh',                       'rst_ajax_callback');

add_action('wp_ajax_nopriv_booking',                'rst_ajax_callback');

add_action('wp_ajax_booking',                       'rst_ajax_callback');

add_action('wp_ajax_save',                          'rst_ajax_callback');

add_action('wp_ajax_update',                        'rst_ajax_callback');

add_action('wp_ajax_delete',                        'rst_ajax_callback');

add_action('wp_ajax_get_events',                    'rst_ajax_callback');

add_action('wp_ajax_savebooking',                   'rst_ajax_callback');

add_action('wp_ajax_nopriv_savebooking',            'rst_ajax_callback');

add_action('wp_ajax_nopriv_offlinereg',             'rst_offline_registration');

add_action('wp_ajax_offlinereg',                    'rst_offline_registration');

add_action('wp_ajax_deleteall',                     'rst_ajax_callback');

add_action('wp_ajax_nopriv_deleteall',              'rst_ajax_callback');

add_action('wp_ajax_deletebooking',                 'rst_ajax_callback');

add_action('wp_ajax_nopriv_deletebooking',          'rst_ajax_callback');

add_action('wp_ajax_releasecurrentcart',            'rst_ajax_callback');

add_action('wp_ajax_nopriv_releasecurrentcart',     'rst_ajax_callback');

//add_action('wp_row_seats-signup',     'wp_row_seats_signup_call');

?>