=== Plugin Name ===
Plugin Name: Row Seats
Plugin URI: http://www.rowseatsplugin.com/row-seats-plugin-information
Author: gcwebsolutions
Author URI: http://www.rowseatsplugin.com/row-seats-plugin-information
Donate link: http://www.rowseatsplugin.com/row-seats-plugin-information
Tags: row seats, booking seats, event booking, shows booking, event manager, booking events, sell seats, booking tickets, ticket booth, paypal booking
Requires at least: 3.5.1
Tested up to: 3.9.1
Stable tag: 2.34

Booking seats is easier with Row Seats plugin. This is a new solution to the increasing request to sell seats.

== Description ==

Booking seats is easier with Row Seats plugin. This is a new solution to the increasing request to sell seats. It features shopping cart features, calendar backend function, csv file upload of your seat details. Just place the shortcode in a page or post and sell your show. For more more information or extra features and functions, you can visit <a href="http://www.rowseatsplugin.com/row-seats-plugin-information">plugin page</a>.

== Installation ==

There are two ways of installing the plugin:

**From the [WordPress plugins page](http://wordpress.org/extend/plugins/)**

1. Download the plugin

2. Upload the `row-seats` folder to your `/wp-content/plugins/` directory.

3. Active the plugin in the plugin menu panel in your administration area.

**From inside your WordPress installation, in the plugin section.**

1. Search for row seats plugin

2. Download it and then activate it.

Also You can find simple tutorials with video by following the link (http://www.rowseatsplugin.com/row-seats-plugin-information)

== Frequently Asked Questions ==

How to this plugin works.

= How do I place an event =

1. You must set your event date through the Add an Event tab. The calendar works this way:

a) Month view - When you create and save a show in this view, it is considered for the whole day event.
b) Week view - You can set the time start/end of your event for any particular day.
c) Day view - same as week view above.

2. Each show created in the Show Calendar it will post in the Manage Seat section. In this menu, you need to click on the event that you want to Add Seats. Once clicked, you need to download the CSV template that will provide a sample on how to create your seat chart. Fill it out and then upload it and that's it. Your show is created. For more detail information on how to form your show go to http://www.rowseatsplugin.com/row-seats-plugin-information.

3. Reports Menu will present the details of the transaction made by the users who booked tickets.

= My page or post doesn't show up the show/event, what do I do? =

Very important to first press "Save Settings" button, even if you have not filled out all the information fields. The shortcode will work in any page or post. You can obtain the shortcode after you upload your seating chart (CSV file).  Make sure that the shortcode starts with [showseats id=X] (X represents the id number).

= I received plugin an update plugin notice from dashboard, should I update while having "live" bookings? =
It is strongly advised that you only update when bookings are past. But, if you need to update while you have 'live' bookings, then do the following:

Through FTP

• First, take a full db backup of Row Seats through your server panel (usually using phpmyadmin) should you need to restore, then deactivate the plugin.  Afterwards, upload the new version through ftp overwriting  all Row Seats files.  If you made any customization, you'll loose them.

Through WP Dashboard

• First, take a full db backup of Row Seats through your server panel (usually using phpmyadmin) should you need to restore, then deactivate the plugin.  Afterwards, delete the old plugin version and upload the new version.  If you made any customization, you'll loose them.

= How do I access extra functionality, or extra settings? =

Our plugin is always updated to our customers first.  The core will always be the latest from our website (not from this repository). For added functionality we have modules that will bring new features to Row Seats. You can visit our site for more information http://www.rowseatsplugin.com/products

= Shortcode for user email confirmation to be shown in page/post =
You can use the following shortcode [rowseatthankspage] in any page or post, this will populate the same information as you would in an email confirmation.

== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png
3. screenshot-3.png
4. screenshot-4.png
5. screenshot-5.png
6. screenshot-6.png


== Changelog ==

= 2.34 =
* Small core modification for wp-users <a title="Row Seats add-ons" href="https://www.rowseatsplugin.com/products" target="_blank">(module required)</a>

= 2.32 =
* Added Row Seats Event widget, it will show all events that are in pages
* Additional currency symbols added (Swedish & Norwegian Krone, Polish Złoty, Brazilian Reals, Malaysian Ringgit, Philippine Peso, Indian Rupee, Hungarian Forint, Russian Ruble, Swiss Franc, Czech Koruna)
* Added shortcode for email confirmation shown on any page/post, use shortcode [rowseatthankspage]

= 2.30 =
* Fixed several warnings on particular servers 
* Stop ajax refresh on page if idle (useful for saving server resources)
* Small bug fixes

= 2.28 =
* Email tags are now changed to [ ] brackets instead of < >
* Small bug with visitor booking seats, and taking them to a fictitious page after booking.

= 2.26 =
* Minor bug fixes
* Added option to re-send email booking confirmations
* Fixed strange bug of Field 'coupon_code' doesn't have a default value

= 2.22 =
* Many changes for new modules
* Overall better reporting <a title="Row Seats add-ons" href="https://www.rowseatsplugin.com/products" target="_blank">(module required)</a>
* General Admission adjustment <a title="Row Seats add-ons" href="https://www.rowseatsplugin.com/products" target="_blank">(module required)</a>
* Fixed transaction report when booking as admin (currency now shows correctly)
* Payment Settings default wording settings now populated.
* Time interval option for cart refresh enabled.

= 2.16 =
* Overall better functions
* Added support for multi languages <a title="Row Seats add-ons" href="https://www.rowseatsplugin.com/products" target="_blank">(available as an add-on)</a>
* Stage graphic omitted, opted for text so it can be translated

= 2.14 =
* Minor bugs fixes

= 2.12 =
* Fixed a small bug with Offline Payment gateway.

= 2.10 =
* Fixed a small bug with Offline Reservation in Core, it caused event dates that ended to still show as active.

= 2.9 =
* restructure core to take new payment gateways (available seperately) such as stripe, authorize.net, paypal pro
* new payment settings page added
* new currency symbol dropdown selection added.  This will add the ability to show currency symbol in the seat chart. Currently there are only 5 symbols available
* new Payment Settings submenu added
* new Payment Transactions submenu added

= 2.6 =
* chart resizing up to 70% [zoom in/out]. Handy for big charts
* csv import options (comma or semi colon dropdown selection). CSV seperator/delimiter
* updated functions file to accept new Special Price add-on

= 2.4 =
* updated jquery.blockUI.js to work with new jquery 1.10.1
* updated row-seats-functions.php to work with new jquery 1.10.1
* ready for wp 3.6 new jquery 1.10.1

= 2.2 =
* Fixed small bug with month calendar.  Shows now appear correctly in the Month Calender section. You can view each event created properly (time wise) as month/week/day view respectively
* Added seating chart alignment.  You can set left, center or right alignment
* Update files to accept new Membership + add-on

= 2.0 =
* New Core build.  You can add functional add-ons available, <a title="Row Seats add-ons" href="https://www.rowseatsplugin.com/products" target="_blank">click here</a>
* Code cleaned up
* Restructured with new db tables
* Comes with 5 color themes
* Polished look
* Can work in an page or post (full width recommended)
* Improvements in overall functions

= 1.0 =
* No page restriction (you can now post shortcode on any page/post)
* Seat orientation added (show seats numbers left/right or right/left)
* Minor improvements


= 0.9 =
* Initial release