<?php
/*
 * Plugin Name: Live Template Editor Addon
 * Version: 1.0.2
 * Plugin URI: https://github.com/rafasashi
 * Description: [PLUGIN_DESCRIPTION]
 * Author: Rafasashi
 * Author URI: https://github.com/rafasashi
 * Requires at least: 4.6
 * Tested up to: 4.7
 *
 * Text Domain: ltple
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Rafasashi
 * @since 1.0.0
 */
	
	/**
	* Add documentation link
	*
	*/
	
	if ( ! defined( 'ABSPATH' ) ) exit;
	
	/**
	 * Returns the main instance of LTPLE_Addon to prevent the need to use globals.
	 *
	 * @since  1.0.0
	 * @return object LTPLE_Addon
	 */
	function LTPLE_Addon ( $version = '1.0.0' ) {
		
		if ( ! class_exists( 'LTPLE_Client' ) ) return;
		
		$instance = LTPLE_Client::instance( __FILE__, $version );
		
		if ( empty( $instance->addon ) ) {
			
			$instance->addon = new stdClass();
			
			$instance->addon = LTPLE_Addon::instance( __FILE__, $instance, $version );
		}

		return $instance;
	}	
	
	add_filter( 'plugins_loaded', function(){

		// Load plugin functions
		
		require_once( 'includes/functions.php' );	
		
		// Load plugin class files

		require_once( 'includes/class-ltple.php' );
		require_once( 'includes/class-ltple-settings.php' );

		// Autoload plugin libraries
		
		$lib = glob( __DIR__ . '/includes/lib/class-ltple-*.php');
		
		foreach($lib as $file){
			
			require_once( $file );
		}
	
		LTPLE_Addon('1.1.0');	
	});