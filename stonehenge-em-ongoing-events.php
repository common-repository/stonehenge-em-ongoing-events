<?php
/************************************************************
* Plugin Name:			Events Manager - Ongoing Events
* Description:			Link single events together with one booking form. Perfect for courses, classes, multi-day seminars, etc.
* Version:				1.6.2
* Author:  				Stonehenge Creations
* Author URI: 			https://www.stonehengecreations.nl/
* Plugin URI: 			https://www.stonehengecreations.nl/creations/stonehenge-em-ongoing-events/
* License URI: 			https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: 			stonehenge-em-ongoing-events
* Domain Path: 			/languages/
* Requires at least: 	5.4
* Tested up to: 		6.0
* Requires PHP:			7.3
* Network:				false
************************************************************/

if( !defined('ABSPATH') ) exit;
include_once(ABSPATH.'wp-admin/includes/plugin.php');


#===============================================
function stonehenge_em_ongoing_events() {
	$wp 	= get_plugin_data( __FILE__ );
	$plugin = array(
		'name' 		=> 'Events Manager – Ongoing Events',
		'short' 	=> 'EM - Ongoing Events',
		'icon' 		=> '<span class="icons">&#x1F517;</span>',
		'version' 	=> $wp['Version'],
		'text' 		=> $wp['TextDomain'],
		'slug' 		=> $wp['TextDomain'],
		'class' 	=> 'EM_Ongoing_Events',
		'base' 		=> plugin_basename(__DIR__),
		'prio' 		=> 40,
	);
	$plugin['url'] 		= admin_url().'admin.php?page='.$plugin['slug'];
	$plugin['options']	= get_option( $plugin['slug'] );
	return $plugin;
}


#===============================================
add_action('plugins_loaded', function() {
	if(!function_exists('stonehenge') ) { require_once('stonehenge/init.php'); }

	$plugin = stonehenge_em_ongoing_events();
	if( start_stonehenge($plugin) ) {
		include('classes/class-functions.php');
		include('classes/class-admin.php');
		include('classes/class-metabox.php');
		include('classes/class-public.php');
		include('classes/class-ical.php');
		include('classes/class-init.php');

		global $EM_Ongoing;
		$EM_Ongoing = new EM_Ongoing_Events();
	}
}, 20);
