<?php
if( !defined('ABSPATH') ) exit;

if( !class_exists('EM_Ongoing_Events') ) :
Class EM_Ongoing_Events extends EM_Ongoing_Events_iCal {

	var $plugin;
	var $text;
	var $options;
	var $is_ready;

	#===============================================
	public function __construct() {
		$plugin 		= self::plugin();
		$slug 			= $plugin['slug'];
		$this->plugin 	= $plugin;
		$this->text 	= $plugin['text'];
		$this->options 	= $plugin['options'];
		$this->is_ready = is_array( $this->options ) ? true : false;

		// Options.
		add_filter("{$slug}_options", array($this, 'define_options'));
		add_filter("{$slug}_options", array($this, 'define_placeholders'));

		if( $this->is_ready && stonehenge()->is_valid ) {
			// Functions.
			add_filter('em_cpt_event', array($this, 'add_hierarchy'), 13, 1);
			add_filter('post_row_actions', array($this, 'hide_actions'), 30, 2 ); 		// If events are shown as posts.
			add_filter('page_row_actions', array($this, 'hide_actions'), 30, 2 ); 		// If events are shown as pages.
			add_filter('manage_event_posts_custom_column', array($this, 'add_descripton_to_column_output'), 10, 2);
			add_action('before_delete_post', array($this, 'maybe_delete_subevents'));

			// Admin.
			add_filter('em_ongoing_wildcards', array($this, 'replace_wildcards'), 10, 3);

			// Metabox.
			add_action('add_meta_boxes', array($this, 'create_meta_box'));
			add_filter('em_event_validate_meta', array($this, 'validate_ongoing_input'), 10, 2);
			add_action('post_updated', array($this, 'save_meta_box_data'));
			add_action('save_post', array($this, 'process_ongoing_values'), 10, 2);

			// Public.
			add_shortcode('ongoing_events', array($this, 'show_ongoing_events_table'));
			add_shortcode('ongoing-events', array($this, 'show_ongoing_events_table')); // Just in case..
			add_filter('em_event_output_placeholder', array($this, 'placeholders'), 10, 3);
			add_filter('em_event_output_single', array($this, 'process_wildcards'), 7, 2);

			// iCal.
			add_action('wp_ajax_em-ongoing-ics', array($this, 'create_ical'));
			add_action('wp_ajax_nopriv_em-ongoing-ics', array($this, 'create_ical'));
		}
	}


	#===============================================
	private static function plugin() {
		return stonehenge_em_ongoing_events();
	}


	#===============================================
	public static function dependency() {
		$dependency = array(
			'events-manager/events-manager.php' => 'Events Manager',
		);
		return $dependency;
	}


	#===============================================
	public static function plugin_updated() {
		return;
	}


	#===============================================
	public static function register_assets() {
		$plugin 	= self::plugin();
		$version 	= $plugin['version'];
//		wp_register_style('ongoing-events-css', plugins_url('assets/unminified/ongoing-events-admin.less', __DIR__), array(), $version, 'all');
		wp_register_style('ongoing-events-css', plugins_url('assets/ongoing-events-admin.min.css', __DIR__), array(), $version, 'all');
		wp_register_script('ongoing-events-js', plugins_url('assets/ongoing-events-admin.min.js', __DIR__), array('jquery'), $version, true);
		wp_localize_script('ongoing-events-js', 'EMOE', self::localize_assets() );
	}


	#===============================================
	public static function localize_assets() {
		$plugin 	= self::plugin();
		$localize 	= array(
			// Date Picker.
			'dateFormat' 		=> stonehenge()->php_date_to_js(),
			'daysFull'			=> stonehenge()->localize_datepicker('weekdays_full', true, false),
			'daysShort'			=> stonehenge()->localize_datepicker('weekdays_short', false, true),
			'monthsFull'		=> stonehenge()->localize_datepicker('months_full', true, false),
			'monthsShort'		=> stonehenge()->localize_datepicker('months_short', true, false),
			'firstDay'			=> get_option('start_of_week'),
			'next'				=> __wp('Next'),
			'previous' 			=> __wp('Previous'),

			// Time Picker.
			'time_format'		=> stonehenge()->get_time_format(),
		);

		if( is_array($plugin['options']) ) {
			global $EM_Ongoing;
			$localize['get'] 	= $EM_Ongoing->translate();
		}
		return $localize;
	}


	#===============================================
	public static function load_admin_assets()  {
		wp_enqueue_style('ongoing-events-css');
		wp_enqueue_script('ongoing-events-js');
	}


} // End class.
endif;
