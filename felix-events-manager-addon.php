<?php
/**
 * Plugin Name: Felix' Events Manager Addon
 * Description: Additional features: use category image as fallback, allow URL parameters for search, enable bookings by default for new events (optional)
 * Version: 1.0.0
 * Text Domain: felix-events-manager-addon
 * Domain Path: /languages
 * Author: Felix Hahn
 * Author URI: https://felixwebdesign.de
 **/
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Admin Settings
 * also loads additional PHP files depending on settings
 */
require_once __DIR__ . "/includes/admin.php";

/*
 * load translations
 */
add_action( 'init', 'fx_em_load_textdomain' );
function fx_em_load_textdomain() {
	load_plugin_textdomain( 'felix-events-manager-addon', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}

/*
 * Add Events and Locations to WordPress search
 */
add_filter( 'pre_get_posts', 'include_custom_post_in_search' );
function include_custom_post_in_search( $query )
{
    if ( $query->is_search ) $query->set( 'post_type', ['post', 'page', 'event', 'location'] );
    return $query;
}

/*
 * Placeholder #_DUPLICATELINK
 * Link to duplicate the event
 */
add_filter( 'em_event_output_placeholder', 'my_em_duplicate_ph', 1, 3 );
function my_em_duplicate_ph( $replace, $EM_Event, $result )
{
    if ( $result == '#_DUPLICATELINK' ) return '<a href="' . $EM_Event->duplicate_url() . '">' . __( 'Duplicate Event', 'felix-events-manager-addon' ) . '</a>';
    
    return $replace;
}

function fx_em_is_gutenberg_styled( string $content) {
	return strpos($content, '<- wp:') !== false;
}

/*
 * Allow Emojis in event description
 */
add_action( 'em_event_save_pre', 'fx_em_save_pre', 10, 1 );
function fx_em_save_pre( $EM_Event )
{
    $EM_Event->post_content = wp_encode_emoji($EM_Event->post_content);
    return $EM_Event;
}

/*
 * Allow Emojis in booking comment
 */
add_action( 'em_booking_save_pre', 'fx_em_save_b_pre', 10, 1 );
function fx_em_save_b_pre( $EM_Booking )
{
    $EM_Booking->booking_comment = wp_encode_emoji($EM_Booking->booking_comment);
    return $EM_Booking;
}
