<?php
/*
Plugin Name: NTM Restricted Registration
Description: Restricts registration to email addresses listed within this file (edit the file to add/remove/edit email addresses or domains). Includes both whitelist (accepted emails) and blacklist (blocked emails). The blacklist will override entries in the whitelist
Author: Stephen Narwold
Author URI: http://blogs.ntm.org/stephen-narwold
Version: 1.2
*/

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

/*************************************/
/**** DO NOT EDIT BELOW THIS LINE ****/
/*(unless you know what you're doing)*/
/*************************************/

function ntmrr_is_blacklisted($user_email) { //Helper Function: Checks whether $user_email is on the blacklist
	//Requires exact match. *'s are not seen as wildcards
	global $ntmrr_blacklisted_emails;


	foreach($ntmrr_blacklisted_emails as $be) {
		if($be = filter_var($be, FILTER_VALIDATE_EMAIL)) { //blacklist only accepts exact email addresses. Might as well validate before comparing.
			if( trim(strtolower($be)) == trim(strtolower($user_email)) ) {
				return true; //User Email matched a blacklisted Email. Email is blacklisted
			}
		}
	}
	
	return false; //No matches. Email is not blacklisted
}

function ntmrr_is_whitelisted($user_email) { //Helper Function: Checks whether $user_email is on the whitelist
	//*'s are seen as wildcards
	global $ntmrr_accepted_emails;
	global $ntmrr_blacklisted_emails;
	
	$ntmrr_accepted_emails = array_diff($ntmrr_accepted_emails, $ntmrr_blacklisted_emails); //first remove any exact matches between the blacklist and whitelist
	foreach($ntmrr_accepted_emails as $email) {
		if($email) { //ignore blank elements
			$email = preg_quote( trim($email) , '/'); //make the email address into a regex friendly string
			$email = str_replace('\*', '[^@]*', $email); //turn *'s (now escaped by preg_quote) into regex wildcards
			if( preg_match("/^" . $email . "$/i", $user_email) ) { //check the user-entered email against the current accepted email
				return true; //User Email matched a whitelisted pattern. Email is whitelisted
			}
		}
	}
	
	return false; //No matches. Email is not whitelisted
}

//On native WP registration, check the registered email against the blacklist and whitelist, and throw appropriate error or redirects
//Shouldn't be needed since Native registration should be turned off, but this is here to plug any security holes.
function ntmrr_validate_email_default($errors, $sanitized_user_login, $user_email) {
	global $email_not_approved_message, $redirect_on_unapproved_email, $redirect_on_unapproved_email_url;
	
	$sanitary_email = filter_var($user_email, FILTER_VALIDATE_EMAIL);
	if( ntmrr_is_blacklisted($sanitary_email) || !ntmrr_is_whitelisted($sanitary_email) ) {
		//If the E-mail is on the blacklist or isn't on the whitelist \...
		if($redirect_on_unapproved_email) {
			//Redirect if that option is chosen
			header('Location: ' . $redirect_on_unapproved_email_url);
			die();
		} else {
			//If redirect not turned on, throw an error
			$errors->add('ntmrr-email-error', $email_not_approved_message);
			return $errors;
		}
	} else {
		//Otherwise, exit this function without throwing any new errors
		return $errors;
	}
}
add_filter('registration_errors', 'ntmrr_validate_email_default', 10, 3);

//For wp-members registration, check the registered email against the blacklist and whitelist, and throw appropriate error or redirects
function ntmrr_validate_email_wpmem($fields) { 
	global $email_not_approved_message, $redirect_on_unapproved_email, $redirect_on_unapproved_email_url;
	$user_email = $fields['user_email'];

	$sanitary_email = filter_var($user_email, FILTER_VALIDATE_EMAIL);
	if( ntmrr_is_blacklisted($sanitary_email) || !ntmrr_is_whitelisted($sanitary_email) ) { 
		//If the E-mail is on the blacklist or is not on the whitelist...
		if($redirect_on_unapproved_email) {
			//Redirect if that option is chosen
			header('Location: ' . $redirect_on_unapproved_email_url);
			die();
		} else {
			// throw an error
			$wpmem_themsg = $email_not_approved_message;
			return $wpmem_themsg;
		}
	} else { 
		//Otherwise, exit this function without throwing any new errors
		return false;
	}
	
}
add_action( 'wpmem_pre_register_data', 'ntmrr_validate_email_wpmem' );


//For WP-Members Registration form, add the text that appears above the form
function ntmrr_registration_requirements($content) {
	return $content . $registration_form_message;
}
add_filter( 'wpmem_register_form_before', 'ntmrr_registration_requirements');

function ntmrr_increase_wpmem_to_secondary_actions($where) {
	//Increases the scope of the WP Members Plugin. Stops blocked pages from showing up in search results, archive pages, recent post lists, etc
	global $wpdb;
	if(!is_user_logged_in() && (is_search() || is_feed() || is_archive() || !is_singular() || is_front_page())) { //Does not fire if user is logged in or the page is being accessed directly. WP-Members will handle the direct access requests.
		//Adds to any query that is accessing posts:
		$where .= $wpdb->prepare( //completely excludes blocked posts from the query results
					//1st line: Exclude post if there's a meta for that post of "block" = "true"
					//2nd/3rd lines: Make sure either "unblock" is "true" or post_type is not page (we only block pages)
                    " AND {$wpdb->posts}.ID NOT IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s ) 
					AND (
						{$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s )
						OR {$wpdb->posts}.post_type != %s 
					)",
                    'block', 'true', 'unblock', 'true', 'page'
                );
	}
	
	return $where;
}
add_filter('posts_where', 'ntmrr_increase_wpmem_to_secondary_actions');
?>