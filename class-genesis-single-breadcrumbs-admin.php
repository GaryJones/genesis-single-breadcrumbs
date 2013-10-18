<?php
/**
 * Genesis Single Breadcrumbs
 *
 * @package   Genesis_Single_Breadcrumbs
 * @author    Gary Jones <gary@gamajo.com>
 * @license   GPL-2.0+
 * @link      http://gamajo.com/
 * @copyright 2013 Gary Jones, Gamajo Tech
 */

/**
 * Plugin class.
 *
 * @package Genesis_Single_Breadcrumbs
 * @author  Gary Jones <gary@gamajo.com>
 */
class Genesis_Single_Breadcrumbs_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $page_hook = null;

	/**
	 * Initialize the class by hooking in methods.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Adds contextual help when this admin page loads
    	add_action( 'in_admin_header', array( $this, 'add_help' ) );
    	add_action( 'admin_menu', array( $this, 'add_box' ), 999 );
    	add_action( 'save_post', array( $this, 'save_box' ), 1, 2 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Add contextual help.
	 *
	 * @since 1.0.0
	 */
	public function add_help() {
		$screen = get_current_screen();
		if ( 'post' !== $screen->id )
			return;

        $settings =
			'<p>'  . __( 'The disable breadcrumb checkbox allows the whole breadcrumb trail to be hidden for this entry.', 'genesis-single-breadcrumbs' ) . '</p>' .
			'<p>'  . __( 'The breadcrumb title field accepts plain text (markup is stripped out). If left blank, then the entry title will be used as the default breadcrumb.', 'genesis-single-breadcrumbs' ) . '</p>' .
			sprintf(
				'<p><a href="%s">%s</a></p>',
        		esc_url( 'http://github.com/GaryJones/genesis-single-breadcrumbs' ),
        		__( 'Genesis Single Breadcrumbs', 'genesis-single-breadcrumbs' )
        	);

        $screen->add_help_tab(
        	array(
        		'id'      => 'genesis_single_breadcrumbs_settings',
        		'title'   => __( 'Genesis Single Breadcrumbs', 'genesis-single-breadcrumbs' ),
        		'content' => $settings,
        	)
        );

	}

	/**
	 * Register a new meta box to the post or page edit screen, so that the user can set breadcrumbs options on a
	 * per-post or per-page basis.
	 *
	 * @since 1.0.0
	 *
	 * @see Genesis_Single_Breadcrumbs_Admin::do_box() Generates the content in the meta box.
	 */
	public function add_box() {
		foreach ( (array) get_post_types( array( 'public' => true ) ) as $type ) {
			add_meta_box(
				'genesis_single_breadcrumbs', // ID
				__( 'Breadcrumbs', 'genesis-single-breadcrumbs' ), // Box title
				array( $this, 'do_box' ), // callback
				$type, // screen
				'normal', // context
				'low' // priority
			);
		}
	}

	/**
	 * Callback for in-post breadcrumbs meta box.
	 *
	 * @since 1.0.0
	 */
	function do_box() {
		wp_nonce_field( 'genesis_single_breadcrumbs_save', 'genesis_single_breadcrumbs_nonce' );
		?>
		<p>
			<label for="genesis_single_breadcrumbs_disable"><input type="checkbox" name="genesis_single_breadcrumbs[_genesis_single_breadcrumbs_disable]" id="genesis_single_breadcrumbs_disable" value="1" <?php checked( genesis_get_custom_field( '_genesis_single_breadcrumbs_disable' ) ); ?> />
			<?php _e( 'Disable breadcrumbs for this entry.', 'genesis-single-breadcrumbs' ); ?></label>
		</p>
		<p>
			<label for="genesis_single_breadcrumbs_title"><?php _e( 'Custom breadcrumb title:', 'genesis-single-breadcrumbs' ); ?></label>
			<input class="large-text" type="text" name="genesis_single_breadcrumbs[_genesis_single_breadcrumbs_title]" id="genesis_single_breadcrumbs_title" value="<?php echo esc_attr( genesis_get_custom_field( '_genesis_single_breadcrumbs_title' ) ); ?>" />
		</p>
		<?php
	}

	/**
	 * Save the breadcrumbs settings when we save a post or page.
	 *
	 * @since 1.0.0
	 *
	 * @uses genesis_save_custom_fields() Perform checks and saves post meta / custom field data to a post or page.
	 *
	 * @param integer  $post_id Post ID. Not used.
	 * @param stdClass $post    Post object.
	 *
	 * @return mixed Returns post id if permissions incorrect, null if doing autosave, ajax or future post, false if update
	 *               or delete failed, and true on success.
	 */
	public function save_box( $post_id, $post ) {
		if ( ! isset( $_POST['genesis_single_breadcrumbs'] ) )
			return;

		$defaults = array(
			'_genesis_single_breadcrumbs_title'   => '',
			'_genesis_single_breadcrumbs_disable' => '',
		);

		//* Merge user submitted options with fallback defaults
		$data = wp_parse_args( $_POST['genesis_single_breadcrumbs'], $defaults );


		//* Sanitize the title, description, and tags
		foreach ( (array) $data as $key => $value ) {
			if ( in_array( $key, array( '_genesis_single_breadcrumbs_title', ) ) )
				$data[ $key ] = strip_tags( $value );
		}

		genesis_save_custom_fields( $data, 'genesis_single_breadcrumbs_save', 'genesis_single_breadcrumbs_nonce', $post );
	}

}
