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
		add_action( 'genesis_before', array( $this, 'disable_breadcrumbs' ) );
		add_filter( 'genesis_single_crumb', array( $this, 'filter_single_crumb' ), 10, 2 );
		add_filter( 'genesis_page_crumb', array( $this, 'filter_page_crumb' ), 10, 2 );
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
	 * Get the custom breadcrumb title.
	 *
	 * @since 1.1.0
	 *
	 * @return string Custom breadcrumb title if set, empty string otherwise.
	 */
	protected function get_breadcrumb_title( $post_id = null ) {
		if ( null === $post_id )
			$post_id = get_the_ID();

		$custom_field = get_post_meta( $post_id, '_genesis_single_breadcrumbs_title', true );

		if ( ! $custom_field )
			return get_the_title( $post_id );

		//* Return custom field, slashes stripped, sanitized if string
		return is_array( $custom_field ) ? stripslashes_deep( $custom_field ) : stripslashes( wp_kses_decode_entities( $custom_field ) );
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

		$new_crumb = $this->get_breadcrumb_title();
		if ( ! $new_crumb )
			return $trail;

		$trimmed_trail = $this->strip_last_crumb( $trail, $args['sep'] );

		return $trimmed_trail . esc_html( $new_crumb );
	}

	/**
	 * Filter the page crumb to replace the string.
	 *
	 * @since 1.1.0
	 *
	 * @param  string $trail Existing markup for the whole breadcrumb trail.
	 * @param  array $args   Breadcrumb trail arguments.
	 *
	 * @return string        Amended markup for the whole breadcrumb trail.
	 */
	public function filter_page_crumb( $trail, $args ) {
		if ( ! is_page() )
			return $trail;

		//* Don't do anything - we're on the front page and we've already dealt with that elsewhere
		if ( is_front_page() )
			return $trail;

		global $wp_query;

		$post = $wp_query->get_queried_object();

		//* If this is a top level Page, it's simple to output the breadcrumb
		if ( ! $post->post_parent )
			return $this->filter_single_crumb( $trail, $args );

		if ( isset( $post->ancestors ) ) {
			if ( is_array( $post->ancestors ) )
				$ancestors = array_values( $post->ancestors );
			else
				$ancestors = array( $post->ancestors );
		} else {
			$ancestors = array( $post->post_parent );
		}

		$crumbs = array();
		foreach ( $ancestors as $ancestor ) {
			array_unshift(
				$crumbs,
				$this->get_breadcrumb_link(
					get_permalink( $ancestor ),
					sprintf( __( 'View %s', 'genesis-single-breadcrumbs' ), get_the_title( $ancestor ) ),
					$this->get_breadcrumb_title( $ancestor ),
					false,
					$args
				)
			);
		}

		//* Add the current page breadcrumb title
		$crumbs[] = $this->get_breadcrumb_title( $post->ID );

		return join( $args['sep'], $crumbs );

	}

	/**
	 * Return anchor link for a single crumb.
	 *
	 * Taken straight from Genesis 2.0.1, but with an extra parameter, so that the args can be passed through to the
	 * filter, as in Genesis core.
	 *
	 * @since 1.1.0
	 *
	 * @param string $url     URL for href attribute.
	 * @param string $title   Title attribute.
	 * @param string $content Linked content.
	 * @param string $sep     Separator.
	 * @param array  $args    Breadcrumb trail args.
	 *
	 * @return string HTML markup for anchor link and optional separator.
	 */
	protected function get_breadcrumb_link( $url, $title, $content, $sep = false, $args ) {

		$link = sprintf( '<a href="%s" title="%s">%s</a>', esc_attr( $url ), esc_attr( $title ), esc_html( $content ) );

		$link = apply_filters( 'genesis_breadcrumb_link', $link, $url, $title, $content, $args );

		if ( $sep )
			$link .= $sep;

		return $link;
	}

	/**
	 * Remove Post Title from Breadcrumb.
	 *
	 * Takes a substring of the breadcrumb trail, starting at 0, with a length of up to last
	 * occurrence of the separator string.
	 *
	 * @since 1.0.0
	 */
	protected function strip_last_crumb( $trail, $separator ) {
		$trimmed_trail = mb_substr( $trail, 0, (int) mb_strrpos( $trail, $separator ) );
		if ( $trimmed_trail )
		  	$trimmed_trail .= $separator;
		return $trimmed_trail;
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
	protected function get_hook_for_callback( $callback_to_find ) {
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
