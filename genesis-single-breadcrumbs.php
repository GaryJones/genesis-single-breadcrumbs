<?php
/**
 * Genesis Single Breadcrumbs
 *
 * @package           Genesis_Single_Breadcrumbs
 * @author            Gary Jones <gary@gamajo.com>
 * @license           GPL-2.0+
 * @link              http://gamajo.com/
 * @copyright         2013 Gary Jones, Gamajo Tech
 *
 * @wordpress-plugin
 * Plugin Name:       Genesis Single Breadcrumbs
 * Plugin URI:        http://gamajo.com/
 * Description:       Adds per-entry options for breadcrumbs when a Genesis child theme is active.
 * Version:           1.1.0
 * Author:            Gary Jones
 * Author URI:        http://gamajo.com/
 * Text Domain:       genesis-single-breadcrumbs
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/GaryJones/genesis-single-breadcrumbs
 * GitHub Branch:     master
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Register hooks that are fired when the plugin is activated and deactivated, respectively.
register_activation_hook( __FILE__, array( 'Genesis_Single_Breadcrumbs', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Genesis_Single_Breadcrumbs', 'deactivate' ) );

require plugin_dir_path( __FILE__ ) . 'class-genesis-single-breadcrumbs.php';
add_action( 'plugins_loaded', array( 'Genesis_Single_Breadcrumbs', 'get_instance' ) );

require plugin_dir_path( __FILE__ ) . 'class-genesis-single-breadcrumbs-public.php';
add_action( 'plugins_loaded', array( 'Genesis_Single_Breadcrumbs_Public', 'get_instance' ) );

if ( is_admin() ) {
	require plugin_dir_path( __FILE__ ) . 'class-genesis-single-breadcrumbs-admin.php';
	add_action( 'plugins_loaded', array( 'Genesis_Single_Breadcrumbs_Admin', 'get_instance' ) );
}

