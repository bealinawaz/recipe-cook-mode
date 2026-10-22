<?php
/**
 * Settings screen.
 *
 * @package Recipe_Cook_Mode
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings page under Settings → Recipe Cook Mode.
 */
final class Recipe_Cook_Mode_Admin {

	const PAGE = 'recipe-cook-mode';

	/**
	 * Registers admin hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( RECIPE_COOK_MODE_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Adds the page under Settings.
	 */
	public static function add_page() {
		add_options_page( __( 'Recipe Cook Mode', 'recipe-cook-mode' ), __( 'Recipe Cook Mode', 'recipe-cook-mode' ), 'manage_options', self::PAGE, array( __CLASS__, 'render_page' ) );
	}

	/**
	 * Adds a Settings link on the Plugins screen.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public static function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=' . self::PAGE ) ) . '">' . esc_html__( 'Settings', 'recipe-cook-mode' ) . '</a>' );
		return $links;
	}

	/**
	 * Loads the color picker on the settings page only.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue( $hook ) {
		if ( 'settings_page_' . self::PAGE !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_add_inline_script( 'wp-color-picker', 'jQuery( function ( $ ) { $( ".recipe-cook-mode-color" ).wpColorPicker(); } );' );
	}

	/**
	 * Registers the setting and fields.
	 */
	public static function register_settings() {
		register_setting(
			'recipe_cook_mode_group',
			Recipe_Cook_Mode_Settings::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( 'Recipe_Cook_Mode_Settings', 'sanitize' ),
				'default'           => Recipe_Cook_Mode_Settings::defaults(),
			)
		);

		add_settings_section( 'recipe_cook_mode_text', __( 'Toggle', 'recipe-cook-mode' ), '__return_false', self::PAGE );
		add_settings_field( 'label', __( 'Label', 'recipe-cook-mode' ), array( __CLASS__, 'text_field' ), self::PAGE, 'recipe_cook_mode_text', array( 'key' => 'label', 'label_for' => 'recipe-cook-mode-label' ) );
		add_settings_field( 'description', __( 'Description', 'recipe-cook-mode' ), array( __CLASS__, 'text_field' ), self::PAGE, 'recipe_cook_mode_text', array( 'key' => 'description', 'label_for' => 'recipe-cook-mode-description' ) );
		add_settings_field( 'color', __( 'Color', 'recipe-cook-mode' ), array( __CLASS__, 'color_field' ), self::PAGE, 'recipe_cook_mode_text', array( 'label_for' => 'recipe-cook-mode-color' ) );

		add_settings_section( 'recipe_cook_mode_wprm', __( 'WP Recipe Maker', 'recipe-cook-mode' ), array( __CLASS__, 'wprm_intro' ), self::PAGE );
		add_settings_field( 'wprm_enabled', __( 'Recipe cards', 'recipe-cook-mode' ), array( __CLASS__, 'wprm_field' ), self::PAGE, 'recipe_cook_mode_wprm' );

