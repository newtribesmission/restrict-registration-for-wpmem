<?php
/*
Plugin Name: Restrict Registration By Email for WP-Members
Description: Restricts registration to email addresses listed within the options file (edit the options.php file to add/remove/edit email addresses or domains). Includes both whitelist (accepted emails) and blacklist (blocked emails). The blacklist will override entries in the whitelist
Author: New Tribes Mission (Stephen Narwold)
Plugin URI: http://wordpress.org/plugins/restrict-registration-for-wp-members/
Version: 1.5

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
*/

if ( file_exists(plugin_dir_path(__FILE__) . 'options.php') ) {
	//Load user options
	require_once(plugin_dir_path(__FILE__) . 'options.php');
	
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
				header('Location: ' . $redirect_on_unapproved_email_url . '?ntmrr_error=not-approved');
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
				header('Location: ' . $redirect_on_unapproved_email_url . '?ntmrr_error=not-approved');
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
		global $registration_form_message;
		return $content . $registration_form_message;
	}
	add_filter( 'wpmem_register_form_before', 'ntmrr_registration_requirements');

	
	//Increases the scope of the WP Members Plugin. Stops blocked pages from showing up in search results, archive pages, recent post lists, etc
	function ntmrr_increase_wpmem_to_secondary_actions($where) {
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
	
	
	//[ntmrr_registration_error] Shortcode to display error message on redirected page
	function ntmrr_sc_redirect_error($atts) {
		global $email_not_approved_message;
		if ($_GET['ntmrr_error'] == 'not-approved' && count($_POST) == 0) {
			//If the GET variable is set AND there is no POST (comes straight from the redirect)
			return do_shortcode($email_not_approved_message);
		}
	}
	add_shortcode( 'ntmrr_registration_error', 'ntmrr_sc_redirect_error' );
	
} else {
	//If there was no options.php, show this error:
	function ntmrr_admin_notice() {
		?>
		<div class="error">
			<p>Restrict Registration Error! options.php not found!</p>
			<p>Copy options_example.php to options.php and set up the options.</p>
			<p>Restrict Registration will be disabled until this issue is resolved</p>
		</div>
		<?php
	}
	add_action( 'admin_notices', 'ntmrr_admin_notice' );
}


