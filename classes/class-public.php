<?php
if( !defined('ABSPATH') ) exit;

if( !class_exists('EM_Ongoing_Events_Public') ) :
Class EM_Ongoing_Events_Public extends  EM_Ongoing_Events_Metabox {


	#===============================================
	public function show_ongoing_events_table() {
		if( !$this->is_ongoing() ) {
			return;
		}

		global $EM_Event;
		if( !is_object($EM_Event) ) {
			return;
		}

		if( $this->is_ongoing() ) {
			$parent_id 		= $this->get_parent_id();
			$sub_events 	= $this->get_sub_events();
			$get 			= $this->translate();
			$time_format 	= stonehenge()->get_time_format();

			$EM_Parent 		= new EM_Event( $parent_id, 'post_id');

			// First construct line 1 for Day 1 (Main Event).
			$parent_url 	= get_permalink($parent_id);
			$start_time 	= date($time_format, strtotime($EM_Parent->event_start_time));
			$end_time 		= date($time_format, strtotime($EM_Parent->event_end_time));

			$row_one 		= $EM_Parent->output("<tr><td><strong><a href='{$parent_url}' title='#_EVENTNAME'>{$get['item']} 1</a></strong></td><td>#_EVENTDATES</td><td>{$start_time} - {$end_time}</td></tr>");

			// Start output.
			$table 	= null;
			$table 	.= '<h3>' . $get['shortcode_title'] . '</h3>';
			$table	.= '<table class="ongoing-events-table"><thead><th></th><th>'. $get['date'] .'</th><th>'. $get['time'] .'</th></thead><tbody>';
			$table .= $row_one;

			// Loop through sub-events.
			foreach( $sub_events as $sub ) {
				$EM_Sub 	= em_get_event($sub->_event_id);
				$nr 		= $EM_Sub->menu_order;
				$url 		= get_permalink($EM_Sub->post_id);
				$start_time = date($time_format, strtotime($EM_Sub->event_start_time));
				$end_time 	= date($time_format, strtotime($EM_Sub->event_end_time));
				$row 		= "<tr><td><strong><a href='{$url}' title='#_EVENTNAME'>{$get['item']} {$nr}</a></strong></td><td>#_EVENTDATES</td><td>{$start_time} - {$end_time}</td></tr>";

				$table 	.= $EM_Sub->output($row);
			}
			$table 	.= '</tbody></table>';
			$table 	.= '<p class="ongoing-events-notice">' . $get['booking_notice'] . '</p>';
			return $table;

		}
		return;
	}


	#===============================================
	public function placeholders( $result, $EM_Event, $placeholder ) {
		if( $placeholder === "#_ONGOINGEVENTS" ) {
			$result = $this->show_ongoing_events_table();
		}

	    if( $placeholder == '#_ONGOINGICAL') {
		    $ical = $this->create_ical( $EM_Event, true);
		    EM_Mailer::$attachments = array(
			    $ical,
		    );
		    $result = '<br />';
	    }

	    if ($placeholder == '#_ONGOINGLINK') {
			$url 		= add_query_arg( array('action' => 'em-ongoing-ics', 'id' => $EM_Event->event_id, 'nc' => time()), admin_url('admin-ajax.php') );
			$string 	= __('Click here to add to your calendar', $this->text);
			$result 	= sprintf( '<p><a href="%s">%s</></p>', $url, $string);
			$result 	= apply_filters('em_ongoing_events_link', $result, $url, $string);
	    }

	    return $result;
	}


	#===============================================
	public function process_wildcards( $content, $EM_Event ) {
		$parent_id = $this->is_ongoing_series( $EM_Event );

		if( $parent_id ) {
			$sub_events 	= get_post_meta($parent_id, '_ongoing_events', true);

			if( $parent_id != $EM_Event->post_id ) {
				$day 	= get_post_meta($EM_Event->post_id, '_ongoing_day', true);
				$event 	= $sub_events[$day];
			}
			else {
				// Simulate $event for Main Event.
				$event 	= array(
					'day' 	=> 1,
					'date' 	=> $EM_Event->start_date,
					'start' => $EM_Event->start_time,
					'end' 	=> $EM_Event->end_time,
					'ID'	=> $EM_Event->ID,
				);
			}
			$content 	= apply_filters('em_ongoing_wildcards', $content, $event, $parent_id);
		}
		return $content;
	}


} // End class.
endif;
