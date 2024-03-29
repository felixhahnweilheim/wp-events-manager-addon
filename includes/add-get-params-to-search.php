<?php
/**
 * Add GET parameters to search arguments if set and safe.
 * - category by tag_id
 * - scope
 * - country by 2 letter uppercase code
 * 
 * Note 1: The search form always recognizes the category by tag_id and the country by 2 letter uppercase code. That means it shows those as pre-selected in the advanced search however the events are not filtered accordingly.
 * We just make this work completely.
 * 
 * Note 2: The scope won't be visible as selected in the search form.
 * 
 * @todo Remove URL parameters when they have been changed, via JS.
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
	// Country by to letter uppercase code
	if (isset($_GET['country']) && (strlen($_GET['country']) == 2) && ctype_upper($_GET['country'])) {
		$args['country'] = $_GET['country'];
	}
	return $args;
}
