<?php
if( !defined('ABSPATH') ) exit;

if( !class_exists('EM_Ongoing_Events_Functions') ) :
Class EM_Ongoing_Events_Functions {


	#===============================================
	public function is_ongoing_series( $EM_Event ) {
		// Function to check in other plugins if this event is part of an Ongoing Series.
		$parent_id = $EM_Event->post_parent > 0 ? $EM_Event->post_parent : $EM_Event->post_id;
		$checkbox  = get_post_meta($parent_id, '_ongoing_check', true);
		return !empty($checkbox) ? $parent_id : false;
	}


	#===============================================
	public function is_ongoing() {
		global $EM_Event;
		if( is_object($EM_Event) ) {
			$parent_id 		= $EM_Event->post_parent;
			$checkbox 		= get_post_meta($EM_Event->post_id, '_ongoing_check', true);
			$is_ongoing 	= ($checkbox === 'yes' && $parent_id === 0) || ($parent_id > 0 ) ? true : false;
			return $is_ongoing;
		}
		return false;
	}


	#===============================================
	public function has_parent() {
		global $EM_Event;
		return (is_object($EM_Event) && $this->is_ongoing() && $EM_Event->post_parent > 0) ? true : false;
	}


	#===============================================
	public function is_parent() {
		global $EM_Event;
		return (is_object($EM_Event) && $this->is_ongoing() && $EM_Event->post_parent === 0) ? true : false;
	}


	#===============================================
	public function get_parent_id() {
		global $EM_Event;
		if( is_object($EM_Event) ) {
			return $EM_Event->post_parent > 0 ? $EM_Event->post_parent : $EM_Event->post_id;
		}
		return false;
	}


	#===============================================
	public function get_sub_events( $parent_id = false ) {
		global $EM_Event;
		if( !$parent_id ) {
			$parent_id = $this->get_parent_id();
		}

		if( is_object($EM_Event) ) {
			// Get Sub Events.
			$args = array(
				'post_type' 	=> 'event',
				'post_parent' 	=> $parent_id,
				'numberposts'	=> -1,
				'post_status' 	=> 'any',
				'orderby' 		=> 'menu_order',
				'order' 		=> 'ASC',
			);
			$sub_events = get_children($args);
			return $sub_events;
		}
		return false;
	}


	#===============================================
	public function get_event_by_slug( $page_slug ) {
		global $wpdb;
		$table 	= EM_EVENTS_TABLE;
		$result = $wpdb->get_var("SELECT `post_id` FROM {$table} where `event_slug` = '{$page_slug}'");

		if( $result ) {
			return $result;
		}
		return false;
	}


	#===============================================
	public function translate() {
		$main = sprintf( '<strong>%s</strong><br>%s %s',
			esc_html__('This is the main event of an ongoing event series.', $this->text),
			esc_html__('Dates and booking are maintained here.', $this->text),
			esc_html__('If you delete this event, all linked sub-events will be deleted as well.', $this->text)
		);

		$translate	= array(
			'add'			=> __wp('Add'),
			'remove'		=> __wp('Remove'),
			'edit'			=> __wp('Edit'),
			'date_format'	=> stonehenge()->get_date_format(),
			'time_format'	=> stonehenge()->get_time_format(),
			'maintain'		=> __('These details are maintained in the Main Event.', $this->text),
			'admin_main'	=> $main,
		);

		if( $this->is_ready ) {
			$strings 	= $this->options['strings'];
			$translate 	= array_merge($strings, $translate);
		}

		return $translate;
	}


	#===============================================
	public function replace_wildcards($string, $event, $parent_id) {
		$get 				= $this->translate();
		$EM_Main 			= new EM_Event($parent_id, 'post_id');
		$wildcards 			= array(
			'%MAIN_ID%' 		=> $parent_id,
			'%MAIN_NAME%'		=> $EM_Main->event_name,
			'%MAIN_DATE%' 		=> date_i18n($get['date_format'], strtotime($EM_Main->start_date)),
			'%MAIN_START'		=> date_i18n($get['time_format'], strtotime($EM_Main->start_time)),
			'%MAIN_END%' 		=> date_i18n($get['time_format'], strtotime($EM_Main->end_time)),
			'%SUB_LABEL%'		=> $get['item'],
			'%SUB_COUNT%'		=> $event['day'],
			'%SUB_DATE%' 		=> date_i18n($get['date_format'], strtotime($event['date'])),
			'%SUB_START%' 		=> date_i18n($get['time_format'], strtotime($event['start'])),
			'%SUB_END%'			=> date_i18n($get['time_format'], strtotime($event['end'])),
			'%LAST_DATE%' 		=> date_i18n($get['date_format'], strtotime($this->get_last_date($parent_id))),
		);

		foreach( $wildcards as $wildcard => $value ) {
			$string = str_replace( $wildcard, $value, $string );
		}
		return $string;
	}


	#===============================================
	public function get_last_date( $parent_id ) {
		$sub_events = get_post_meta($parent_id, '_ongoing_events', true);
		$last_day 	= array_key_last( $sub_events );
		$last_date 	= $sub_events[$last_day]['date'];
		return $last_date;
	}


	#===============================================
	public function show_settings_helper($type) {
		$url 		= esc_url_raw( admin_url() . 'edit.php?post_type=event&page=events-manager-options#formats' );
		$link 		= sprintf('<a href=%1$s target="_blank">%2$s</a>', $url, __('Events Manager Settings', $this->text) );
		$message 	= wp_kses_allowed( sprintf( _x('This plugin uses the %1$s in your %2$s.', 'This plugin uses the formatting in your settings', $this->text),
			esc_html__($type), $link ) );
		return $message;
	}


	#===============================================
	public function add_hierarchy($args) {
		if( !$args['hierarchical'] ) {
			$args['hierarchical'] = true;
			flush_rewrite_rules();
			return $args;
		}
		return $args;
	}


	#===============================================
	public function hide_actions($actions, $post) {
		if( isset($_REQUEST['post_type']) && 'event' == $_REQUEST['post_type']) {
			if( !empty($post->post_parent) ) {
				unset( $actions['trash'] );
				unset( $actions['delete'] );
				unset( $actions['duplicate'] );
			}
		}
		return $actions;
	}


	#===============================================
	public function add_descripton_to_column_output( $column ) {
		if( isset($this->options['settings']['admin_info']) && $this->options['settings']['admin_info'] != 'no' ) {
			global $post, $EM_Event;
			switch( $column ) {
				case 'extra':
					if( $this->is_ongoing() ) {
						$options 	= $this->options;
						$get 		= $this->translate();
						if( $this->has_parent() ) {
							$parent_id = $this->get_parent_id();
							$parent_post = get_post($parent_id);
							echo sprintf( '&#x1F517;&nbsp; %s %s &rArr; <strong><a href="%s">%s</a></strong>.<br>',
								ucfirst($get['item']),
								get_post_meta($post->ID, '_ongoing_day', true),
								get_edit_post_link( $parent_post->ID),
								$parent_post->post_title
							);
						}
						else {
							echo esc_html__('This is a Main Event of an ongoing event series.', $this->text) .' &#x1F517;<br>';
						}
					}
				break;
			}
		}
	}


	#===============================================
	public function construct_query_from_meta( $meta, $id ) {
		// Prepare meta inputs for correct array.
		$sql = array();
		foreach($meta as $meta_key => $meta_vals) {
			foreach($meta_vals as $meta_val) {
				$column = ltrim($meta_key, '_');
				$sql[$column] = $meta_val;
			}
		}

		// Unset values, because these are not used in the SQL Table.
		unset($sql['event_start_local']);
		unset($sql['event_end_local']);
		unset($sql['thumbnail_id']);

		// Construct SQL Query.
		$table = EM_EVENTS_TABLE;
		foreach($sql as $column => $value ) {
			$value = "'$value'";
			$updates[] = "$column = $value";
		}
		$implode 	= implode(', ', $updates);
		$query 		= "UPDATE {$table} SET ". $implode ." WHERE post_id = '{$id}'";
		return $query;
	}


	#===============================================
	public function restore_tickets_to_main_event( $new_post_id ) {
		global $wpdb;
		$parent_id 			= $this->get_parent_id();
		$parent_event_id 	= get_post_meta($parent_id, '_event_id', true);
		$events_table 		= EM_EVENTS_TABLE;
		$tickets_table 		= EM_TICKETS_TABLE;
		$new_event_id 		= $wpdb->get_var("SELECT `event_id` FROM {$events_table} WHERE `post_id` = '{$new_post_id}'");
		$wrong_ids			= $wpdb->get_results("SELECT `ticket_id` FROM {$tickets_table} WHERE `event_id` = '{$new_event_id}'", ARRAY_A);

		if( count( (array) $wrong_ids) > 0 ) {
			foreach( $wrong_ids as $wrong ) {
				$right_id = $wpdb->query("UPDATE {$tickets_table} SET `event_id` = '{$parent_event_id}' WHERE `ticket_id` = '{$wrong['ticket_id']}'");
			}
		}
		return;
	}


	#===============================================
	public function maybe_delete_subevents( $parent_id ) {
		global $post_type, $post;
		if( $post_type != 'event' ) {
			return;
		}

		// Get subevents.
		$sub_events = $this->get_sub_events();
		if( !empty($sub_events) ) {
			foreach( $sub_events as $sub ) {
				wp_delete_post( $sub->post_id, true ); 	// Bypass Trash.
			}
		}
	}

} // End class.
endif;


#===============================================
if( !function_exists('sort_events_by_date') ) {
	function sort_events_by_date($a, $b) {
		return strcmp($a["date"], $b["date"]);
	}
}
