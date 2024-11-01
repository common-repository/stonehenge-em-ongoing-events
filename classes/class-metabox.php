<?php
if( !defined('ABSPATH') ) exit;

if( !class_exists('EM_Ongoing_Events_Metabox') ) :
Class EM_Ongoing_Events_Metabox extends EM_Ongoing_Events_Admin {


	#===============================================
	public function create_meta_box( $post_type ) {
		if( $post_type === 'event' && current_user_can('publish_events', 'edit_events', 'edit_others_events') ) {
			add_meta_box('ongoing_event', esc_html__('Ongoing Event', $this->text), array($this, 'show_meta_box'), 'event', 'normal', 'high');
		}
	}


	#===============================================
	public function show_meta_box( $post ) {
		global $EM_Event;

		// Check for Recurring Event first.
		if( $EM_Event->is_recurring() || $EM_Event->is_recurrence() ) {
			echo '<p>'. __('This is a Recurring Event.', $this->text) .' '. __('It cannot also be an ongoing event. You would have to detach it first.', $this->text) .'</p>';
			return;
		}

		// Set variables.
		$options 		= $this->options;
		$get 			= $this->translate();
	    $count 			= 2; // Always start at 2 -> Main Event = 1.
		$parent_id 		= $this->is_ongoing_series($EM_Event);
		$date_format 	= stonehenge()->get_date_format();
		$time_format 	= stonehenge()->get_time_format();

		#===============================================
		# 	Maybe show admin notice.
		#===============================================
		$notice = $type = false;

		// Is this a sub event?
		if( $this->has_parent() ) {
			$ongoing_day	= get_post_meta($post->ID, '_ongoing_day', true);
			$edit_url 		= esc_url_raw( get_edit_post_link( $parent_id ) );
			$notice 		= '<strong>'. sprintf( _x('This is %1$s %2$s of <a href=%3$s>%4$s</a>.', 'This is item # of Main Event', $this->text),
				esc_html($get['item']), esc_html($ongoing_day), $edit_url, esc_html(get_the_title($parent_id)) ) .'</strong><br>'.  esc_html__('Sub Event Details and Bookings are maintained in the Main Event.', $this->text);
			echo $notice;

			// Protect this events details.
			?><script>
				jQuery('.em-date-start, .em-date-end, #start-time, #end-time, #em-time-all-day, #event-timezone').attr('disabled', true);
				jQuery('#event-date-explanation').html('<?php echo $get['maintain']; ?>').css('color', '#e14d43');
			</script>
			<style>.submitdelete, .deletion, .misc-event-duplicate-link, #em-event-bookings, #commentstatusdiv, #commentsdiv, #slugdiv, #edit-slug-buttons {display:none !important;}</style>
			<?php
			return;
		}

		// Is this is main event?
		if( $this->is_parent() ) {
			$notice	= sprintf( '<strong>%s</strong><br>%s %s',
		 		__('This is the main event of an ongoing event series.', $this->text),
		 		__('Dates, times and booking are maintained here.', $this->text),
		 		__('If you delete this event, all linked sub-events will be deleted as well.', $this->text)
		 	);
		 	$type = 'main';
		}

		// Check for shortcode.
		if( $parent_id && strpos($EM_Event->post_content, '[ongoing_events]') === false ) {
			$notice .= ' '. sprintf( __('You may want to use the shortcode %s in your content.', $this->text), '<code>[ongoing_events]</code>');
		}

		if( $notice ) {
			stonehenge()->show_admin_notice($notice, 'info');
		}


		#===============================================
		# 	Start the Form.
		#===============================================
		$this->load_admin_assets();
		stonehenge()->load_datepicker();
		stonehenge()->load_timepicker();

		wp_nonce_field( plugin_basename( __FILE__ ), '_em_ongoing_nonce' );
		$checked = get_post_meta($post->ID, '_ongoing_check', true)  != 'yes' ? null : ' checked="checked"';

		?>
		<p id="ongoing_ask"><?php echo $get['ask']; ?>&nbsp;&nbsp;
		<label class="flip">
			<input type="checkbox" id="_ongoing_check" name="_ongoing_check" value="yes" <?php echo $checked; ?>>
			<span data-unchecked="<?php echo esc_attr( __wp('No'), ENT_QUOTES); ?>" data-checked="<?php echo esc_attr( __wp('Yes'), ENT_QUOTES); ?>"></span>
		</label>
		</p>

		<?php
		$sub_events 	= $this->get_sub_events();
		$hidden			= $sub_events ? null : 'style="display:none;"';
		?>
		<div id="ongoing-events" <?php echo $hidden; ?>>
			<table class="ongoing-events-admin-table">
				<thead>
					<th scope="col"></th>
					<th scope="col"><?php echo esc_html($get['date']); ?></th>
					<th scope="col"><?php echo esc_html($get['time']); ?></th>
					<th scope="col"></th>
				</thead>
				<tbody>
					<?php
					if( $sub_events ) {
						foreach( $sub_events as $event ) {
							$start_date = date($date_format, strtotime( get_post_meta($event->ID, '_event_start_date', true) ));
							$start_time = date($time_format, strtotime( get_post_meta($event->ID, '_event_start_time', true) ));
							$end_time 	= date($time_format, strtotime( get_post_meta($event->ID, '_event_end_time', true) ));
							$edit 		= get_edit_post_link( $event->ID );

							echo "<tr><td data-label='#' class='left'><strong class='count'>{$get['item']} {$count}</strong><input type='hidden' name='_ongoing_events[{$count}][ID]' autocomplete='off' value='{$event->ID}'><input type='hidden' name='_ongoing_events[{$count}][day]' autocomplete='off' value='{$count}'></td><td data-label='{$get['date']}' class='center'><input type='text' name='_ongoing_events[{$count}][date]' class='pickadate' size='22' value='{$start_date}'></td><td data-label='{$get['time']}' class='center'><input type='text' name='_ongoing_events[{$count}][start]' class='pickatime start-time' size='8' autocomplete='off' value='{$start_time}'> - <input type='text' name='_ongoing_events[{$count}][end]' class='pickatime end-time' size='8' autocomplete='off' value='{$end_time}'></td><td class='left'><span class='button-remove ongoing-remove' title='{$get['remove']}'></span><span class='button-add ongoing-add' title='{$get['add']}'></span><a href='{$edit}' target='_blank' class='edit-link'><span class='button-edit' title='{$get['edit']}'></span></a></td>";
							$count = $count + 1;
						}
					}
					else {
						echo "<tr><td data-label='#' class='left'><strong class='count'>{$get['item']} {$count}</strong><input type='hidden' name='_ongoing_events[{$count}][day]' autocomplete='off'></td><td data-label='{$get['date']}' class='center'><input type='text' name='_ongoing_events[{$count}][date]' class='pickadate' size='22'></td><td data-label='{$get['time']}' class='center'><input type='text' name='_ongoing_events[{$count}][start]' class='pickatime start-time' size='8' autocomplete='off'> - <input type='text' name='_ongoing_events[{$count}][end]' class='pickatime end-time' size='8' autocomplete='off'></td><td class='left'><span class='button-remove ongoing-remove' title='{$get['remove']}'></span><span class='button-add ongoing-add' title='{$get['add']}'></span></td>";
					}
					?>
				</tbody>
			</table>
		</div>
		<?php
	}


	#===============================================
	public function validate_ongoing_input( $result, $EM_Event ) {
		$get = $this->translate();
		if( $_POST['post_type'] === 'event' && isset($_POST['_ongoing_check']) && isset($_POST['_ongoing_events']) ) {
			foreach( $_POST['_ongoing_events'] as $events => $event ) {
				foreach( $event as $key ) {
					if( strtotime($event['start']) > strtotime($event['end']) ) {	// Use strtotime because of AM/PM.
						$message = null;
						$message = sprintf('<strong>%1$s %2$s:</strong> %3$s',
							$get['item'], $event['day'], __('The end time cannot before the start time.', $this->text)
						);
						$EM_Event->add_error( $message );
						return false;
					}
				}
			}
		}
	  return $result;
	}


	#===============================================
	public function save_meta_box_data( $post_id ) {
		if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {	return; }

		// Sub events do not have this nonce.
		if( !isset($_POST['_em_ongoing_nonce']) ) { return; }
		if( !wp_verify_nonce($_POST['_em_ongoing_nonce'], plugin_basename( __FILE__ )) ) { return; }

 		// Check if Ongoing Checkbox is checked.
 		if( !isset($_POST['_ongoing_check']) || $_POST['_ongoing_check'] != 'yes' || !isset($_POST['_ongoing_events']) ) {

 			// First check of there are sub-events created in the database already.
 			$existing_events = $this->get_sub_events();
 			if( !empty($existing_event) ) {
	 			foreach( $existing_events as $existing_event ) {
					wp_delete_post( $existing_event->ID, true); //Bypass Trash.
	 			}
 			}
 			// Delete meta keys, just in case.
			delete_post_meta($_POST['post_ID'], '_ongoing_events');
			delete_post_meta($_POST['post_ID'], '_ongoing_check');
			return;
 		}
	 	else {
			$count = 2; // Always start at 2 => Main Event = 1.
			$cleaned_events = array();

			// First, sort & clean the incoming data.
			$meta_events = $_POST['_ongoing_events'];
			usort($meta_events, 'sort_events_by_date');

			foreach( $meta_events as $events => $event ) {
				if( strtotime($event['start']) < strtotime($event['end']) ) {
					$cleaned_events[$count]['day'] 		= sanitize_text_field( (int) $count); // Always fill with new to keep it clean.
					$cleaned_events[$count]['date'] 	= sanitize_text_field( date('Y-m-d', strtotime($event['date'])) );
					$cleaned_events[$count]['start'] 	= sanitize_text_field( date('H:i:s', strtotime($event['start'])) );
					$cleaned_events[$count]['end'] 		= sanitize_text_field( date('H:i:s', strtotime($event['end'])) );

					if( isset($event['ID']) && !empty($event['ID']) ) {
						$cleaned_events[$count]['ID']  	= sanitize_text_field( (int) $event['ID']);
					}
					$count = $count + 1;
				}
			}
			update_post_meta($_POST['post_ID'], '_ongoing_events', $cleaned_events);
			update_post_meta($_POST['post_ID'], '_ongoing_check', sanitize_key($_POST['_ongoing_check']));
			return;
		}
		return;
	}


	#===============================================
	public function process_ongoing_values( $parent_id, $post ) {
		// Only continue if this the Main Event of an Ongoing Series. (Sub events do not have the nonce.)
		if( $post->post_type != 'event' || $post->post_status != 'publish' || !isset( $_REQUEST['_em_ongoing_nonce'] ) ) {
			return;
		}

		// If this is not a main event.
		$toggle = get_post_meta($post->ID, '_ongoing_check', true);
		if( !isset($toggle) || empty($toggle) || $toggle != 'yes' ) {
			// Delete post meta, just to keep things clean.
			delete_post_meta($parent_id, '_ongoing_check');
			delete_post_meta($parent_id, '_ongoing_events');

			// Are there Left-Over?
			$left_overs = $this->get_sub_events($parent_id);
			if( $left_overs ) {
				foreach( $left_overs as $left_over ) {
					wp_delete_post($left_over->ID, true); 	// Bypass trash.
				}
			}
			// All done.
			return;
		}

		if( $toggle === 'yes' ) {
			$ongoing_ids = $existing_ids = array();

			$ongoing_events 	= get_post_meta($parent_id, '_ongoing_events', true);
			$existing_events 	= $this->get_sub_events($parent_id);

			// Create an array with ID's of Sub Events in the Main Event.
			if( !empty($ongoing_events) ) {
				foreach( $ongoing_events as $event ) {
					if( isset($event['ID']) && !empty($event['ID']) && is_numeric($event['ID']) ) {
						$ongoing_ids[] = $event['ID'];
					}
				}
			}

			// Create an array with ID's of existing Sub Events.
			if( !empty($existing_events) ) {
				foreach($existing_events as $existing_event) {
					$existing_ids[] = $existing_event->ID;
				}
			}

			// First, delete left-overs to free-up slugs.
			$to_delete = array_diff($existing_ids, $ongoing_ids);
			foreach( $to_delete as $id ) {
				wp_delete_post( $id, true ); 	// Bypass trash.
			}

			// What needs to be done?
			$processed_events = array();
			foreach( $ongoing_events as $event ) {
				$day = $event['day'];
				$processed_events[$day] = $event;
				if( isset($event['ID']) && !empty($event['ID']) ) {
					$this->update_sub_event($event, $parent_id);
				}
				else {
					$processed_events[$day]['ID'] = $this->create_sub_event($event, $parent_id);
				}
			}
			// Save new ID's to the main event.
			update_post_meta($parent_id, '_ongoing_events', $processed_events);
		}
		return;
	}


	#===============================================
	public function create_sub_event( $event, $parent_id ) {
		// Are we really creating a new event? => Double check, again.
		if( isset($event['ID']) || !empty($event['ID']) ) {
			return;
		}

		$get 			= $this->translate();
		$parent_title 	= get_the_title($parent_id);
		$new_title 		= "{$parent_title} ({$get['item']} {$event['day']})";
		$new_slug 		= sanitize_title_for_query( $new_title );
		$EM_Parent 		= new EM_Event( $parent_id, 'post_id' );
		$current_UTC 	= date('Y-m-d H:i:s', time() );
		$current_local 	= stonehenge()->utc_to_local($current_UTC);

		if( !$this->get_event_by_slug($new_slug) ) {
			$content 	= html_entity_decode( $get['sub_content'] );
			$content 	= str_replace( '%MAIN_ID%', $parent_id, $content ); 	// Required for the [event] shortcode.
			$excerpt 	= html_entity_decode( $get['sub_excerpt'] );
			$excerpt  	= apply_filters('em_ongoing_wildcards', $excerpt, $event, $parent_id);

			// First, create a new EM_Event Object.
			$Main 			= new EM_Event($parent_id, 'post_id');
			$New			= $Main;

			// Reset certain data.
			if( get_option('dbem_categories_enabled') ) $New->get_categories(); // Before we remove event/post ID's.
			$New->ID 				= null;
			$New->post_id 			= null;
			$New->event_id 			= null;
			$New->post_name			= $new_title;
			$New->name 				= $new_title;
			$New->event_name 		= $new_title;
			$New->event_slug 		= $new_slug;
			$New->slug 				= $new_slug;
			$New->post_content 		= $content;
			$New->post_excerpt 		= $excerpt;
			$New->event_start_date	= $event['date'];
			$New->start_date		= $event['date'];
			$New->event_end_date	= $event['date'];
			$New->end_date			= $event['date'];
			$New->event_start_time	= $event['start'];
			$New->start_time		= $event['start'];
			$New->event_end_time	= $event['end'];
			$New->end_time			= $event['end'];
			$New->event_all_day		= 0;
			$New->event_rsvp 		= 0;
			$New->rsvp 				= 0;
			$New->post_parent 		= $parent_id;
			$New->event_parent 		= $parent_id;
			$New->force_status 		= 'publish';
			$New->save();

			$new_post_id =  $New->post_id;

			// Make the new post hierarchical.
			$post_array = array(
				'ID' 				=> $new_post_id,
				'post_parent' 		=> $parent_id,
				'post_status' 		=> 'publish',
				'comment_status'	=> 'closed',
				'ping_status'		=> 'closed',
				'meta_input' 		=> array(
					'_ongoing_day' 		=> $event['day'],
				),
				'menu_order'		=> $event['day'],
				'post_modified' 	=> $current_local,
				'post_modified_gmt'	=> $current_UTC,
			);
			wp_update_post( $post_array );

			// Add extra meta to Post.
			$meta['_event_start_date'][0] 	= $event['date'];
			$meta['_event_end_date'][0] 	= $event['date'];
			$meta['_event_start_time'][0] 	= $event['start'];
			$meta['_event_end_time'][0] 	= $event['end'];
			$meta['_event_start'][0] 		= stonehenge()->local_to_utc( $event['date'] .' '. $event['start'], 'Y-m-d H:i:s');
			$meta['_event_end'][0] 			= stonehenge()->local_to_utc( $event['date'] .' '. $event['end'], 'Y-m-d H:i:s');
			$meta['_event_all_day'][0] 		= 0;
			$meta['_event_timezone'][0] 	= get_post_meta($parent_id, '_event_timezone', true);
			$meta['_event_rsvp'][0] 		= 0;
			$meta['_event_rsvp'][0] 		= 0;
			$meta['_event_start_local'][0] 	= $event['date'] .' '. $event['start'];
			$meta['_event_end_local'][0] 	= $event['date'] .' '. $event['end'];
			$meta['_thumbnail_id'][0] 		= get_post_meta($parent_id, '_thumbnail_id', true);

			// Insert post meta for new event.
			foreach($meta as $meta_key => $meta_vals) {
				foreach($meta_vals as $meta_val) {
				    update_post_meta($new_post_id, $meta_key, $meta_val);
				}
			}

			// Clean the new Event Object.
			$remove_meta = array( '_event_rsvp_date', '_event_rsvp_time', '_event_rsvp_spaces', '_event_spaces', '_ongoing_events', '_ongoing_check', '_em_active_gateways', '_event_all_day', '_recurrence', '_recurrence_days' );

			foreach( $remove_meta as $key ) {
				delete_post_meta($new_post_id, $key);
			}

			// Update the SQL for this event -> Dates and times tend to keep on the old values.
			global $wpdb;
			$query = $this->construct_query_from_meta( $meta, $new_post_id );
			$sql = $wpdb->query($query);

			// Restore Tickets to Main Event.
			$this->restore_tickets_to_main_event( $new_post_id );

			// Save the new Post ID in the Main Event Meta.
			return $new_post_id;
		}
		return;
	}


	#===============================================
	public function update_sub_event( $event, $parent_id ) {
		// $event = single array of one sub event with full data. Check with database to update both $post & $EM_Event.
		if( !isset($event['ID']) || empty($event['ID']) ) {		// A Double check never hurts.
			return;
		}

		$get 			= $this->translate();
		$id 			= $event['ID']; // Just easier.
		$timezone 		= get_post_meta($parent_id, '_event_timezone', true);
		$current_title 	= get_the_title($id);
		$parent_title 	= get_the_title($parent_id);
		$new_title 		= "{$parent_title} ({$get['item']} {$event['day']})";
		$post_title 	= $new_title != $current_title ? $new_title : $current_title;
		$post_slug 		= sanitize_title_for_query( $post_title );
		$excerpt 		= html_entity_decode( $get['sub_excerpt'] );
		$excerpt  		= apply_filters('em_ongoing_wildcards', $excerpt, $event, $parent_id);

		// Are there changes?
		$current 	= array(
			'day' 		=> get_post_meta($id, '_ongoing_day', true),
			'date' 		=> get_post_meta($id, '_event_start_date', true),
			'start' 	=> get_post_meta($id, '_event_start_time', true),
			'end' 		=> get_post_meta($id, '_event_end_time', true),
			'slug' 		=> get_post_field( 'post_name', $id ),
		);

		$new 		= array(
			'day' 		=> $event['day'],
			'date' 		=> $event['date'],
			'start' 	=> $event['start'],
			'end' 		=> $event['end'],
			'slug' 		=> $post_slug,
		);

		$changes = array_diff($new, $current);
		if( !empty($changes) ) {
			$post_array = array(
				'ID' 			=> $id,
				'post_title' 	=> $post_title,
				'post_name' 	=> $post_slug,
				'post_excerpt' 	=> $excerpt,
				'menu_order'	=> $event['day'],
				'meta_input' 		=> array(
					'_ongoing_day' 		=> $event['day'],
				),
			);
			wp_update_post( $post_array);

			$Sub_Event = new EM_Event( $id, 'post_id');
			$Sub_Event->post_name			= $post_slug;
			$Sub_Event->name 				= $post_title;
			$Sub_Event->event_name 			= $post_title;
			$Sub_Event->event_slug 			= $post_slug;
			$Sub_Event->slug 				= $post_slug;
			$Sub_Event->post_excerpt 		= $excerpt;
			$Sub_Event->event_start_date	= $event['date'];
			$Sub_Event->start_date			= $event['date'];
			$Sub_Event->event_end_date		= $event['date'];
			$Sub_Event->end_date			= $event['date'];
			$Sub_Event->event_start_time	= $event['start'];
			$Sub_Event->start_time			= $event['start'];
			$Sub_Event->event_end_time		= $event['end'];
			$Sub_Event->end_time			= $event['end'];
			$Sub_Event->event_all_day		= 0;
			$Sub_Event->event_rsvp 			= 0;
			$Sub_Event->rsvp 				= 0;
			$Sub_Event->post_parent 		= $parent_id;
			$Sub_Event->event_parent 		= $parent_id;
			$Sub_Event->save();
		}

		// Just in case...
		$this->restore_tickets_to_main_event( $id );
		return;
	}

} // End class.
endif;
