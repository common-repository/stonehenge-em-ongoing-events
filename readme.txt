=== Events Manager - Ongoing Events ===
Plugin Name: 		Events Manager - Ongoing Events
Contributors:		DuisterDenHaag
Tags: 				Events Manager, multi day, chained, classes, courses
Donate link: 		https://useplink.com/payment/VRR7Ty32FJ5mSJe8nFSx
Requires at least: 	5.4
Tested up to: 		6.0
Requires PHP: 		7.3
Stable tag: 		trunk
License: 			GPLv2 or later
License URI: 		http://www.gnu.org/licenses/gpl-2.0.html


Easy to use add-on for Events Manager for organizing multiday events with just one booking / sign-up. Perfect for courses, classes, seminars, etc.


== Description ==
> Requires [Events Manager](https://wordpress.org/plugins/events-manager/) (free plugin) to be installed & activated.

Easy to use add-on for Events Manager for organizing multiday events with just one booking / sign-up.

Unlike EM's Recurring Events, EM Ongoing Events do not require separate bookings per event date. Simply create a single event with a booking form. Publish it as a normal, single event. Toggle to "Yes" and specify the other dates that this event/class/course/seminar will be continued. As soon as you click "Publish", those ongoing (linked) events will be automatically created for you.

Each sub event will link back to the original booking form. If a visitor has already booked, they will get a notice, preventing them from signing up twice. (If double bookings are not allowed, of course.)

That way you can easily maintain all bookings from the main event, while the ongoing dates are neatly displayed in your calendar as well. Your customer will only have one payment, get one conformation email and still have access to all event dates.

To prevent accidental editing and loosing the correct linked data, the sub event date & times are maintained in the main event. The bookings section is disabled (bookings are registered in the main event). You can still edit the location and featured image, etc.

You can use the placeholder #_ONGOINGEVENTS in your HTML Events Manager Booking Emails to show a table of the corresponding events.

Placing the placeholder #_ONGOINGICAL in your Booking Confirmed Email will automatically add a .ics file for the booking of that Ongoing Series.


= How this works =
1. Create a single event - the first day of your ongoing events series - with a booking form.
2. Toggle "Is this an Ongoing Event?" &rArr; "Yes".
3. The first row on the Ongoing Events Table will be shown. Edit date and times, if needed.
4. Click on + to add a new line, or - to remove one.
5. Publish your event - all the sub events will be created automatically.
6. Using the shortcode [ongoing_events] in your event content will show a table of all linked event info in every event.


= Styling =
The shortcode `[ongoing_events]` will output a table with the empty CSS class <em>ongoing-events-table</em>. If you do not define that in your own stylesheet, your theme's default table lay-out will automatically be used.

If you want to hide the "Order one ticket for all events" notice, just add this in your theme's Customizer to Additional CSS:
.ongoing-events-notice { display: none; }

Labels and strings used by this plugin can be defined in the plugin settings page. That way you are not bound to set translations.


== Feedback ==
I am open to your suggestions and feedback!
[Please also check out my other plugins, tutorials and useful snippets for Events Manager.](https://www.stonehengecreations.nl/)


== Frequently Asked Questions ==
= Is this plugin WP MultiSite compatible? =
Yes, all settings are neatly saved per blog. Please note though that cross-blog events are not possible in Events Manager.

= Are you part of the Events Manager team? =
**No, I am not!**
I am not associated with [Events Manager](https://wordpress.org/plugins/events-manager/) nor its developer, [Marcus Sykes](http://netweblogic.com/), in <em>**any**</em> way.



== Installation ==
1. Install & activate the .zip file in your WordPress Plugins Dashboard page.
2. Set your preferences in the plugin settings page.
3. Create a single event - the first day of your ongoing events series - with a booking form.
4. Toggle "Is this an Ongoing Event?" &rArr; "Yes".
5. The first row on the Ongoing Events Table will be shown. Edit date and times, if needed.
6. Click on + to add a new line, or - to remove one.
7. Publish your event - all the sub events will be created automatically.
8. Using the shortcode [ongoing_events] in your event content will show a table of all linked event info in every event.


== Screenshots ==
1. Front-end Main Event.
2. Front-end Sub Event, when a booking has already been made.
3. Ongoing Events meta box in the Main Event.
4. Admin Events List Page showing Ongoing Events.


== Localization ==
* US English (default)
* Dutch (always included in the download)

The plugin is ready to be translated, all texts are defined in the .pot file which is included in the download. Any contributions to localize this plugin are very welcome!


== Upgrade Notice ==


== Changelog ==
= 1.6.1 =
- Added: Placeholder #_ONGOINGLINK explained.
- Added: TinyMCE editor for easier styling of the sub event content.
- Confirmed compatibility with WordPress 5.5.


= 1.6.0 =
- **NEW:** Added a new placeholder: #_ONGOINGICAL. <br>It allows you to attach an .ics file of that Ongoing Series to the approved booking email.
- **NEW:** Date & Time Picker formats will automatically be converted to correspond with their PHP equivalent.
- **Changed:** Date & Time formatting now solely depend on WordPress General Settings to prevent errors with EM formatting in some cases.
- **Changed:** Admin notices are now less intrusive.
- Re-introduced creating sub events directly in new, unpublished main events. No need to save first, anymore.
- Updated the plugin options page for clearer explanations.
- Updated the .pot file for translations.
- updated the included Dutch translation file.
- Some other bug fixes.
- This add-on is now compatible with [EM Event Cancellation](https://www.stonehengecreations.nl/creations/stonehenge-em-cancellation/).

= 1.5.6 =
- Created a failsafe for the time-picker showing "17:00 PM" for users who left the EM time settings in the faulty default "H:i A".
- Bug fix dates for sub-events being shown as NaN/NaN with certain date formatting.

= 1.5.4 =
- Bug fix in updater.

= 1.5.3 =
- Some code changes.
- Confirmed compatibility with WordPress 5.4.1.
