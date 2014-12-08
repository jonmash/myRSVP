<?php 

	delete_option( 'rsvp_db_version' );
 
	unregister_setting('rsvp-option-group', OPTION_OPENDATE);
	unregister_setting('rsvp-option-group', OPTION_GREETING);
	unregister_setting('rsvp-option-group', OPTION_THANKYOU);
	unregister_setting('rsvp-option-group', OPTION_NOTE_VERBIAGE);
	unregister_setting('rsvp-option-group', OPTION_YES_VERBIAGE);
	unregister_setting('rsvp-option-group', OPTION_NO_VERBIAGE);
	unregister_setting('rsvp-option-group', OPTION_DEADLINE);
	unregister_setting('rsvp-option-group', OPTION_THANKYOU);
	unregister_setting('rsvp-option-group', OPTION_NOTIFY_EMAIL);
	unregister_setting('rsvp-option-group', OPTION_NOTIFY_ON_RSVP);
	unregister_setting('rsvp-option-group', OPTION_WELCOME_TEXT);
	unregister_setting('rsvp-option-group', OPTION_RSVP_QUESTION);
	unregister_setting('rsvp-option-group', OPTION_RSVP_CUSTOM_YES_NO);
	unregister_setting('rsvp-option-group', RSVP_OPTION_HIDE_NOTE);
	unregister_setting('rsvp-option-group', OPTION_RSVP_GUEST_EMAIL_CONFIRMATION);
	unregister_setting('rsvp-option-group', OPTION_RSVP_DISABLE_CUSTOM_EMAIL_FROM);
	unregister_setting('rsvp-option-group', OPTION_RSVP_EMAIL_TEXT);
	unregister_setting('rsvp-option-group', OPTION_DEBUG_RSVP_QUERIES);

	 
	// Drop the custom db table.
	$table = FAMILIES_TABLE;
	$sql = "DROP TABLE IF EXISTS `".$table."`;";
	$wpdb->query($sql);
		
	// ATTENDEES Table
	$table = ATTENDEES_TABLE;
	$sql = "DROP TABLE IF EXISTS `".$table."`;";
	$wpdb->query($sql);
		
	//Attempts Table
	$table = ATTEMPTS_TABLE;
	$sql = "DROP TABLE IF EXISTS `".$table."`;";
	$wpdb->query($sql);

?>