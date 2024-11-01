<?php
if( !defined('ABSPATH') ) exit;

if( !class_exists('EM_Ongoing_Events_iCal') ) :
Class EM_Ongoing_Events_iCal extends EM_Ongoing_Events_Public {


	#===============================================
	public function define_placeholders( $sections = array() ) {
		$url 	= admin_url('edit.php?post_type=event&page=events-manager-options#emails');
		$link 	= sprintf('<a href="%s" target="_blank">%s</a>', $url, __em('Confirmed booking email') );
		$sections['placeholders'] = array(
			'id' 		=> 'placeholders',
			'label'		=> __em('Placeholders'),
			'fields'	=> array(
				array(
					'id' 		=> 'table',
					'label'		=> '#_ONGOINGEVENTS',
					'type'		=> 'span',
					'default'	=> sprintf( __('To display the Ongoing Events Table in your %s, you can use the placeholder %s.', $this->text), $link, '<code>#_ONGOINGEVENTS</code>'),
				),
				array(
					'id'		=> 'ical',
					'label'		=> '#_ONGOINGICAL',
					'type' 		=> 'span',
					'default'	=> sprintf( __('By adding the placeholder %s to end of your %s, a .ics file will be added to the email.', $this->text), '<code>#_ONGOINGICAL</code>', $link ) .'<ul><li>'. __('The attachment will only contain the events in its own Ongoing Series and will only be added if the booking is indeed an Ongoing Event.', $this->text) .'</li><li>'. __('Each item will show the Event Name, Event Location, Event Excerpt and the start & end time per event.', $this->text) .'</li><li>'. __('Depending on the calendar app used, the item will show a map of the location.', $this->text) .'</li></ul>',
				),
				array(
					'id' 		=> 'link',
					'label' 	=> '#_ONGOINGLINK',
					'type'		=> 'span',
					'default'	=> __('This placeholder will output a link to manually download the ongoing .ics file.', $this->text) .'<br>'. stonehenge()->show_filter('em_ongoing_events_link', array('$result', '$url', '$string') ),
				),
			)
		);
		return $sections;
	}


	#===============================================
	private function get_ical_variables( $EM_Event ) {
		$date_format 	= 'Ymd\THis\Z';
		$tz 			= wp_timezone_string();
		$EM_Location 	= new EM_Location( $EM_Event->location_id );
		$address 		= stonehenge()->localize_em_location( $EM_Location, true, false );
		$address		= str_replace('<br>', '\, ', $address);
		$address		= str_replace(PHP_EOL, '\, ', $address);

		$result = array(
			'uid' 			=> $EM_Event->event_id . '@' . get_bloginfo('name'),
			'summary' 		=> $EM_Event->output("#_EVENTNAME"),
			'description'	=> sanitize_textarea_field(  $EM_Event->post_excerpt ),
			'url' 			=> $EM_Event->output("#_EVENTURL"),
			'start'			=> $tz .':'. date($date_format, strtotime($EM_Event->start_date .' '. $EM_Event->start_time)),
			'end' 			=> $tz .':'. date($date_format, strtotime($EM_Event->end_date .' '. $EM_Event->end_time)),
			'categories'	=> $EM_Event->output("#_CATEGORYNAME"),
			'timestamp' 	=> date($date_format, time()),
			'loc_name'		=> $EM_Location->location_name,
			'loc_full' 		=> $address,
			'loc_geo'		=> $EM_Location->location_latitude .';'. $EM_Location->location_longitude,
			'loc_apple'		=> $EM_Location->location_latitude .','. $EM_Location->location_longitude,
		);
		return $result;
	}


	#===============================================
	public function create_ical( $EM_Event = false, $attachment = false ) {
		global $EM_Ongoing;
		if( !$EM_Event ) {
			$event_id 	= trim(sanitize_text_field( $_GET['id'] ));
			$EM_Event 	= new EM_Event( $event_id );
		}

		// Is this an Ongoing Series?
		$parent_id = $this->is_ongoing_series( $EM_Event );
		if( !$parent_id ) {
			return;
		}

		// Make sure we start with the Main Event.
		if( $parent_id != $EM_Event->post_id ) {
			$EM_Event = new EM_Event( $parent_id, 'post_id');
		}

		// Start output.
		$ical = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//em-ongoing-events//1.0//EN
CALSCALE:GREGORIAN
";

		// Add Main Event to iCal.
		$get 	= $this->get_ical_variables( $EM_Event );
		$ical 	.= "BEGIN:VEVENT
UID:{$get['uid']}
DTSTART;TZID={$get['start']}
DTEND;TZID={$get['end']}
DTSTAMP:{$get['timestamp']}
URL:{$get['url']}
SUMMARY:{$get['summary']}
DESCRIPTION:{$get['description']}
CATEGORIES:{$get['categories']}
LOCATION:{$get['loc_full']}
GEO:{$get['loc_geo']}
X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-ADDRESS={$get['loc_full']};X-APPLE-RADIUS=100;X-TITLE={$get['loc_name']}:geo:{$get['loc_apple']}
END:VEVENT
";

		// Add sub events to iCal.
		$sub_events = $this->get_sub_events($parent_id);
		foreach( $sub_events as $sub ) {
			// Sub Event variables.
			$EM_Event 	= new EM_Event( $sub->ID, 'post_id' );
			$get 		= $this->get_ical_variables( $EM_Event );

			$ical 		.= "BEGIN:VEVENT
UID:{$get['uid']}
DTSTART;TZID={$get['start']}
DTEND;TZID={$get['end']}
DTSTAMP:{$get['timestamp']}
URL:{$get['url']}
SUMMARY:{$get['summary']}
DESCRIPTION:{$get['description']}
CATEGORIES:{$get['categories']}
LOCATION:{$get['loc_full']}
GEO:{$get['loc_geo']}
X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-ADDRESS={$get['loc_full']};X-APPLE-RADIUS=100;X-TITLE={$get['loc_name']}:geo:{$get['loc_apple']}
END:VEVENT
";
		}

		// Close iCal.
		$ical .= "END:VCALENDAR";

		$ical_name 	= sprintf('ongoing-event-%s.ics', $EM_Event->event_id);
		$ical_path	= stonehenge()->pdf_file_path( 'ongoing-events' );
		$ical_file	= $ical_path . $ical_name;

		if( $attachment ) {
			// Save iCal.
			file_put_contents($ical_file, $ical);
			return $ical_file;
		}
		else {
			// Make iCal downloadable.
			header('Content-Type: text/calendar; charset=utf-8');
			header('Content-Disposition: attachment; filename='.$ical_name);
			echo $ical;
			exit();
		}
	}


} // End Class.
endif;