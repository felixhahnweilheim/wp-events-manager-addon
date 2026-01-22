<?php

/*
 * Load options and additional features if enabled
 */
$fx_em_options = get_option( 'fx_em_options' );
foreach ( fx_em_get_available_options() as $id => $option ) {
	if ( isset( $fx_em_options[$id] ) && isset( $option['php_file'] ) ) require_once __DIR__ . $option['php_file'];
}
if ( ! empty( $fx_em_options['base_lang'] ) && ! empty( $fx_em_options[ 'langs' ] ) ) require_once __DIR__ . '/translations.php';
if ( ! empty( $fx_em_options[ 'api_url' ] ) && ! empty( $fx_em_options['api_key'] ) ) require_once __DIR__ . '/translate-api.php';

if ( isset( $fx_em_options['use-gutenberg'] ) ) {
define('EM_GUTENBERG', true);
}

function fx_em_get_available_options()
{
	return [
'use-gutenberg' => [
'type' => 'boolean',
'default' => 'false',
'label' => __( 'Use the Gutenberg Block Editor', 'felix-events-manager-addon' ),
'description' => 'Used temporarily to create fully styled event pages. Translations?'
],
		'add-schema-to-single-event' => [
			'type' => 'boolean',
			'default' => false,
			'label' => __( 'Schema.org', 'felix-events-manager-addon' ),
			'description' => __( 'Add Schema.org data to events to improve SEO.', 'felix-events-manager-addon' ),
			'php_file' => '/add-schema-to-single-event.php'
		],
		'image-fallback' => [
			'type' => 'boolean',
			'default' => false,
			'label' => __( 'Image Fallback', 'felix-events-manager-addon' ),
			'description' => __( 'Use category image as fallback.', 'felix-events-manager-addon' ),
			'php_file' => '/image-fallback.php'
		],
		'add-get-params-to-search' => [
			'type' => 'boolean',
			'default' => false,
			'label' => __( 'URL Search', 'felix-events-manager-addon' ),
			'description' => __( 'Allow GET parameters in the events search.', 'felix-events-manager-addon' ),
			'php_file' => '/add-get-params-to-search.php'
		],
		'enable-bookings-by-default' => [
			'type' => 'boolean',
			'default' => false,
			'label' => __( 'Bookings Default', 'felix-events-manager-addon' ),
			'description' => __( 'Enable bookings by default for new events.', 'felix-events-manager-addon' ),
			'php_file' => '/enable-bookings-by-default.php'
		],
		'base_lang' => [
			'type' => 'string',
			'default' => 'en',
			'label' => __( 'Base Language', 'felix-events-manager-addon' ),
			'description' => __( 'short language code', 'felix-events-manager-addon')
		],
		'langs' => [
			'type' => 'string',
			'label' => __( 'Translation Languages', 'felix-events-manager-addon' ),
			'description' => __( 'Comma seperated list of 2 letter language codes.', 'felix-events-manager-addon' )
		],
		'api_url' => [
			'type' => 'string',
			'label' => __( 'Translation API', 'felix-events-manager-addon' ),
			'description' => __( 'Full URL to translation endpoint.', 'felix-events-manager-addon' )
		],
		'api_key' => [
			'type' => 'string',
			'label' => __( 'API Key', 'felix-events-manager-addon' ),
			'description' => ''
		],
	];
}


/*
 * Add Admin page
 * as submenu of the Events Manager
 */
