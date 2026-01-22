<?php
/*
 * Translation related features
 * see README.md for the needed constants
 */

/*
 * Add Editor or textfield for every extra language in frontend form
 * You need to add the action to your frontend form template like this:
 * ```
 * do_action( 'fx_em_custom_editors', $EM_Event );
 * ```
 */

$fx_em_options = get_option( 'fx_em_options' );
$fx_em_base_lang = $fx_em_options['base_lang'];
$fx_em_langs = explode(',', $fx_em_options['langs']);

add_action( 'fx_em_custom_editors', 'fx_em_content_editors', 10, 1 );
function fx_em_content_editors( $EM_Event ): void
{
	global $fx_em_langs;
	$result = '';
	foreach( $fx_em_langs as $lang ) {
		$result .= '<h4 style="margin-bottom:0 !important">' . Locale::getDisplayLanguage( $lang, get_locale() ) . ':</h4>';
		$result .= fx_custom_event_editor( $lang, $EM_Event );
	}
	echo $result ?? '';
}

/*
 * Add Editor or textfield for every extra language to backend form
 */
add_action( 'add_meta_boxes', 'fx_em_meta_boxes' );
function fx_em_meta_boxes(): void
{
	global $fx_em_langs;
	foreach ( $fx_em_langs as $lang ) {
		$langName = Locale::getDisplayLanguage( $lang, get_locale() );
		add_meta_box( "em-event-content-$lang", $langName, 'fx_em_event_editor_admin', EM_POST_TYPE_EVENT, 'normal','high', ['lang' => $lang]);
		add_meta_box( "em-event-content-$lang", $langName, 'fx_em_event_editor_admin', 'event-recurring', 'normal','high', ['lang' => $lang]);
	}
}
function fx_em_event_editor_admin( $post, $data ): void
{
    $lang = $data['args']['lang'];
    global $EM_Event;
    echo fx_custom_event_editor( $lang, $EM_Event, true );
}

/*
 * returns the HTML for the editor or textarea
 * used for frontend and backend form
 */
function fx_custom_event_editor( $lang, $EM_Event, $admin = false ): string
{
	$result = '';
	$content = fx_em_get_content_translation( $EM_Event->event_id, $lang );
	if( $admin || ( get_option('dbem_events_form_editor') && function_exists('wp_editor') ) ) {
		ob_start();
$e_config = ['textarea_name' => 'content-' . $lang, 'textarea_rows' => 10];
if ( ! $admin ) {
$e_config['media_buttons'] = false;
$e_config['quicktags'] = false;
}
		wp_editor( $content, 'em-editor-content-' . $lang, $e_config);
		$result .= ob_get_clean();
	} else {
		$result .= "<textarea name=\"content-$lang\" rows=\"10\" style=\"width:100%\">";
		$result .= $content;
		$result .= '</textarea><br >';
		$result .= esc_html( 'Details about the event.', 'events-manager' );
		$result .= esc_html( 'HTML allowed.', 'events-manager' );
	}
	return $result ?? '';
}

/*
 * returns the translated description of the event with ID $event_id
 * in the given language $lang
 * adds p tags if $autop is true
 */
function fx_em_get_content_translation( $event_id, $lang, bool $autop = true ): string
{
	global $wpdb;
    $sql = $wpdb->prepare( "SELECT meta_value FROM " . EM_META_TABLE . " WHERE object_id=%s AND meta_key='content-$lang'", $event_id );
    $result = wp_kses_post( $wpdb->get_col($sql, 0)[0] ?? '' );
	if ( !empty($result) && $autop ) {
		$result = wpautop( $result );
	}
    return $result;
}

/*
 * Save extra languges descriptions
 */
