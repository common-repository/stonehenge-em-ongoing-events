<?php

// If uninstall is not called by WordPress, exit.
if( !defined('WP_UNINSTALL_PLUGIN') )
    exit();

// Clean th WP Database from this plugin.
$slug 		= 'stonehenge-em-ongoing-events';
$version 	= $slug .'_version';
$saved  	= get_option($slug);

if( $saved['delete'] && $saved['delete'] != 'no' ) {
	delete_option( $slug );
	delete_option( $version );
}
