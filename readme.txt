=== Restrict Registration By Email for WP-Members ===
Contributors: stevish
Donate link: http://ntm.org/give
Tags: email, registration, verify email, wpmembers, blacklist, whitelist
Requires at least: 3.5
Tested up to: 3.9
Stable tag: 2.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Restricts registration to email addresses listed within the options file. Assumes WP native registration is turned off. 

== Description ==

Restricts registration to email addresses listed on the options page. Assumes WP Members is installed and active, and WP native registration is turned off. Includes both whitelist (accepted emails) and blacklist (blocked emails). The blacklist will override entries in the whitelist.

If you'd like to present an issue or contribute a fix, the Github repository is located at https://github.com/newtribesmission/NTM-WPMem-Restrict-Registration 

== Installation ==

1. Make sure the WP-Members plugin is installed and activated
1. Turn the WordPress native Registration off (users can still register through WP-Members)
1. Upload the `/ntm-wpmem-restrict-registration` folder to the `/wp-content/plugins/` directory
1. **Set up the options in Dashboard > Users > Pre-Approve** (Before you do this, all registrations will be blocked)
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How do I set up the options? =

As of 2.0, from the dashboard, find "Users > Pre-Approve" and follow the directions there.

= Will this work without WP Members activated? =

For security, this plugin will stop any registration attempts from unapproved email address regardless of how the registration is attempted. However, the error messages and redirects aren't guaranteed to work with the WP naative registration.

== Changelog ==

= 2.0 =
* Removed options.php in favor of placing options in the database
* Added admin panel to manage options

= 1.4.1 =
* Added the [ntmrr_registration_error] shortcode for use on redirect landing page

= 1.4 =
* Initial WP Release
* Moved/Renamed files
* Added WordPress Friendly readme.txt
* Fix "plugin breaks everything if options.php is missing"

= 1.3 =
* Initial Public release

== Upgrade Notice ==

= 2.0 =
Uses better options setup! Update now!