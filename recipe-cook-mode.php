<?php
/**
 * Plugin Name:       Recipe Cook Mode
 * Plugin URI:        https://wordpress.org/plugins/recipe-cook-mode/
 * Description:       Adds a Cook Mode toggle that keeps visitors' screens awake while they follow a recipe. Works with WP Recipe Maker cards, any post type, a shortcode, or a block.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Ali Nawaz
 * Author URI:        https://bealinawaz.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       recipe-cook-mode
 *
 * @package Recipe_Cook_Mode
 */

defined( 'ABSPATH' ) || exit;

define( 'RECIPE_COOK_MODE_VERSION', '1.0.0' );
define( 'RECIPE_COOK_MODE_FILE', __FILE__ );
define( 'RECIPE_COOK_MODE_URL', plugin_dir_url( __FILE__ ) );

require_once __DIR__ . '/includes/class-recipe-cook-mode-settings.php';
require_once __DIR__ . '/includes/class-recipe-cook-mode.php';

Recipe_Cook_Mode::init();

if ( is_admin() ) {
	require_once __DIR__ . '/includes/class-recipe-cook-mode-admin.php';
	Recipe_Cook_Mode_Admin::init();
}
