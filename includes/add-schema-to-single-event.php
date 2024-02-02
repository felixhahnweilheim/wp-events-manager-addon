<?php

/*
 * Add JSON-LD formatted Schema.org data to the head section of single event pages
 */
add_action( 'wp_head', 'fx_em_schema_single' );
function fx_em_schema_single() {

    if ( get_post_type() === 'event' ) {

        $FX_EM_Schema = new FX_EM_Schema( get_the_id() );
        echo "<script type='application/ld+json'>{$FX_EM_Schema->get_json_string()}</script>\n";
    }
}

class FX_EM_Schema {
    
    /**
     * @var int
     */
    public int $post_id;
    
    /**
	 * @var EM_Event
	 */
    public $EM_Event;
    
	/**
	 * @var EM_Location (physical)
	 */
    public $EM_Location;
    
    public function __construct( int $post_id ) {
        
        $this->post_id = $post_id;
        $this->EM_Event = em_get_event( $this->post_id, 'post_id' );
        $this->EM_Location = em_get_location( $this->EM_Event->location_id );
    }
    
    /**
     * Get the Schema in JSON-LD format
     */
    public function get_json_string(): string {
        
        $schema = $this->getSchema();
        $schema = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        return $schema;
    }
    
    /*
     * Get the Schema as array
     */
    private function getSchema(): array {
        
        $schema['@context'] = 'https://schema.org/';
        $schema['@type'] = 'Event';
        
        // Event Name
        $schema['name'] = $this->EM_Event->name;
        
		// Event Description
		if ( !empty($this->EM_Event->post_excerpt) ) {
			$schema['description'] = wp_strip_all_tags($this->EM_Event->post_excerpt,true);
		} elseif (!empty($this->EM_Event->post_content)) {
			$schema['description']  = substr(wp_strip_all_tags($this->EM_Event->post_content, true),0,50);
		}

        // Start Date and Time
        $EM_DateTime = $this->EM_Event->start();
		$schema['startDate'] = $EM_DateTime->format('Y-m-d\TH:iO');
        
        // End Date and Time
        $EM_DateTime = $this->EM_Event->end();
        $schema['endDate'] = $EM_DateTime->format('Y-m-d\TH:iO');

		// Image
		$imageUrl = $this->EM_Event->output('#_EVENTIMAGEURL');
		if ( !empty($imageUrl) ) {
			$schema['image'] = $imageUrl;
		}
		
        // Status (scheduled or cancelled) if feature is enabled
        if ( get_option('dbem_event_status_enabled') ) {
            if ( $this->EM_Event->get_active_status() == __('Cancelled', 'events-manager'))  {
                $schema['eventStatus'] = 'https://schema.org/EventCancelled';
            } else {
                $schema['eventStatus'] = 'https://schema.org/EventScheduled';
            }
        }
		
        if ( !isset( $this->EM_Event->event_location_type ) ) {
        	// Physical Event
        	$schema['eventAttendanceMode'] = 'https://schema.org/OfflineEventAttendanceMode';
        	$schema['location'] = $this->get_location();
        } elseif ($this->EM_Event->event_location->data->url !== '') {
			// Online Event
			$schema['eventAttendanceMode'] = 'https://schema.org/OnlineEventAttendanceMode';
			$schema['location'] = $this->get_virtual_location($this->EM_Event->event_location);
		}
        
        return $schema;
    }
    
    /*
     * Get Location Schema as array
     */
    private function get_location(): array {
        
        $location['@type'] = 'Place';
        $location['name'] = $this->EM_Location->location_name;

        // Address
        $location['address'] = (array) $this->get_address();
            
        return $location;
    }

    /*
     * Get Address Schema as array
     */
    private function get_address(): array {

        $address['@type'] = 'PostalAddress';
        $address['addressCountry'] = $this->EM_Location->location_country;
        $address['addressLocality'] = $this->EM_Location->location_town;
        // addressRegion: use State, region as fallback
        if ( !empty( $this->EM_Location->location_state ) ) {
            $address['addressRegion'] = $this->EM_Location->location_state;
        } elseif ( !empty( $this->EM_Location->location_region ) ) {
            $address['addressRegion'] = $this->EM_Location->location_region;
        }
        $address['postalCode'] = $this->EM_Location->location_postcode;
        $address['streetAddress'] = $this->EM_Location->location_address;
        
        return $address;
    }
	
	/*
	 * Get Virtual Location
	 */
	private function get_virtual_location($EM_Event_Location): array {
		
		$location['@type'] = 'VirtualLocation';
		$location['url'] = $EM_Event_Location->data['url'];
		
		return $location;
	}
}