/******************/
/** Options Page **/
/******************/
function ntmrr_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	//Update options on form submission
    if( isset($_POST['ntmrr_posted']) && $_POST['ntmrr_posted'] == 'Y' ) {
        update_option( 'ntmrr_registration_form_message', $_POST['ntmrr_registration_form_message'] );
		update_option( 'ntmrr_email_not_approved_message', $_POST['ntmrr_email_not_approved_message'] );
		update_option( 'ntmrr_redirect_on_unapproved', $_POST['ntmrr_redirect_on_unapproved'] );
		update_option( 'ntmrr_redirect_on_unapproved_url', $_POST['ntmrr_redirect_on_unapproved_url'] );
		update_option( 'ntmrr_whitelisted_emails', $_POST['ntmrr_whitelisted_emails'] );
		update_option( 'ntmrr_blacklisted_emails', $_POST['ntmrr_blacklisted_emails'] );
        
		echo '<div class="updated"><p><strong>Settings saved.</strong></p></div>';
	}
	$reg_form_msg = esc_html(stripslashes(get_option('ntmrr_registration_form_message')));
	$error_msg = esc_html(stripslashes(get_option('ntmrr_email_not_approved_message')));
	$redirect = esc_html(stripslashes(get_option('ntmrr_redirect_on_unapproved')));
	$url = esc_html(stripslashes(get_option('ntmrr_redirect_on_unapproved_url')));
	$whitelist = esc_textarea(stripslashes(get_option('ntmrr_whitelisted_emails')));
	$blacklist = esc_textarea(stripslashes(get_option('ntmrr_blacklisted_emails')));
	?>
	<div class="wrap">
		<form name="ntmrr_options_form" method="POST" action="">
		<input type="hidden" name="ntmrr_posted" value="Y" />
			<h2>Emails</h2>
			<div style="width: 49%; float: left;">
				<h3><label for="ntmrr_whitelisted_emails">Email WhiteList</label></h3>
				<p>These are the <strong>accepted</strong> emails. Users <em>must</em> use an email represented on this list in order to register. Any emails not on this list will be rejected</p>
				<ul>
					<li>One address per line</li>
					<li>You may use * as a wildcard, but...
					<ul>
						<li>Make sure the @ is included in each email address ("*abc.org" would match "bob@badmanabc.org")</li>
						<li>Try to stick to *@whatever.org or certain_name@gmail.com</li>
						<li>Don't go crazy with the *'s for security's sake (*_*@*.abc*.org is what NOT to do)</li>
						<li>*@abc.org is a good usage</li>
					</ul>
					</li>
				</ul>
				<textarea rows="15" name="ntmrr_whitelisted_emails" style="width: 100%;"><?php echo $whitelist; ?></textarea>
			</div>
			<div style="width: 49%; float: right;">
				<h3><label for="ntmrr_whitelisted_emails">Email BlackList</label></h3>
				<p>These are <strong>blocked</strong> emails. Users who register with emails in this list wil be denied access, even if their emails are on the whitelist</p>
				<ul>
					<li>One address per line</li>
					<li>You may <strong>NOT</strong> use * as a wildcard</li>
					<li>Each entry must be a complete, valid email address</li>
					<li>Remember, this overrides the WhiteList.</li>
					<li>Useful for blocking specific people in approved organizations (like stephen_the_hacker@abc.org)</li>
				</ul>
				<textarea rows="15" name="ntmrr_blacklisted_emails" style="width: 100%;"><?php echo $blacklist; ?></textarea>
			</div>
			<p class="submit" style="clear: both;">
				<input type="submit" name="Submit1" class="button-primary" value="Save Changes" />
			</p>
			
			<h2 style="clear: both;">Options</h2>
			<label for="ntmrr_registration_form_message">Registration Form Message</label>
			<p>This message appears above the WP-Members registration form. It is useful for telling them their email address needs to be from certain domains or be "pre-approved".</p>
			<p>Example: &lt;p&gt;The email address you use must be on the pre-approved list&lt;/p&gt;</p>
			<input type="text" name="ntmrr_registration_form_message" value="<?php echo $reg_form_msg; ?>" /><br/><br/>
			
			<label for="ntmrr_email_not_approved_message">"Email Not Approved" Message</label>
			<p>This is the error message that appears when attempting to register using an unapproved email address.</p>
			<p>Example: &lt;p&gt;We're sorry. You are using an E-mail address that has not been pre-approved.&lt;/p&gt;</p>
			<input type="text" name="ntmrr_email_not_approved_message" value="<?php echo $error_msg; ?>" /><br/><br/>
			
			<input type="checkbox" name="ntmrr_redirect_on_unapproved" value="1" <?php echo $redirect ? 'checked' : ''; ?> /><label for="ntmrr_redirect_on_unapproved">Redirect when email is not approved?</label>
			<p>To redirect on error instead of just showing a message, check the box and fill in the location below</p>
			<p>Be sure to use valid url (ie 'https://' . $_SERVER['SERVER_NAME'] . '/YOUR-FAILURE-PAGE/')</p>
			<label for="ntmrr_redirect_on_unapproved_url">URL to redirect to: </label><input type="text" name="ntmrr_redirect_on_unapproved_url" value="<?php echo $url; ?>" />
			<p>Use [ntmrr_registration_error] on the redirect page to show the error on that page if needed</p><br/>
			
			<p class="submit" style="clear: both;">
				<input type="submit" name="Submit2" class="button-primary" value="Save Changes" />
			</p>
		</form>
	</div>
	<?php
}
function ntmrr_add_options_menu() {
	add_users_page( 'Restrict Registration By Email', 'Pre-Approve', 'manage_options', 'ntmrr_options_menu', 'ntmrr_options' );
}
add_action( 'admin_menu', 'ntmrr_add_options_menu' );

?>
