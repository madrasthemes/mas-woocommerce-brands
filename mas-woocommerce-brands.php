<?php
/**
 * Plugin Name:       MAS Brands for WooCommerce
 * Plugin URI:        https://github.com/madrasthemes/mas-woocommerce-brands
 * Description:       Add brands to your products, as well as widgets and shortcodes for displaying your brands.
 * Version:           1.0.8
 * Requires at least: 5.3
 * Requires PHP:      7.4
 * Author:            MadrasThemes
 * Author URI:        https://madrasthemes.com/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       mas-wc-brands
 * Domain Path:       /languages
 * WC tested up to:   8.5
 *
 * @package Mas_WC_Brands
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define MAS_WCBR_PLUGIN_FILE.
if ( ! defined( 'MAS_WCBR_PLUGIN_FILE' ) ) {
	define( 'MAS_WCBR_PLUGIN_FILE', __FILE__ );
}

/**
 * Required functions
 */
if ( ! function_exists( 'mas_wcbr_is_woocommerce_active' ) ) {
	/**
	 * Check if WooCommerce is active.
	 *
	 * @return bool
	 */
	function mas_wcbr_is_woocommerce_active() {

		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		return in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
	}
}

if ( mas_wcbr_is_woocommerce_active() ) {
	// Include the main Mas_WC_Brands class.
	if ( ! class_exists( 'Mas_WC_Brands' ) ) {
		include_once dirname( MAS_WCBR_PLUGIN_FILE ) . '/includes/class-mas-wc-brands.php';
	}

	/**
	 * Unique access instance for Mas_WC_Brands class.
	 */
	function Mas_WC_Brands() { //phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
		return Mas_WC_Brands::instance();
	}

	// Global for backwards compatibility.
	$GLOBALS['mas_wc_brands'] = Mas_WC_Brands();
}

add_action(
	'before_woocommerce_init',
	function() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);
