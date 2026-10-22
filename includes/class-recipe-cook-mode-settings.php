<?php
/**
 * Plugin settings.
 *
 * @package Recipe_Cook_Mode
 */

defined( 'ABSPATH' ) || exit;

/**
 * Reads and validates the plugin settings.
 */
final class Recipe_Cook_Mode_Settings {

	const OPTION    = 'recipe_cook_mode_settings';
	const POSITIONS = array( 'top', 'bottom' );

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'label'           => __( 'Cook Mode', 'recipe-cook-mode' ),
			'description'     => __( 'Keep your screen awake while you cook', 'recipe-cook-mode' ),
			'color'           => '#2271b1',
			'wprm_enabled'    => true,
			'wprm_position'   => 'top',
			'auto_post_types' => array(),
			'auto_categories' => array(),
			'auto_position'   => 'top',
		);
	}

	/**
	 * Current settings merged with defaults.
	 *
	 * @return array
	 */
	public static function get() {
		$settings = wp_parse_args( (array) get_option( self::OPTION, array() ), self::defaults() );
		if ( '' === trim( $settings['label'] ) ) {
			$settings['label'] = self::defaults()['label'];
		}
		return $settings;
	}

	/**
	 * Whether WP Recipe Maker is active.
	 *
	 * @return bool
	 */
	public static function wprm_active() {
		return class_exists( 'WP_Recipe_Maker' );
	}

	/**
	 * Sanitizes the settings form.
	 *
	 * @param mixed $input Submitted values.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$defaults = self::defaults();
		$output   = $defaults;

		$output['label']       = isset( $input['label'] ) ? sanitize_text_field( $input['label'] ) : $defaults['label'];
		$output['description'] = isset( $input['description'] ) ? sanitize_text_field( $input['description'] ) : $defaults['description'];

		$color           = isset( $input['color'] ) ? sanitize_hex_color( $input['color'] ) : '';
		$output['color'] = $color ? $color : $defaults['color'];

		$output['wprm_enabled'] = ! empty( $input['wprm_enabled'] );
		foreach ( array( 'wprm_position', 'auto_position' ) as $key ) {
			$position       = isset( $input[ $key ] ) ? (string) $input[ $key ] : 'top';
			$output[ $key ] = in_array( $position, self::POSITIONS, true ) ? $position : 'top';
		}

		$public_types              = get_post_types( array( 'public' => true ) );
		$types                     = isset( $input['auto_post_types'] ) ? array_map( 'sanitize_key', (array) $input['auto_post_types'] ) : array();
		$output['auto_post_types'] = array_values( array_intersect( $types, $public_types ) );

		$categories                = isset( $input['auto_categories'] ) ? array_map( 'absint', (array) $input['auto_categories'] ) : array();
		$output['auto_categories'] = array_values( array_filter( $categories ) );

		return $output;
	}
}
