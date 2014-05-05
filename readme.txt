=== Restrict Registration By Email for WP-Members ===
Contributors: stevish
Donate link: http://ntm.org/give
Tags: email, registration, verify email, wpmembers, blacklist, whitelist
Requires at least: 3.5
Tested up to: 3.9
Stable tag: 1.3.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Restricts registration to email addresses listed within the options file. Assumes WP native registration is turned off. 

== Description ==

Restricts registration to email addresses listed within the options file (edit options.php to add/remove/edit email addresses or domains). Assumes WP Members is installed and active, and WP native registration is turned off. Includes both whitelist (accepted emails) and blacklist (blocked emails). The blacklist will override entries in the whitelist.

== Installation ==

1. Make sure the WP-Members plugin is installed and activated
1. Turn the WordPress native Registration off (users can still register through WP-Members)
1. Upload the `/ntm-wpmem-restrict-registration` folder to the `/wp-content/plugins/` directory
1. **Set up the user variables in options_example.php and rename it to options.php** (See FAQ for more details)
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How do I set up the options? =

The options for this plugin are controlled by editing options.php (which you can copy from options_example.php). There are no menus within WordPress. This is by design and is not likely to be changed for stability's sake.

First, edit `wp-content/plugins/ntm-wpmem-restrict-registration/options.php` (if it's not there, make a copy of `options_example.php`). Here are the options and what they do:

* Accepted Emails List ($ntmrr_accepted_emails)
 * Feel free to add, edit or remove any entry on the list
 * One address per line, surrounded by "qoutes" with a comma at the end, for example: "*@ntm.org",
 * Use * as a wildcard
     - Make sure the @ is included in each email address ("*ntm.org" would match "bob@badmantm.org")
     - Try to stick to *@whatever.org or certain_name@gmail.com
     - Don't go crazy with the *'s for security's sake (*_*@*.ntm*.org is what NOT to do)
     - *@ntm.org is a good usage
 * Example:
     - $ntmrr_accepted_emails = array(
         -  "*@ntm.org",
         -  "stevish@gmail.com",
     - );
* Blackliste Email List ($ntmrr_blacklisted_emails)
 * NO *'s ALLOWED! Only enter full email addresses (one per line)
 * Blacklisted emails are blocked even if they're on the approved list above.
 * No need to blacklist anything that's not on the whitelist... everything that's not on the whitelist is already blocked
  * - Potential use for this list is when one user needs to be blocked (like "stephen_the_hacker") while the rest of the organization (*@ntm.org) should still be allowed
 * Example:
     - $ntmrr_blacklisted_emails = array(
         - "stephen_the_hacker@ntm.org",
     - );
* Registration Form Message ($registration_form_message)
 * This message appears above the WP-Members registration form.
 * Useful for telling them their email address needs to be from certain domains or be "pre-approved"
 * Example:
     - $registration_form_message = "&lt;p style='padding: 10px; background-color: #ff6; border: 2px solid #aa4;'&gt;The email address you use must be on the pre-approved list pre-approved&lt;/p&gt;";
* "Email not approved" message ($email_not_approved_message)
 * This is the error message that appears when attempting to register using an unapproved email address
 * If the redirect option below is used, this will not be used.
 * Example:
     - $email_not_approved_message = "&lt;p style='line-height: 120%; text-align: left; padding: 0 5px; font-weight: normal;'&gt;We're sorry. You are using an E-mail address that has not been pre-approved.&lt;/p&gt;";
* Redirect on unapproved email ($redirect_on_unapproved_email and $redirect_on_unapproved_email_url)
 * To redirect on error instead of showing a message, set $redirect_on_unapproved_email to true
 * Be sure to use valid url (ie 'https://' . $_SERVER['SERVER_NAME'] . '/YOUR-FAILURE-PAGE/')
 * Example:
     - $redirect_on_unapproved_email = false;
     - $redirect_on_unapproved_email_url = 'https://' . $_SERVER['SERVER_NAME'] . '/YOUR-FAILURE-PAGE/';

= Will this work without WP Members activated? =

For security, this plugin will stop any registration attempts from unapproved email address regardless of how the registration is attempted. However, the error messages and redirects aren't guaranteed to work with the WP naative registration.

== Changelog ==

= 1.4 =
* Initial WP Release
* Moved/Renamed files
* Added WordPress Friendly readme.txt
* Fix "plugin breaks everything if options.php is missing"

= 1.3 =
* Initial Public release

== Upgrade Notice ==

= 1.2 =
Please update to the newest version