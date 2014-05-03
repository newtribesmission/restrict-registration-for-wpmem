Restrict Registration for WP-Members
====================================

Author: New Tribes Mission (Stephen Narwold)
WordPress Plugin for use in conjunction with WP-Members. Assumes WP native registration is turned off.
Restricts registration to email addresses listed within the options file (edit options.php to add/remove/edit email addresses or domains). Includes both whitelist (accepted emails) and blacklist (blocked emails). The blacklist will override entries in the whitelist

    Copyright (C) 2014 New Tribes Mission

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

Instructions
============
1. Install and set up WP-Members
2. Turn off registration in WP settings
3. Install this plugin
4. Set up user variables
	a. Edit "options.php"
	b. Accepted Emails List ($ntmrr_accepted_emails)
		- Feel free to add, edit or remove any entry on the list
		- One address per line, surrounded by "qoutes" with a comma at the end, for example: "*@ntm.org",
		- Use * as a wildcard
			- Make sure the @ is included in each email address ("*ntm.org" would match "bob@badmantm.org")
			- Try to stick to *@whatever.org or certain_name@gmail.com
			- Don't go crazy with the *'s for security's sake (*_*@*.ntm*.org is what NOT to do)
			- *@ntm.org is a good usage
		- Example:
			$ntmrr_accepted_emails = array(
				"*@ntm.org",
				"stevish@gmail.com",
			);
	c. Blackliste Email List ($ntmrr_blacklisted_emails)
		- NO *'s ALLOWED! Only enter full email addresses (one per line)
		- Blacklisted emails are blocked even if they're on the approved list above.
		- No need to blacklist anything that's not on the whitelist... everything that's not on the whitelist is already blocked
			- Potential use for this list is when one user needs to be blocked (like "stephen_the_hacker") while the rest of the organization (*@ntm.org) should still be allowed
		- Example:
			$ntmrr_blacklisted_emails = array(
				"stephen_the_hacker@ntm.org",
			);
	d. Registration Form Message ($registration_form_message)
		- This message appears above the WP-Members registration form.
		- Useful for telling them their email address needs to be from certain domains or be "pre-approved"
		- Example:
			$registration_form_message = "<p style='padding: 10px; background-color: #ff6; border: 2px solid #aa4;'>The email address you use must be on the pre-approved list pre-approved</p>";
	e. "Email not approved" message ($email_not_approved_message)
		- This is the error message that appears when attempting to register using an unapproved email address
		- If the redirect option below is used, this will not be used.
		- Example:
			$email_not_approved_message = "<p style='line-height: 120%; text-align: left; padding: 0 5px; font-weight: normal;'>We're sorry. You are using an E-mail address that has not been pre-approved.</p>";
	f. Redirect on unapproved email ($redirect_on_unapproved_email and $redirect_on_unapproved_email_url)
		- To redirect on error instead of showing a message, set $redirect_on_unapproved_email to true
		- Be sure to use valid url (ie 'https://' . $_SERVER['SERVER_NAME'] . '/YOUR-FAILURE-PAGE/')
		- Example:
			$redirect_on_unapproved_email = false;
			$redirect_on_unapproved_email_url = 'https://' . $_SERVER['SERVER_NAME'] . '/YOUR-FAILURE-PAGE/';
5. Activate the plugin