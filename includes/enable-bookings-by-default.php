<?php

/**
 * Enable bookings by default for every event if current user can manage bookings.
 */
add_action('em_event', 'enable_booking', 10, 3);
function enable_booking($EM_Event, $id, $search_by) {
	// Only when ID is false (new event).
	if (empty($EM_Event->event_id) && current_user_can('manage_bookings')) {
		$EM_Event->event_rsvp = 1;
	}
}
