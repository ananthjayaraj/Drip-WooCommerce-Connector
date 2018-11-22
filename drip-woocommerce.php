<?php
/**
 * Plugin Name:  WooCommerce Drip Connector
 * Description: Adds customer email to drip when a order is created.
 * Author: Ananth Jayarajan
 * Author URI: https://www.ananthvj.com
 * Version: 1.0
 * Text Domain: drip-woocommerce
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @author    Ananth Jayarajan
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 */


 if ( ! function_exists('write_log')) {
	 function write_log ( $log )  {
		 if ( is_array( $log ) || is_object( $log ) ) {
			 error_log( print_r( $log, true ) );
		 } else {
			 error_log( $log );
		 }
	 }
 }


defined( 'ABSPATH' ) or exit;

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	if ( ! class_exists( 'WC_DripConn' ) ) {

		//not using string tables for now
		//load_plugin_textdomain( 'wc_acme', false, dirname( plugin_basename( __FILE__ ) ) . '/' );

		class WC_DripConn {
			public function __construct() {
				// called only after woocommerce has finished loading
				add_action( 'woocommerce_init', array( &$this, 'woocommerce_loaded' ) );

				// called after all plugins have loaded
				add_action( 'plugins_loaded', array( &$this, 'plugins_loaded' ) );

				// called just before the woocommerce template functions are included
				add_action( 'init', array( &$this, 'include_template_functions' ), 20 );

				add_action('woocommerce_new_order', array( &$this, 'b7_wc_new_order'), 10);

				// indicates we are running the admin
				if ( is_admin() ) {
					// ...
				}

				// indicates we are being served over ssl
				if ( is_ssl() ) {
					// ...
				}

				// take care of anything else that needs to be done immediately upon plugin instantiation, here in the constructor
			}

			public function b7_wc_new_order($order_id) {

        // Drip API PHP Library Class
        require_once( plugin_dir_path( __FILE__ ) . 'includes/lib/Drip_API.class.php' );

				// Get an instance of the WC_Order object (same as before)
				$order = wc_get_order( $order_id );
        $billing_email = $order->get_billing_email();
				//write_log($billing_email);

      $wcdrip_api = new Drip_Api( /*'insert_your_drip_api_key'*/ );

        if (!$wcdrip_api) write_log('drip api object creation failed');

        // Subscriber Parameters
        $subscriber_params = array(
          'account_id'    => /*'insert_your_drip_account_id'*/,
          'email'         => $billing_email,
          // 'custom_fields' => $this->custom_fields( $order, $customer_id ),
          // 'tags'          => $tags,
        );

        $return = $wcdrip_api->create_or_update_subscriber( $subscriber_params );

        //write_log($return);
			}

			/**
			 * Take care of anything that needs woocommerce to be loaded.
			 * For instance, if you need access to the $woocommerce global
			 */
			public function woocommerce_loaded() {
				// ...
			}

			/**
			 * Take care of anything that needs all plugins to be loaded
			 */
			public function plugins_loaded() {
				// ...
			}

			/**
			 * Override any of the template functions from woocommerce/woocommerce-template.php
			 * with our own template functions file
			 */
			public function include_template_functions() {
				//include( 'woocommerce-template.php' );
			}
		}

	}
}

$dripconn = new WC_DripConn();

/*  SETTING API USING GENERATOR http://wpsettingsapi.jeroensormani.com/ */


add_action( 'admin_menu', 'b7wcd_add_admin_menu' );
add_action( 'admin_init', 'b7wcd_settings_init' );


function b7wcd_add_admin_menu(  ) {

	add_options_page( 'WooCommerce Drip Connector', 'WooCommerce Drip Connector', 'manage_options', 'woocommerce_drip_connector', 'b7wcd_options_page' );

}


function b7wcd_settings_init(  ) {

	register_setting( 'pluginPage', 'b7wcd_settings' );

	add_settings_section(
		'b7wcd_pluginPage_section',
		__( 'Your section description', 'wordpress' ),
		'b7wcd_settings_section_callback',
		'pluginPage'
	);

	add_settings_field(
		'b7wcd_textarea_field_0',
		__( 'Settings field description', 'wordpress' ),
		'b7wcd_textarea_field_0_render',
		'pluginPage',
		'b7wcd_pluginPage_section'
	);

	add_settings_field(
		'b7wcd_textarea_field_1',
		__( 'Settings field description', 'wordpress' ),
		'b7wcd_textarea_field_1_render',
		'pluginPage',
		'b7wcd_pluginPage_section'
	);


}


function b7wcd_textarea_field_0_render(  ) {

	$options = get_option( 'b7wcd_settings' );
	?>
	<textarea cols='40' rows='5' name='b7wcd_settings[b7wcd_textarea_field_0]'>
		<?php echo $options['b7wcd_textarea_field_0']; ?>
 	</textarea>
	<?php

}


function b7wcd_textarea_field_1_render(  ) {

	$options = get_option( 'b7wcd_settings' );
	?>
	<textarea cols='40' rows='5' name='b7wcd_settings[b7wcd_textarea_field_1]'>
		<?php echo $options['b7wcd_textarea_field_1']; ?>
 	</textarea>
	<?php

}


function b7wcd_settings_section_callback(  ) {

	echo __( 'This section description', 'wordpress' );

}


function b7wcd_options_page(  ) {

	?>
	<form action='options.php' method='post'>

		<h2>WooCommerce Drip Connector</h2>

		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>

	</form>
	<?php

}

?>