add_action( 'admin_menu', 'fx_em_submenu', 20 );
function fx_em_submenu (): void
{
	$plugin_page = add_submenu_page(
		'edit.php?post_type='.EM_POST_TYPE_EVENT,
		'Felix\' Addon', // page title
		'Felix\' Addon', // menu title
		'manage_options', // capability
		'fx_em_admin', // page slug
		'fx_em_admin_page' // callback
	);
	/*add_action( 'admin_print_styles-'. $plugin_page, 'em_admin_load_styles' );
		add_action( 'admin_head-'. $plugin_page, 'em_admin_general_style' );*/
}
function fx_em_admin_page(): void
{
	// check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) return;

	// add error/update messages ??
	// check if the user have submitted the settings
	// WordPress will add the "settings-updated" $_GET parameter to the url
	if ( isset( $_GET['settings-updated'] ) ) {
		// add settings saved message with the class of "updated"
		add_settings_error( 'fx_em_schema', 'fx_em_message', __( 'Settings Saved', 'fx_em_addon' ), 'updated' );
	}
	// show error/update messages
	settings_errors( 'fx_em_schema' );
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<form action="options.php" method="post">
		<?php
	// output security fields for the registered setting "fx_em_options"
	settings_fields( 'fx_em_options' );
	// output setting sections and their fields
	// (sections are registered for "wporg", each field is registered to a specific section)
	do_settings_sections( 'fx_em_admin' );
	// output save settings button
	submit_button( 'Save Settings' );
		?>
	</form>
</div>
<?php
}
/**
	 * Register our wporg_settings_init to the admin_init action hook.
	 */
add_action( 'admin_init', 'fx_em_settings_init' );
function fx_em_settings_init(): void
{
	//foreach ($options as $id => $option) {
	// Register a new setting
	register_setting(
		'fx_em_options', // option group
		"fx_em_options", // option name
	);
	//}

	// Register a new section in the "wporg" page.
	add_settings_section(
		'fx_em_main', // section slug
		__( 'Features', 'fx_em_addon' ), // section title
		'fx_em_admin_section_cb', // callback
		'fx_em_admin' // page slug
	);

	foreach ( fx_em_get_available_options() as $id => $option) {
		// Register a new field in the "fx_em_main" section, inside the "fx_em_admin" page.
		$inputType = ($option['type'] == 'boolean') ? 'checkbox' : 'text';
		add_settings_field(
			"fx_em_$id", // slug name - As of WP 4.6 this value is used only internally.
			// Use $args' label_for to populate the id inside the callback.
			$option['label'], // title
			'fx_em_field_cb', // callback
			'fx_em_admin', // page slug
			'fx_em_main', // section slug
			[
				'inputType' => $inputType,
				'label_for' => 'fx_em_field_schema',
				'description' => $option['description'],
				'option_group' => 'fx_em_options',
				'name' => $id,
				'label' => $option['label']
			]
		);
	}
}

/**
	 * Developers section callback function.
	 *
	 * @param array $args  The settings array, defining title, id, callback.
	 */
function fx_em_admin_section_cb( $args ) {}

/**
	 * field callback function.
	 *
	 * WordPress has magic interaction with the following keys: label_for, class.
	 * - the "label_for" key value is used for the "for" attribute of the <label>.
	 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
	 * Note: you can add custom key value pairs to be used inside your callbacks.
	 *
	 * @param array $args
	 */

function fx_em_field_cb( $args )
{
	$options = get_option($args['option_group']);
	$value   = ( !isset( $options[$args['name']] ) ) 
		? null : $options[$args['name']];
	$checked = ($value && $args['inputType'] == 'checkbox') ? ' checked="checked" ' : '';
	$valueHtml = ($value && $args['inputType'] == 'text') ? ' value="' . $value . '" ' : '';
	
	// Could use ob_start.
	$html  = '';
	$html .= '<input id="' . esc_attr( $args['name'] ) . '" 
			name="' . esc_attr( $args['option_group'] . '['.$args['name'].']') .'" 
			type="' . $args['inputType'] . '" ' . $checked . $valueHtml . '/>';
	$html .= '<span class="wndspan">' . esc_html( $args['description'] ) .'</span>';
	//$html .= '<b class="wntip" data-title="'. esc_attr( $args['tip'] ) .'"> ? </b>';

	echo $html;
}
