<?php

add_action( 'wp_head', 'fx_em_schema_single' );
function fx_em_schema_single() {

    if ( get_post_type() === 'event' ) {
        
		$EM_Event = em_get_event(get_the_ID());
        
        $schema['@context'] = 'https::schema.org';
        $schema['@type'] = 'Event';
		
		$schema['name'] = $EM_Event->name;
        
        // @todo support online events
        $schema['location'] = get_location( $EM_Event->location_id );
        
        $schema = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        echo "<script type='application/ld+json'>{$schema}</script>\n";
    }
}

function get_location( $location_id ): array {

    $EM_Location = em_get_location( $location_id );
	//$location = (array) $EM_Location;
    
    $location['@type'] = 'Place';
    $location['name'] = $EM_Location->location_name;
        
    $location['address'] = get_address($EM_Location);
        
    return $location;
}

function get_address( $EM_Location ): array {

    $address['@type'] = 'PostalAddress';
    
    return $address;
}
