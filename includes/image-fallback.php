<?php

/*
 *  Use category image as fallback
 */
add_filter('em_event_output_placeholder','my_em_image_placeholders',1,3);
function my_em_image_placeholders($replace, $EM_Event, $result){
    if( substr($result,0,12) == '#_EVENTIMAGE' ){
		if($EM_Event->get_image_url() == ''){
			if( get_option('dbem_categories_enabled') ){
				foreach( $EM_Event->get_categories() as $EM_Category ){
					$count = 1;
					$catReplace = str_replace('#_EVENT', '#_CATEGORY', $result, $count);
					$output = $EM_Category->output($catReplace);
					
					if(!empty($output)) {
						return $output;
					}
				}
			}
		}
	}
    return $replace;
}
/*
 * Correct conditional placeholders {has_image} and {no_image}
 */
add_action('em_event_output_show_condition', 'my_em_image_event_output_show_condition', 1, 4);
function my_em_image_event_output_show_condition($show, $condition, $full_match, $EM_Event){
    if( !$show && $condition == 'has_image' && $EM_Event->output('#_EVENTIMAGEURL') != ''){
        return true;
    }
    if( $show && $condition == 'no_image' && $EM_Event->output('#_EVENTIMAGEURL') != ''){
        return false;
    }
    return $show;
}
