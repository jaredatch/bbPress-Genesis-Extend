<?php
/**
 * Plugin Name: bbPress Genesis Extend
 * Plugin URI:  http://wordpress.org/extend/plugins/bbpress-genesis-extend/
 * Description: Provides basic compaitibility between bbPress and the <a href="http://jaredatchison.com/go/genesis/">Genesis Framework</a>.
 * Version:     1.0.2
 * Author:      Jared Atchison
 * Author URI:  http://www.jaredatchison.com 
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author     Jared Atchison
 * @version    1.0.2
 * @package    bbPressGenesisExtend
 * @copyright  Copyright (c) 2012, Jared Atchison
 * @link       http://jaredatchison.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * bbPress Genesis Extend init class
 *
 * @since 0.8.0
 */
class bbpge_init {

	/**
	 * We hook into bbp_after_setup_theme, this way if bbPress
	 * isn't activated we won't load the plugin.
	 *
	 * @since 0.8.0
	 */
	function __construct() {

		// Activation
		register_activation_hook( __FILE__,  array( $this, 'activation'    ) );

		add_action( 'bbp_after_setup_theme', array( $this, 'genesis_check' ) );
	}
	
	/**
	 * Check to see if  a Genesis child theme is in place.
	 *
	 * @since 0.8.0
	 */
	function genesis_check() {
		
		if ( 'genesis' == basename( TEMPLATEPATH ) ) {

			// Load the text domain for translations
			add_action( 'init', array( $this, 'pe_init' ) );

			// The meat and gravy
			require_once( dirname( __FILE__ )  . '/bbpress-genesis-extend.php'          );
			require_once( dirname( __FILE__ )  . '/bbpress-genesis-extend-settings.php' );

			// All systems go!
			add_action( 'bbp_ready', 'bbpge_setup', 6 );
		}
	}

	/**
	 * Set plugin version number upon activation
	 *
	 * We use this to turn off features for new installs that have been deprecated.
	 *
	 * @since 0.9.0
	 */
	function activation() {
		add_option( 'bbpge_version', '1.0.1' );
	}

	/**
	 * Load the textdomain so we can support other languages
	 *
	 * @since 0.8.0
	 */
	function pe_init() {
		load_plugin_textdomain( 'bbpress-genesis-extend', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
	
}

new bbpge_init();