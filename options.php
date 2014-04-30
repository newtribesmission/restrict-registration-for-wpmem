<?php
/*** This is the file you edit to customize options or add email addresses ***/

/*************************************/
/******* Accepted Emails List ********/
/*************************************/
/* - Feel free to add, edit or remove any entry on the list
/* - One address per line, surrounded by "qoutes" with a comma at the end, for example: "*@ntm.org",
/* - Use * as a wildcard
/*   - Make sure the @ is included in each email address ("*ntm.org" would match "bob@badmantm.org")
/*   - Try to stick to *@whatever.org or certain_name@gmail.com
/*   - Don't go crazy with the *'s for security's sake (*_*@*.ntm*.org is what NOT to do)
/*   - *@ntm.org is a good usage
/*/

$ntmrr_accepted_emails = array(
	"*@ntm.org",
	"stevish@gmail.com",
);

/*************************************/
/****** BLACKLISTED Emails List ******/
/*************************************/
/* - NO *'s ALLOWED! Only enter full email addresses (one per line)
/* - Blacklisted emails are blocked even if they're on the approved list above.
/* - No need to blacklist anything that's not on the whitelist... everything that's not on the whitelist is already blocked
/*   - Potential use for this list is when one user needs to be blocked (like "stephen_the_hacker") while the rest of the organization (*@ntm.org) should still be allowed
/* */

$ntmrr_blacklisted_emails = array(
	"stephen_the_hacker@ntm.org",
);

/*************************************/
/******* Other User Variables ********/
/*************************************/
//This message appears above the WP-Members registration form.
//Useful for telling them their email address needs to be from certain domains or be "pre-approved"
$registration_form_message = "<p style='padding: 10px; background-color: #ff6; border: 2px solid #aa4;'>The email address you use must be on the pre-approved list pre-approved</p>";

//This is the error message that appears when attempting to register using an unapproved email address
//If the redirect option below is used, this will not be used.
$email_not_approved_message = "<p style='line-height: 120%; text-align: left; padding: 0 5px; font-weight: normal;'>We're sorry. You are using an E-mail address that has not been pre-approved.</p>";

//To redirect on error instead of showing a message, set $redirect_on_unapproved_email to true
//Be sure to use valid url (ie 'https://' . $_SERVER['SERVER_NAME'] . '/YOUR-FAILURE-PAGE/')
$redirect_on_unapproved_email = false;
$redirect_on_unapproved_email_url = 'https://' . $_SERVER['SERVER_NAME'] . '/YOUR-FAILURE-PAGE/';
?>