<?php
if( !defined('ABSPATH') ) exit;

if( !class_exists('EM_Ongoing_Events_Admin') ) :
Class EM_Ongoing_Events_Admin extends EM_Ongoing_Events_Functions {


	#===============================================
	public function define_options( $sections = array() ) {
		$name 	= sprintf('WordPress &rArr; %1$s &rArr; %2$s', __wp('Settings'), __wp('General') );
		$url 	= admin_url('options-general.php');
		$sections['settings'] = array(
			'id' 		=> 'settings',
			'label'		=> __wp('Info'),
			'fields' 	=> array(
				array(
					'id' 		=> 'intro',
					'label' 	=> '',
					'type' 		=> 'info',
					'default' 	=> sprintf('<strong>%s</strong><br>%s', __('How do Ongoing Events work?', $this->text), __('This add-on lets you create single events that are linked together, but only require one booking for the user to attend all events. This can be useful if you are organizing classes, multi-day workshops or seminars, for example.', $this->text) .' ' .__('The idea is that you create one single event as the first day of this ongoing series. In that event you can specify the other dates and times for the underlying events.', $this->text) .'</p><p>'. __('When you publish the main event, the sub events will be created automatically. Their content will reflect that it is an ongoing sub event and link back to the booking form of the main event.', $this->text) .' '. __('If you do not want to display the predefined Ongoing Events Table, simply remove the shortcode from the page content after creation.', $this->text) .'</p><p>'. __('<b>Note:</b> Dates and times of the sub events can only be maintained in the main event.', $this->text) ) .'<p><strong>'.  __('Date & Time settings.', $this->text) .'</strong><br>'. sprintf( __('This add-on will use the formatting settings in %s to display dates and time.', $this->text), sprintf('<a href="%1$s" target="_blank">%2$s</a>', $url, $name) ) .'<br>'. __('The jQuery variant for date and time pickers will be automatically converted to have the same lay-out as their PHP equivalents, so there are no new settings to be set.', $this->text) .'</p>',
				),
				array(
					'id' 		=> 'admin_info',
					'label' 	=> __('Show Info', $this->text),
					'type' 		=> 'toggle',
					'required'	=> true,
					'helper' 	=> __('Would you like to show Ongoing Events info in the Admin Events List page?', $this->text),
					'default' 	=> 'yes',
				),
				array(
					'id' 		=> 'delete',
					'label' 	=> __('Delete Data', $this->text),
					'type' 		=> 'toggle',
					'required'	=> true,
					'helper' 	=> __('Automatically delete all data from your database when you uninstall this plugin?', $this->text),
					'default' 	=> 'no',
				),
			)
		);

		$sections['strings'] = array(
			'id' 		=> 'strings',
			'label'		=> __wp('Translations'),
			'fields' 	=> array(
				array(
					'id'		=> 'info',
					'label' 	=> '',
					'type' 		=> 'info',
					'default' 	=> __('Please specify below how you want certain fields of the sub events to be named. Your input will be used throughout this plugin in the Admin and Front-End.', $this->text),
				),
				array(
					'id' 		=> 'ask',
					'label'		=> __('Ask to Enable', $this->text),
					'type'		=> 'text',
					'required'	=> true,
					'helper'	=> __('This question will be shown in the Edit Event Page before the toggle.', $this->text),
					'default'	=> __('Does this event span over multiple days?', $this->text),
				),
				array(
					'id' 		=> 'item',
					'label' 	=> sprintf( __('Label for %s', $this->text), __('Item', $this->text) ),
					'type' 		=> 'text',
					'default' 	=> __('Day'),
					'required' 	=> true,
					'helper' 	=> __('This indicates the Sub Event. Use something like "Day" or "Part".', $this->text),
				),
				array(
					'id' 		=> 'date',
					'label' 	=> sprintf( __('Label for %s', $this->text), __wp('Date') ),
					'type' 		=> 'text',
					'default' 	=> __wp('Date'),
					'required' 	=> true,
					'helper' 	=> $this->show_settings_helper('Date Format'),
				),
				array(
					'id' 		=> 'time',
					'label' 	=> sprintf( __('Label for %s', $this->text), __wp('Time') ),
					'type' 		=> 'text',
					'default' 	=> __wp('Time'),
					'required' 	=> true,
					'helper' 	=> $this->show_settings_helper('Time Format'),
				),

				array(
					'id' 		=> 'shortcode_title',
					'label' 	=> sprintf( __('Label for %s', $this->text), __wp('Table') ),
					'type' 		=> 'text',
					'default' 	=> 'Related Events',
					'required' 	=> true,
					'helper' 	=> __('This is the Header of the [ongoing-events] table.', $this->text),
				),
				array(
					'id' 		=> 'booking_notice',
					'label' 	=> __('Booking Notice', $this->text),
					'type' 		=> 'text',
					'default' 	=> 'Booking a single ticket will grant you access to all event dates listed above.',
					'required' 	=> true,
					'helper' 	=> __('This message is shown below the Ongoing Events Table to tell visitors they only have to book one time for all dates.', $this->text),
				),
				array(
					'id' 		=> 'wildcards',
					'label'		=> esc_html__('Wildcards', $this->text),
					'type' 		=> 'span',
					'default' 	=> sprintf('<strong>%s</strong>', esc_html__('You can use the following wildcards in sections below to target the specific information:', $this->text)) . '<br>'. $this->show_wildcards(),
				),
				array(
					'id' 		=> 'sub_notice',
					'label' 	=> __('Sub Event Indication', $this->text),
					'type' 		=> 'text',
					'default' 	=> 'This is %SUB_LABEL% %SUB_COUNT% of %MAIN_NAME%.',
					'required' 	=> true,
				),
				array(
					'id' 		=> 'sub_content',
					'label' 	=> __('Sub Event Content', $this->text),
					'type' 		=> 'editor',
					'default' 	=> '<h2>This is %SUB_LABEL% %SUB_COUNT% of %MAIN_NAME%, which starts on %MAIN_DATE%.</h2>
<p>[ongoing_events]</p>
<h3>Bookings</h3>
[event post_id="%MAIN_ID%"]#_BOOKINGFORM[/event]',
					'required' 	=> true,
				),
				array(
					'id' 		=> 'sub_excerpt',
					'label' 	=> __('Sub Event Excerpt', $this->text),
					'type' 		=> 'textarea',
					'default' 	=> 'This is %SUB_LABEL% %SUB_COUNT% of %MAIN_NAME%, starting on %MAIN_DATE%.',
					'required' 	=> true,
				),
			)
		);
		return $sections;
	}


	#===============================================
	public function show_wildcards() {
		$show 	= '<pre class="note">';
		$show 	.= sprintf('%s<br>%s<br>%s<br>%s<br>%s<br><br>%s<br>%s<br>%s<br>%s<br>%s<br>%s</pre>',
'%MAIN_ID% 			&rArr; '. esc_html__('Displays post ID of the Main Event.', $this->text),
'%MAIN_NAME% 	&rArr; '. esc_html__('Displays event name of the Main Event.', $this->text),
'%MAIN_DATE% 	&rArr; '. esc_html__('Displays start date of the Main Event.', $this->text),
'%MAIN_START%	&rArr; '. esc_html__('Displays start time of the Main Event.', $this->text),
'%MAIN_END% 		&rArr; '. esc_html__('Displays end time of the Main Event.', $this->text),
'%SUB_LABEL% 	&rArr; '. esc_html__('Displays label of each sub event (first field above).', $this->text),
'%SUB_COUNT% 	&rArr; '. esc_html__('Displays the incremented number of this specific Sub Event.', $this->text),
'%SUB_DATE% 		&rArr; '. esc_html__('Displays the start date of this specific Sub Event.', $this->text),
'%SUB_START% 	&rArr; '. esc_html__('Displays the start time of this specific Sub Event.', $this->text),
'%SUB_END% 		&rArr; '. esc_html__('Displays the end time of this specific Sub Event.', $this->text),
'%LAST_DATE%		&rArr; '. esc_html__('Displays the date of the last event in this series.', $this->text)
);
		return $show;
	}


} // End class.
endif;