		add_settings_section( 'recipe_cook_mode_auto', __( 'Automatic placement', 'recipe-cook-mode' ), array( __CLASS__, 'auto_intro' ), self::PAGE );
		add_settings_field( 'auto_post_types', __( 'Post types', 'recipe-cook-mode' ), array( __CLASS__, 'post_types_field' ), self::PAGE, 'recipe_cook_mode_auto' );
		add_settings_field( 'auto_categories', __( 'Only in categories', 'recipe-cook-mode' ), array( __CLASS__, 'categories_field' ), self::PAGE, 'recipe_cook_mode_auto' );
		add_settings_field( 'auto_position', __( 'Position', 'recipe-cook-mode' ), array( __CLASS__, 'position_field' ), self::PAGE, 'recipe_cook_mode_auto', array( 'key' => 'auto_position', 'label_for' => 'recipe-cook-mode-auto_position' ) );
	}

	/**
	 * Name attribute for a setting.
	 *
	 * @param string $key Setting key.
	 * @return string
	 */
	private static function name( $key ) {
		return Recipe_Cook_Mode_Settings::OPTION . '[' . $key . ']';
	}

	/**
	 * Text input.
	 *
	 * @param array $args Field arguments.
	 */
	public static function text_field( $args ) {
		printf(
			'<input type="text" id="%1$s" class="regular-text" name="%2$s" value="%3$s" />',
			esc_attr( 'recipe-cook-mode-' . $args['key'] ),
			esc_attr( self::name( $args['key'] ) ),
			esc_attr( Recipe_Cook_Mode_Settings::get()[ $args['key'] ] )
		);
	}

	/**
	 * Color picker.
	 */
	public static function color_field() {
		printf(
			'<input type="text" id="recipe-cook-mode-color" class="recipe-cook-mode-color" name="%1$s" value="%2$s" data-default-color="%3$s" />',
			esc_attr( self::name( 'color' ) ),
			esc_attr( Recipe_Cook_Mode_Settings::get()['color'] ),
			esc_attr( Recipe_Cook_Mode_Settings::defaults()['color'] )
		);
	}

	/**
	 * Position select.
	 *
	 * @param array $args Field arguments.
	 */
	public static function position_field( $args ) {
		$current = Recipe_Cook_Mode_Settings::get()[ $args['key'] ];
		printf( '<select id="%1$s" name="%2$s">', esc_attr( 'recipe-cook-mode-' . $args['key'] ), esc_attr( self::name( $args['key'] ) ) );
		printf( '<option value="top" %1$s>%2$s</option>', selected( $current, 'top', false ), esc_html__( 'Top', 'recipe-cook-mode' ) );
		printf( '<option value="bottom" %1$s>%2$s</option>', selected( $current, 'bottom', false ), esc_html__( 'Bottom', 'recipe-cook-mode' ) );
		echo '</select>';
	}

	/**
	 * WP Recipe Maker section intro.
	 */
	public static function wprm_intro() {
		if ( ! Recipe_Cook_Mode_Settings::wprm_active() ) {
			echo '<p>' . esc_html__( 'Install and activate WP Recipe Maker to add the toggle to its recipe cards automatically.', 'recipe-cook-mode' ) . '</p>';
		}
	}

	/**
	 * WP Recipe Maker integration fields.
	 */
	public static function wprm_field() {
		$settings = Recipe_Cook_Mode_Settings::get();
		printf(
			'<label><input type="checkbox" name="%1$s" value="1" %2$s /> %3$s</label>',
			esc_attr( self::name( 'wprm_enabled' ) ),
			checked( $settings['wprm_enabled'], true, false ),
			esc_html__( 'Add the toggle to every WP Recipe Maker recipe card', 'recipe-cook-mode' )
		);
		echo '<p><label for="recipe-cook-mode-wprm_position">' . esc_html__( 'Position on the card:', 'recipe-cook-mode' ) . '</label> ';
		self::position_field(
			array(
				'key' => 'wprm_position',
			)
		);
		echo '</p>';
	}

	/**
	 * Automatic placement intro.
	 */
	public static function auto_intro() {
		echo '<p>' . esc_html__( 'Add the toggle to posts that are not WP Recipe Maker recipes, such as recipes from another plugin or written in the post content. You can also place it anywhere with the Cook Mode block or the [recipe_cook_mode] shortcode.', 'recipe-cook-mode' ) . '</p>';
	}

	/**
	 * Post type checkboxes.
	 */
	public static function post_types_field() {
		$selected = Recipe_Cook_Mode_Settings::get()['auto_post_types'];
		echo '<fieldset>';
		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $type ) {
			if ( 'attachment' === $type->name ) {
				continue;
			}
			printf(
				'<label style="display:block"><input type="checkbox" name="%1$s[]" value="%2$s" %3$s /> %4$s</label>',
				esc_attr( self::name( 'auto_post_types' ) ),
				esc_attr( $type->name ),
				checked( in_array( $type->name, $selected, true ), true, false ),
				esc_html( $type->labels->name )
			);
		}
		echo '</fieldset>';
	}

	/**
	 * Category checkboxes for limiting posts.
	 */
	public static function categories_field() {
		$selected   = Recipe_Cook_Mode_Settings::get()['auto_categories'];
		$categories = get_categories( array( 'hide_empty' => false ) );
		echo '<fieldset style="max-height:12em;overflow:auto">';
		foreach ( $categories as $category ) {
			printf(
				'<label style="display:block"><input type="checkbox" name="%1$s[]" value="%2$d" %3$s /> %4$s</label>',
				esc_attr( self::name( 'auto_categories' ) ),
				(int) $category->term_id,
				checked( in_array( (int) $category->term_id, $selected, true ), true, false ),
				esc_html( $category->name )
			);
		}
		echo '</fieldset><p class="description">' . esc_html__( 'Applies to posts. Leave all unchecked to include every category.', 'recipe-cook-mode' ) . '</p>';
	}

	/**
	 * Renders the settings page.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p><?php esc_html_e( 'Cook Mode uses the Screen Wake Lock API to keep a visitor\'s screen on while they follow a recipe. It works in current versions of Chrome, Edge, Safari, and Firefox. In browsers without support, the toggle explains that Cook Mode is unavailable.', 'recipe-cook-mode' ); ?></p>
			<form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">
				<?php
				settings_fields( 'recipe_cook_mode_group' );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
