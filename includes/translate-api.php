<?php

/*
 * Translate text via the custom API
 */
function fx_em_translate( string $text, string $source_lang, string $target_lang ): ?string
{
	if ($target_lang == 'en') {
		$target_lang = 'en-GB';
	}
	
$allowed_html = [
    'a' => [
        'id' => true,
        'href'  => true,
        'title' => true,
    ],
    'strong' => [],
];
	
	$fx_em_options = get_option( 'fx_em_options' );
    $data = [
'fx-auth-key' => $fx_em_options['api_key'],
'text'=> wp_kses($text, $allowed_html),
	    'source_lang' => $source_lang,
	    'target_lang' => $target_lang
    ];
    $args = ['body' => $data];

    $response = wp_remote_post( $fx_em_options['api_url'], $args );
$result = wp_remote_retrieve_body( $response );

return wp_kses($result, $allowed_html);
}

/*
 * 
 */
add_action( 'em_event_save_pre', 'fx_em_save_pre_t', 10, 1 );
function fx_em_save_pre_t( $EM_Event )
{
	if( empty($EM_SAVING_EVENT) ) return; //never proceed with this if NOT using EM_Event::save()- handled by fx_em_post_update, see below
	
    if (empty($EM_Event->post_content) ) {
		$translated = fx_em_find_translate();
		if ( ! empty ( $translated ) ) {
			$EM_Event->post_content = $translated;
			return $EM_Event;
		}
	}
    return $EM_Event;
}

// Handle updates on admin site ($EM_Event->save() is not executed so we have to hook into WP)
add_action( 'wp_insert_post_data', 'fx_em_post_update', 10, 4 );
function fx_em_post_update( array $data, array $postarr, array $unsanitized_postarr, bool $update )
{
	if ( ! empty($EM_SAVING_EVENT) ) return; //never proceed with this if using EM_Event::save()- handled by fx_em_save_pre_t, see above
	
	if ( ! $update ) {
		return $data;
	}
	// Only set for post_type = event!
	if ( EM_POST_TYPE_EVENT !== $data['post_type'] ) {
		return $data;
	}
	
	if (empty($data['post_content'])) {
		$translated = fx_em_find_translate();
		if ( ! empty ( $translated ) ) {
			$data['post_content'] = $translated;
			return $data;
		}
	}
	return $data;
}

function fx_em_find_translate(): string
{
	global $fx_em_langs;
	$fx_em_options = get_option( 'fx_em_options' );
	foreach ( $fx_em_langs as $lang ) {
		if ( ! empty($_POST['content-' . $lang] ) ) {
			return fx_em_translate( $_POST['content-' . $lang], $lang, $fx_em_options['base_lang'] );
		}
	}
	return '';
}