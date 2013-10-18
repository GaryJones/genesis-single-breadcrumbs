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
class Genesis_Single_Breadcrumbs_Public {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the class by hooking in methods.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		add_filter( 'genesis_single_crumb', array( $this, 'filter_single_crumb' ), 10, 2 );
		add_action( 'genesis_before', array( $this, 'disable_breadcrumbs' ) );
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
	 * Filter the single crumb to replace the string.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $trail Existing markup for the whole breadcrumb trail.
	 * @param  array $args   Breadcrumb trail arguments.
	 *
	 * @return string        Amended markup for the whole breadcrumb trail.
	 */
	public function filter_single_crumb( $trail, $args ) {
		if ( ! is_singular() )
			return $trail;

		$new_crumb = genesis_get_custom_field( '_genesis_single_breadcrumbs_title' );
		if ( ! $new_crumb )
			return $trail;

		$trimmed_trail = $this->strip_last_crumb( $trail, $args['sep'] );

		return $trimmed_trail . esc_html( $new_crumb );
	}

	/**
	 * Remove Post Title from Breadcrumb.
	 *
	 * Takes a substring of the breadcrumb trail, starting at 0, with a length of up to and including the last
	 * occurrence of the separator string.
	 *
	 * @since 1.0.0
	 */
	protected function strip_last_crumb( $trail, $separator ) {
		return mb_substr( $trail, 0, mb_strrpos( $trail, $separator ) + mb_strlen( $separator ) );
	}

	/**
	 * Disable the breadcrumbs, by unhooking genesis_do_breadcrumbs from whereever it is currently hooked.
	 *
	 * @since 1.0.0
	 *
	 * @uses Genesis_Single_breadcrumbs_Public::get_hook_for_callback()
	 */
	public function disable_breadcrumbs() {
		if ( is_singular() && genesis_get_custom_field( '_genesis_single_breadcrumbs_disable' ) ) {
			$hook_data = $this->get_hook_for_callback( 'genesis_do_breadcrumbs' );
			remove_action( $hook_data['hook'], 'genesis_do_breadcrumbs', $hook_data['priority'] );
		}
	}

	/**
	 * Search through all hooked in callbacks to find the first hook name and priority.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $callback_to_find Name of the callback to find.
	 *
	 * @return array                    Details of the hook and priority.
	 */
	public function get_hook_for_callback( $callback_to_find ) {
		global $wp_filter;
		foreach ( $wp_filter as $hook => $hooked ) {
			foreach ( $hooked as $priority => $callbacks ) {
				foreach ( array_keys( $callbacks ) as $callback_name ) {
					if ( $callback_to_find === $callback_name ) {
						return array( 'hook' => $hook, 'priority' => $priority );
					}
				}
			}
		}
	}

}
