<?php 
/*
 * Plugin Name: Negotiabl WooCommerce Extension
 * Plugin URI: http://negotiabl.com/
 * Description: Sparking instant negotiations over Facebook Messenger
 * Version: 1.0.1
 * Author: Gravitum
 * Author URI: http://gravitum.com/
 * Developer: Gravitum
 * Developer URI: http://gravitum.com/
 * Text Domain: woo_negotiabl
 * Domain Path: /languages
 *
 * WC requires at least: 3.2.6
 * WC tested up to: 3.2.6
 *
 * Copyright: © 2018 WooCommerce.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2015 Rg, Inc.
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

function negotiabl_wooCommerceRequired() {
	?>
		<br/>
		<div class="update-nag notice">
		  <p><?php _e( 'Woocommerce plugin required for Woo Negotiabl Plugin.', 'woo_negotiabl' ); ?></p>
		</div>
	<?php
}

/**
 * Check if WooCommerce is active
 **/
if ( !( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) ) {
	add_action( 'admin_notices', 'negotiabl_wooCommerceRequired' );
	return;
}

define( 'NEGOTIABL_VERSION', '0.0.1' );
define( 'NEGOTIABL__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NEGOTIABL_TEXTDOMAIN', 'woo_negotiabl' );

global $wpdb;
define( 'NEGOTIABL_CART_TABLE', $wpdb->prefix . 'woocommerce_negotiabl_cart' );

//local
/* define( 'NEGOTIABL_ADD_API_URL', 'http://localhost:3000/woo-commerce/shop/api-credentials' );
define( 'NEGOTIABL_AUTH_URL', 'http://localhost:3000/woo-commerce/plugin/authenticate' );
define( 'NEGOTIABL_CREATE_URL', 'http://localhost:3000/webhooks/woo-commerce/cart/create' );
define( 'NEGOTIABL_UPDATE_URL', 'http://localhost:3000/webhooks/woo-commerce/cart/update' );  */

//dev
/* define( 'NEGOTIABL_ADD_API_URL', 'https://ecomnegoalerts-dev.herokuapp.com/woo-commerce/shop/api-credentials' );
define( 'NEGOTIABL_AUTH_URL', 'https://ecomnegoalerts-dev.herokuapp.com/woo-commerce/plugin/authenticate' );
define( 'NEGOTIABL_CREATE_URL', 'https://ecomnegoalerts-dev.herokuapp.com/webhooks/woo-commerce/cart/create' );
define( 'NEGOTIABL_UPDATE_URL', 'https://ecomnegoalerts-dev.herokuapp.com/webhooks/woo-commerce/cart/update' ); */

//prod
define( 'NEGOTIABL_ADD_API_URL', 'https://app.negotiabl.com/woo-commerce/shop/api-credentials' );
define( 'NEGOTIABL_AUTH_URL', 'https://app.negotiabl.com/woo-commerce/plugin/authenticate' );
define( 'NEGOTIABL_CREATE_URL', 'https://app.negotiabl.com/webhooks/woo-commerce/cart/create' );
define( 'NEGOTIABL_UPDATE_URL', 'https://app.negotiabl.com/webhooks/woo-commerce/cart/update' );

global $wpdb;
define( 'NEGOTIABL_SUBSCRIPTION_ORDERS', $wpdb->prefix . 'uscreen_subscription_orders' );
define( 'NEGOTIABL_SUBSCRIPTION', $wpdb->prefix . 'uscreen_user_subscriptions' );

require_once( NEGOTIABL__PLUGIN_DIR . 'inc/pluggable-functions.php' );
require_once( NEGOTIABL__PLUGIN_DIR . 'classes/negotiabl.php' );
require_once( NEGOTIABL__PLUGIN_DIR . 'classes/ajax.php' );
require_once( NEGOTIABL__PLUGIN_DIR . 'classes/admin.menu.class.php' );
require_once( NEGOTIABL__PLUGIN_DIR . 'classes/cart.class.php' );

require_once( NEGOTIABL__PLUGIN_DIR . 'models/woo-negotiabl-request.php' );
require_once( NEGOTIABL__PLUGIN_DIR . 'models/cart.php' );

register_activation_hook( __FILE__, array( 'Negotiabl', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'Negotiabl', 'plugin_deactivation' ) );

add_action( 'woocommerce_init', array( 'Negotiabl', 'init' ) );
add_action( 'plugins_loaded', array( 'Negotiabl', 'plugins_loaded' ) );
add_action( 'wp_enqueue_scripts', array( 'Negotiabl', 'addScriptsAndStyles' ) );
//if( !negotiabl_checkIsCurlEnabled( ) ) add_action( 'admin_notices', array( 'Negotiabl', 'curlErrorNotice' ) );

$obj = new NegotiablAdminMenu();
add_filter( 'woocommerce_settings_tabs_array', array( $obj, "wooNegotiablSettingTab" ), 50 );
add_action( 'woocommerce_settings_tabs_' . $obj->menuTab, array( $obj, "wooNegotiablSettingTabFields" ) );
add_action( 'woocommerce_update_options_' . $obj->menuTab, array( $obj, "wooNegotiablUpdateSettingTabFields" ) );

//add_action( 'woocommerce_ajax_added_to_cart', 'Negotiabl_WooCommerceCart::createEventCartByAjax', 10, 1 );
add_action( 'woocommerce_add_to_cart', 'Negotiabl_WooCommerceCart::createEventCart', 99, 6 );
add_filter( 'woocommerce_update_cart_action_cart_updated', 'Negotiabl_WooCommerceCart::updateEventCart', 10, 1 );

/*add_filter( 'woocommerce_api_create_order', 'Negotiabl_WooCommerceCart::updateEventCartIdIntoOrder', 10, 1 );*/
add_filter( 'woocommerce_new_order', 'Negotiabl_WooCommerceCart::updateEventCartIdIntoOrder', 10, 1 );

add_action( 'woocommerce_cart_item_removed', 'Negotiabl_WooCommerceCart::removeCartItem', 10, 2 );
add_action( 'woocommerce_cart_emptied', 'Negotiabl_WooCommerceCart::emptyCart' );