add_filter( 'em_event_save', 'fx_em_translations_save', 1, 2 );
function fx_em_translations_save( $result, $EM_Event )
{
	global $wpdb, $fx_em_langs, $fx_em_base_lang;
	
	if( ! $EM_Event->event_id ) {
		// do nothing
		return $result;
	}
	
	// do nothing if content is empty
	if (empty($EM_Event->post_content)) {
		return $result;
	}
	
	$content_to_add = [];
	foreach( $fx_em_langs as $lang ) {
		
		if( isset($_POST['content-' . $lang]) ){
		    //First delete any old saves
            $wpdb->query("DELETE FROM ".EM_META_TABLE." WHERE object_id='{$EM_Event->event_id}' AND meta_key='content-$lang'");
			
			// Auto-translate if empty
			// todo: check if API active
			if (empty($_POST['content-' . $lang])) {
				$t_content = fx_em_translate( $EM_Event->post_content, $fx_em_base_lang, $lang );
			} else {
				$allowed_html = [
    'a' => [
        'id' => true,
        'href'  => true,
        'title' => true,
    ],
    'strong' => [],
];
				$t_content = wp_kses_post($_POST['content-' . $lang], $allowed_html);
			}
			
			$t_content = esc_sql( wp_encode_emoji( $t_content ) );
			$content_to_add[] = "({$EM_Event->event_id}, 'content-$lang', '$t_content')";
           //$EM_Event->description_translations[$lang] = $t_content;
    	}
	}
	if ( $content_to_add !== [] ) {
		$wpdb->query("INSERT INTO ".EM_META_TABLE." (object_id, meta_key, meta_value) VALUES".implode(',',$content_to_add));
	}
    return $result;
}

/*
 * Override notes placeholder with translations
 */
add_filter( 'em_event_output_placeholder', 'my_em_notes_ph', 1, 3 );
function my_em_notes_ph( $replace, $EM_Event, $result )
{
	global $fx_em_base_lang, $fx_em_langs;
	
	$desc = '';
    if( $result == '#_EVENTNOTES' ){
		
			//var_dump($replace);
		if (strpos($replace, 'wp-block') !== false) {
			return $replace;
		}
	
		$lang = substr( get_locale(), 0, 2);
		
		if ($lang !== $fx_em_base_lang) {
		
		    $desc = fx_em_get_content_translation( $EM_Event->event_id, $lang );
			
			if (!empty($desc)) {
				return $desc;
			}
		}
		
		if ( empty($replace) ) {
			foreach( $fx_em_langs as $lang ) {
				$desc = fx_em_get_content_translation( $EM_Event->event_id, $lang );
				
				if ( ! empty( $desc ) ) {
//To do: auto-translate and save ?? and return
					return $desc;
				}
			}
	    }
    }
    return $replace;
}

/*
 * Override excerpt placeholder with translations
 */
add_filter( 'em_event_output_placeholder', 'my_em_excerpt_ph', 1, 3 );
function my_em_excerpt_ph( $replace, $EM_Event, $result )
{
	global $fx_em_base_lang, $fx_em_langs;
	
    if( substr( $result, 0, 14 ) == '#_EVENTEXCERPT' ){
		
	$rest = substr( $result, 14 );
	if ( $rest == '' || $rest == 'CUT') {
		return $replace; // we do not translate the excerpt field
	}
	$rest = trim( $rest, 'CUT{ }' ); // treat #_EVENTEXCERPTCUT same as #_EVENTEXCERPT, not ideal though

	$args = explode( ',', $rest );
		
		$words_limit = isset( $args[0] ) ? absint( $args[0] ) : 55;
		$more = isset( $args[1] ) ? $args[1] : ' [...]';
		
		$lang = substr( get_locale(), 0, 2);
		
		if ($lang !== $fx_em_base_lang) {
		    $exc = wp_trim_words( fx_em_get_content_translation( $EM_Event->event_id, $lang, false ), $words_limit, $more);
			
			if (!empty($exc)) {
				return $exc;
			}
		}
		
// Look for any translation to prevent empty value
		if ( empty($replace) ) {
			foreach( $fx_em_langs as $lang ) {
				$exc = wp_trim_words( fx_em_get_content_translation( $EM_Event->event_id, $lang, false ), $words_limit, $more);
//To do: auto-translate and save ?? and return
				if (!empty($exc)) {
					return $exc;
				}
			}
	    }
    }
    return $replace;
}
