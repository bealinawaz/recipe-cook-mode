<?php
/**
 * Front-end toggle, integrations, shortcode, and block.
 *
 * @package Recipe_Cook_Mode
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders the Cook Mode toggle and decides where it appears.
 */
final class Recipe_Cook_Mode {

	const HANDLE    = 'recipe-cook-mode';
	const SHORTCODE = 'recipe_cook_mode';
	const BLOCK     = 'recipe-cook-mode/toggle';

	/**
	 * Number of toggles rendered on this page, used for unique element IDs.
	 *
	 * @var int
	 */
	private static $count = 0;

	/**
	 * Registers hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue' ) );
		add_filter( 'the_content', array( __CLASS__, 'auto_insert' ), 20 );
		add_filter( 'wprm_recipe_shortcode_output', array( __CLASS__, 'add_to_wprm_recipe' ), 10, 3 );
	}

	/**
	 * Registers assets, the shortcode, and the block.
	 */
	public static function register() {
		wp_register_style( self::HANDLE, RECIPE_COOK_MODE_URL . 'assets/cook-mode.css', array(), RECIPE_COOK_MODE_VERSION );
		wp_register_script(
			self::HANDLE,
			RECIPE_COOK_MODE_URL . 'assets/cook-mode.js',
			array(),
			RECIPE_COOK_MODE_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		wp_localize_script(
			self::HANDLE,
			'recipeCookMode',
			array(
				'active'      => __( 'Cook Mode is on. Your screen will stay awake.', 'recipe-cook-mode' ),
				'paused'      => __( 'Cook Mode is paused. It will resume when you return to this page.', 'recipe-cook-mode' ),
				'unsupported' => __( 'Cook Mode is not supported in this browser.', 'recipe-cook-mode' ),
				'failed'      => __( 'Cook Mode could not be turned on. Check your battery saver settings and try again.', 'recipe-cook-mode' ),
			)
		);

		add_shortcode( self::SHORTCODE, array( __CLASS__, 'shortcode' ) );

		wp_register_script(
			'recipe-cook-mode-block',
			RECIPE_COOK_MODE_URL . 'assets/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
			RECIPE_COOK_MODE_VERSION,
			true
		);
		register_block_type(
			self::BLOCK,
			array(
				'api_version'     => 3,
				'title'           => __( 'Cook Mode', 'recipe-cook-mode' ),
				'category'        => 'widgets',
				'icon'            => 'visibility',
				'description'     => __( 'A toggle that keeps the screen awake while visitors follow a recipe.', 'recipe-cook-mode' ),
				'editor_script'   => 'recipe-cook-mode-block',
				'style'           => self::HANDLE,
				'attributes'      => array(
					'label'       => array(
						'type'    => 'string',
						'default' => '',
					),
					'description' => array(
						'type'    => 'string',
						'default' => '',
					),
				),
				'render_callback' => array( __CLASS__, 'render_block' ),
			)
		);
	}

	/**
	 * Enqueues assets in the page head when the current post is known to show a toggle.
	 * Toggles added in other ways enqueue their assets when they render.
	 */
	public static function maybe_enqueue() {
		if ( ! is_singular() ) {
			return;
		}
		$post = get_post();
		if ( ! $post ) {
			return;
		}
		$content = (string) $post->post_content;
		if (
			has_shortcode( $content, self::SHORTCODE )
			|| has_block( self::BLOCK, $post )
			|| self::auto_insert_applies( $post )
			|| ( self::wprm_applies() && self::has_wprm_recipe( $content ) )
		) {
			self::enqueue();
		}
	}

	/**
	 * Enqueues the toggle's script and style.
	 */
	private static function enqueue() {
		wp_enqueue_style( self::HANDLE );
		wp_enqueue_script( self::HANDLE );
	}

	/**
	 * Adds the toggle to single WP Recipe Maker recipe cards.
	 *
	 * @param string $output Recipe HTML.
	 * @param object $recipe Recipe object.
	 * @param string $type   Display type: single, archive, amp, and others.
	 * @return string
	 */
	public static function add_to_wprm_recipe( $output, $recipe, $type ) {
		if ( 'single' !== $type || ! self::wprm_applies() || is_feed() ) {
			return $output;
		}
		$toggle = self::render();
		return 'bottom' === Recipe_Cook_Mode_Settings::get()['wprm_position'] ? $output . $toggle : $toggle . $output;
	}

	/**
	 * Adds the toggle to post content for the chosen post types and categories.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public static function auto_insert( $content ) {
		if ( ! is_singular() || ! in_the_loop() || ! is_main_query() || is_feed() ) {
			return $content;
		}
		$post = get_post();
		if ( ! $post || ! self::auto_insert_applies( $post ) ) {
			return $content;
		}
		// A WP Recipe Maker card in this post already shows its own toggle.
		if ( self::wprm_applies() && self::has_wprm_recipe( (string) $post->post_content ) ) {
			return $content;
		}
		if ( has_shortcode( $post->post_content, self::SHORTCODE ) || has_block( self::BLOCK, $post ) ) {
			return $content;
		}
		$toggle = self::render();
		return 'bottom' === Recipe_Cook_Mode_Settings::get()['auto_position'] ? $content . $toggle : $toggle . $content;
	}

	/**
	 * Shortcode handler: [recipe_cook_mode label="..." description="..."].
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'label'       => '',
				'description' => '',
			),
			$atts,
			self::SHORTCODE
		);
		return self::render( $atts['label'], $atts['description'] );
	}

	/**
	 * Block render callback.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function render_block( $attributes ) {
		return self::render(
			isset( $attributes['label'] ) ? (string) $attributes['label'] : '',
			isset( $attributes['description'] ) ? (string) $attributes['description'] : ''
		);
	}

	/**
	 * Toggle markup. Empty label or description falls back to the settings.
	 *
	 * @param string $label       Label text.
	 * @param string $description Description text.
	 * @return string
	 */
	public static function render( $label = '', $description = '' ) {
		$settings    = Recipe_Cook_Mode_Settings::get();
		$label       = '' !== trim( $label ) ? $label : $settings['label'];
		$description = '' !== trim( $description ) ? $description : $settings['description'];
		$id          = 'recipe-cook-mode-' . ( ++self::$count );

		self::enqueue();

		ob_start();
		?>
		<div class="recipe-cook-mode" style="<?php echo esc_attr( '--recipe-cook-mode-color: ' . $settings['color'] ); ?>">
			<label class="recipe-cook-mode__toggle" for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox" role="switch" class="recipe-cook-mode__input" id="<?php echo esc_attr( $id ); ?>"<?php echo '' !== trim( $description ) ? ' aria-describedby="' . esc_attr( $id . '-description' ) . '"' : ''; ?> />
				<span class="recipe-cook-mode__switch" aria-hidden="true"></span>
				<span class="recipe-cook-mode__label"><?php echo esc_html( $label ); ?></span>
			</label>
			<?php if ( '' !== trim( $description ) ) : ?>
				<p class="recipe-cook-mode__description" id="<?php echo esc_attr( $id . '-description' ); ?>"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
			<p class="recipe-cook-mode__status" role="status" aria-live="polite"></p>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Whether the WP Recipe Maker integration is on and WP Recipe Maker is active.
	 *
	 * @return bool
	 */
	private static function wprm_applies() {
		return Recipe_Cook_Mode_Settings::wprm_active() && Recipe_Cook_Mode_Settings::get()['wprm_enabled'];
	}

	/**
	 * Whether post content contains a WP Recipe Maker recipe.
	 *
	 * @param string $content Post content.
	 * @return bool
	 */
	private static function has_wprm_recipe( $content ) {
		return false !== strpos( $content, 'wp:wp-recipe-maker/recipe' ) || has_shortcode( $content, 'wprm-recipe' );
	}

	/**
	 * Whether auto-insert is configured for this post.
	 *
	 * @param WP_Post $post Post.
	 * @return bool
	 */
	private static function auto_insert_applies( $post ) {
		$settings = Recipe_Cook_Mode_Settings::get();
		if ( ! in_array( $post->post_type, $settings['auto_post_types'], true ) ) {
			return false;
		}
		if ( 'post' === $post->post_type && $settings['auto_categories'] ) {
			return has_category( $settings['auto_categories'], $post );
		}
		return true;
	}
}
