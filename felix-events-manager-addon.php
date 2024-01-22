<?php
/**
 * Plugin Name: Felix' Events Manager Addon
 * Description: Additional features: use category image as fallback, allow URL parameters for search
 * Version: 0.1
 * Author: Felix Hahn
 * Author URI: https://hahn-felix.de
 **/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 * The following constansts can be overwritten in wp-config.php to disable specific features of this plugin safely.
 */
// boolean FX_EM_IMAGE_FALLBACK Image Fallback: User the category image as fallback if an individual event has no image.
const FX_EM_IMAGE_FALLBACK = true;

// boolean FX_EM_BOOKINGS_DEFAULT Enable bookings by default for every new event if the user can manage bookings.
const FX_EM_BOOKINGS_DEFAULT = true;

// boolean FX_EM_ALLOW_GET_SEARCH Allow to search via some URL parameters.
const FX_EM_ALLOW_GET_SEARCH = true;

if ( FX_EM_IMAGE_FALLBACK ) {
	require_once __DIR__ . '/includes/image-fallback.php';
}
if ( FX_EM_BOOKINGS_DEFAULT ) {
	require_once __DIR__ . '/includes/enable-bookings-by-default.php';
}
if ( FX_EM_ALLOW_GET_SEARCH ) {
	require_once __DIR__ . '/includes/add-get-params-to-search.php';
}
