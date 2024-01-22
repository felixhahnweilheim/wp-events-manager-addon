<?php

/**
 * Add GET parameters to SQL condition if safe
 * currently supported: category by tag_id, scope, country by 2 letter uppercase code
*/
add_filter( 'em_events_get_default_search', 'add_get_params', 10, 3);
function add_get_params( $args, $array, $defaults ):array
{
	// Category by term_id
	if (isset($_GET['category']) && is_numeric($_GET['category'])) {
	    $args['category'] = $_GET['category'];
	}
	// Scope
	if (isset( $_GET['scope']) && in_array($_GET['scope'], array_keys(em_get_scopes()))) {
		$args['scope'] = $GET['scope'];
	}
	// Country by to letter code (uppercase !)
	if (isset($_GET['country']) && (strlen($_GET['country']) == 2) && ctype_upper($_GET['country'])) {
		$args['country'] = $_GET['country'];
	}
	return $args;
}
