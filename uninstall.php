<?php
/**
 * Removes the plugin's settings.
 *
 * @package Recipe_Cook_Mode
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'recipe_cook_mode_settings' );
