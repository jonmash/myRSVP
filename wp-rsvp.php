<?php
/**
 * @package myRSVP
 * @author Jonathan Mash
 * @version 0.0.1
 */
/*
Plugin Name: myRSVP 
Text Domain: my-rsvp-plugin
Plugin URI: http://wordpress.org/extend/plugins/my-rsvp/
Description: This plugin allows guests to RSVP to an event.
Author: Jonathan Mash
Version: 0.0.1
Author URI: http://jonmash.ca
License: MIT
*/
	session_start();
	global $wpdb;
	define("ATTENDEES_TABLE", $wpdb->prefix."rsvp_attendees");
	define("ATTEMPTS_TABLE", $wpdb->prefix."rsvp_attempts");
	define("FAMILIES_TABLE", $wpdb->prefix."rsvp_families");

	define("EDIT_SESSION_KEY", "RsvpEditAttendeeID");
	define("EDIT_QUESTION_KEY", "RsvpEditQuestionID");
	define("RSVP_FRONTEND_TEXT_CHECK", "my-rsvp-plugin");
	
	define("OPTION_GREETING", "rsvp_custom_greeting");
	define("OPTION_THANKYOU", "rsvp_custom_thankyou");
	define("OPTION_DEADLINE", "rsvp_deadline");
	define("OPTION_OPENDATE", 'rsvp_opendate');
	define("OPTION_YES_VERBIAGE", "rsvp_yes_verbiage");
	define("OPTION_NO_VERBIAGE", "rsvp_no_verbiage");
	define("OPTION_NOTE_VERBIAGE", "rsvp_note_verbiage");
	define("RSVP_OPTION_HIDE_NOTE", "rsvp_hide_note_field");
	define("OPTION_NOTIFY_ON_RSVP", "rsvp_notify_when_rsvp");
	define("OPTION_NOTIFY_EMAIL", "rsvp_notify_email_address");
	define("OPTION_WELCOME_TEXT", "rsvp_custom_welcome");
	define("OPTION_RSVP_QUESTION", "rsvp_custom_question_text");
	define("OPTION_RSVP_CUSTOM_YES_NO", "rsvp_custom_yes_no");
	define("OPTION_RSVP_GUEST_EMAIL_CONFIRMATION", "rsvp_guest_email_confirmation");
	define("OPTION_RSVP_EMAIL_TEXT", "rsvp_email_text");
	define("OPTION_DEBUG_RSVP_QUERIES", "Y");
	define("RSVP_DB_VERSION", "1");
	define("QT_SHORT", "shortAnswer");
	define("QT_MULTI", "multipleChoice");
	define("QT_LONG", "longAnswer");
	define("QT_DROP", "dropdown");
	define("QT_RADIO", "radio");
	define("RSVP_START_PARA", "<p class=\"rsvpParagraph\">");
	define("RSVP_END_PARA", "</p>\r\n");
	define("RSVP_START_CONTAINER", "<div id=\"rsvpPlugin\">\r\n");
	define("RSVP_END_CONTAINER", "</div>\r\n");
	define("RSVP_START_FORM_FIELD", "<div class=\"rsvpFormField\">\r\n");
	define("RSVP_END_FORM_FIELD", "</div>\r\n");

	$my_plugin_file = __FILE__;

	if (isset($plugin)) {
		$my_plugin_file = $plugin;
	}
	else if (isset($mu_plugin)) {
		$my_plugin_file = $mu_plugin;
	}
	else if (isset($network_plugin)) {
		$my_plugin_file = $network_plugin;
	}

	define('RSVP_PLUGIN_FILE', $my_plugin_file);
	define('RSVP_PLUGIN_PATH', WP_PLUGIN_DIR.'/'.basename(dirname($my_plugin_file)));
  
  
	if((isset($_GET['page']) && (strToLower($_GET['page']) == 'rsvp-admin-export')) || (isset($_POST['rsvp-bulk-action']) && (strToLower($_POST['rsvp-bulk-action']) == "export"))) {
		add_action('init', 'rsvp_admin_export');
	}
	
	require_once("rsvp_frontend.inc.php");
	
	/*
	 * Description: Database setup for the rsvp plug-in.  
	 */
	function rsvp_database_setup() {
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		require_once("rsvp_db_setup.inc.php");
	}
	
 
	function rsvp_is_passcode_unique($passcode) {
		global $wpdb;

		$isUnique = false;

		$sql = $wpdb->prepare("SELECT * FROM ".FAMILIES_TABLE." WHERE pin = %s", $passcode);
		if(!$wpdb->get_results($sql)) {
			$isUnique = true;
		}
		return $isUnique;
	}
	
	/**
	* This generates a random character passcode to be used for guests when the option is enabled.
	*/
	function rsvp_generate_passcode($length = 4) {
		$characters = '23456789ABCDEFGHJKMNPQRSTUXYZ';
		$passcode = "";

		for ($p = 0; $p < $length; $p++) {
			$passcode .= $characters[mt_rand(0, strlen($characters))];
		}

		return $passcode;
	}

	function rsvp_admin_options() {
		global $wpdb;
		
		//Check for duplicate passcode/blank passcodes.
?>
		<script type="text/javascript" language="javascript">
			jQuery(document).ready(function() {
				jQuery("#rsvp_opendate").datepicker();
				jQuery("#rsvp_deadline").datepicker();
			});
		</script>
		<div class="wrap">
			<h2>RSVP Guestlist Options</h2>
			<form method="post" action="options.php">
				<?php settings_fields( 'rsvp-option-group' ); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="rsvp_opendate">RSVP Open Date:</label></th>
						<td align="left"><input type="text" name="rsvp_opendate" id="rsvp_opendate" value="<?php echo htmlspecialchars(get_option(OPTION_OPENDATE)); ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="rsvp_deadline">RSVP Deadline:</label></th>
						<td align="left"><input type="text" name="rsvp_deadline" id="rsvp_deadline" value="<?php echo htmlspecialchars(get_option(OPTION_DEADLINE)); ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="rsvp_custom_greeting">Custom Greeting:</label></th>
						<td align="left"><textarea name="rsvp_custom_greeting" id="rsvp_custom_greeting" rows="5" cols="60"><?php echo htmlspecialchars(get_option(OPTION_GREETING)); ?></textarea></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="rsvp_custom_welcome">Custom Welcome:</label></th>
						<td align="left">Default is: &quot;There are a few more questions we need to ask you if you could please fill them out below to finish up the RSVP process.&quot;<br />
							<textarea name="rsvp_custom_welcome" id="rsvp_custom_welcome" rows="5" cols="60"><?php echo htmlspecialchars(get_option(OPTION_WELCOME_TEXT)); ?></textarea></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="<?php echo OPTION_RSVP_EMAIL_TEXT; ?>">Email Text: <br />Sent to guests in confirmation, at top of email</label></th>
						<td align="left"><textarea name="<?php echo OPTION_RSVP_EMAIL_TEXT; ?>" id="<?php echo OPTION_RSVP_EMAIL_TEXT; ?>" rows="5" cols="60"><?php echo htmlspecialchars(get_option(OPTION_RSVP_EMAIL_TEXT)); ?></textarea></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="rsvp_custom_question_text">RSVP Question Verbiage:</label></th>
						<td align="left">Default is: &quot;So, how about it?&quot;<br />
							<input type="text" name="rsvp_custom_question_text" id="rsvp_custom_question_text" 
							value="<?php echo htmlspecialchars(get_option(OPTION_RSVP_QUESTION)); ?>" size="65" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="rsvp_yes_verbiage">RSVP Yes Verbiage:</label></th>
						<td align="left"><input type="text" name="rsvp_yes_verbiage" id="rsvp_yes_verbiage" 
							value="<?php echo htmlspecialchars(get_option(OPTION_YES_VERBIAGE)); ?>" size="65" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="rsvp_no_verbiage">RSVP No Verbiage:</label></th>
						<td align="left"><input type="text" name="rsvp_no_verbiage" id="rsvp_no_verbiage" 
							value="<?php echo htmlspecialchars(get_option(OPTION_NO_VERBIAGE)); ?>" size="65" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="rsvp_kids_meal_verbiage">RSVP Kids Meal Verbiage:</label></th>
						<td align="left"><input type="text" name="rsvp_kids_meal_verbiage" id="rsvp_kids_meal_verbiage" 
							value="<?php echo htmlspecialchars(get_option(OPTION_KIDS_MEAL_VERBIAGE)); ?>" size="65" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="rsvp_veggie_meal_verbiage">RSVP Vegetarian Meal Verbiage:</label></th>
						<td align="left"><input type="text" name="rsvp_veggie_meal_verbiage" id="rsvp_veggie_meal_verbiage" 
							value="<?php echo htmlspecialchars(get_option(OPTION_VEGGIE_MEAL_VERBIAGE)); ?>" size="65" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="rsvp_note_verbiage">Note Verbiage:</label></th>
						<td align="left"><textarea name="rsvp_note_verbiage" id="rsvp_note_verbiage" rows="3" cols="60"><?php 
							echo htmlspecialchars(get_option(OPTION_NOTE_VERBIAGE)); ?></textarea></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="rsvp_hide_note_field">Hide Note Field:</label></th>
						<td align="left"><input type="checkbox" name="rsvp_hide_note_field" id="rsvp_hide_note_field" value="Y" 
							<?php echo ((get_option(RSVP_OPTION_HIDE_NOTE) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="rsvp_custom_thankyou">Custom Thank You:</label></th>
						<td align="left"><textarea name="rsvp_custom_thankyou" id="rsvp_custom_thankyou" rows="5" cols="60"><?php echo htmlspecialchars(get_option(OPTION_THANKYOU)); ?></textarea></td>
					</tr>
					<tr>
						<th scope="row"><label for="rsvp_notify_when_rsvp">Notify When Guest RSVPs</label></th>
						<td align="left"><input type="checkbox" name="rsvp_notify_when_rsvp" id="rsvp_notify_when_rsvp" value="Y" 
							<?php echo ((get_option(OPTION_NOTIFY_ON_RSVP) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
					</tr>
					<tr>
						<th scope="row"><label for="rsvp_notify_email_address">Email address to notify</label></th>
						<td align="left"><input type="text" name="rsvp_notify_email_address" id="rsvp_notify_email_address" value="<?php echo htmlspecialchars(get_option(OPTION_NOTIFY_EMAIL)); ?>"/></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="rsvp_guest_email_confirmation">Send email to main guest when they RSVP</label></th>
						<td align="left"><input type="checkbox" name="rsvp_guest_email_confirmation" id="rsvp_guest_email_confirmation" value="Y" 
							<?php echo ((get_option(OPTION_RSVP_GUEST_EMAIL_CONFIRMATION) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
					</tr>
				</table>
				<input type="hidden" name="action" value="update" />
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
			</form>
		</div>
<?php
	}
	
	//FAMILY LIST
	function rsvp_admin_familylist() {
		global $wpdb;		
		
		//Check if the database needs to be updated
		if(get_option("rsvp_db_version") != RSVP_DB_VERSION) {
			rsvp_database_setup();
		}
		
		//Bulk Delete
		if((count($_POST) > 0) && ($_POST['rsvp-bulk-action'] == "delete") && (is_array($_POST['family']) && (count($_POST['family']) > 0))) {
			foreach($_POST['family'] as $family) {
				if(is_numeric($family) && ($family > 0)) {
					$wpdb->query($wpdb->prepare("DELETE FROM ".FAMILIES_TABLE." WHERE id = %d", 
																			$family));
				}
			}
		}
		
		$sql = "SELECT id, pin, email, alias, comments FROM ".FAMILIES_TABLE;
		$orderBy = " alias";
		if(isset($_GET['sort'])) {
			if(strToLower($_GET['sort']) == "id") {
				$direction = ((strtolower($_GET['sortDirection']) == "desc") ? "DESC" : "ASC");
				$orderBy = " id $direction";
			}
			elseif(strToLower($_GET['sort']) == "alias") {
				$direction = ((strtolower($_GET['sortDirection']) == "desc") ? "DESC" : "ASC");
				$orderBy = " alias $direction";
			}				
		}
		$sql .= " ORDER BY ".$orderBy;
		
		$families = $wpdb->get_results($sql);
		$sort = "";
		$sortDirection = "asc";
		if(isset($_GET['sort'])) {
			$sort = $_GET['sort'];
		}
		if(isset($_GET['sortDirection'])) {
			$sortDirection = $_GET['sortDirection'];
		}
	?>
		<script type="text/javascript" language="javascript">
			jQuery(document).ready(function() {
				jQuery("#cb").click(function() {
					if(jQuery("#cb").attr("checked")) {
						jQuery("input[name='family[]']").attr("checked", "checked");
					} else {
						jQuery("input[name='family[]']").removeAttr("checked");
					}
				});
			});
		</script>
		<div class="wrap">	
			<div id="icon-edit" class="icon32"><br /></div>	
			<h2>List of current families</h2>
			<form method="post" id="rsvp-form" enctype="multipart/form-data">
				<input type="hidden" id="rsvp-bulk-action" name="rsvp-bulk-action" />
				<input type="hidden" id="sortValue" name="sortValue" value="<?php echo htmlentities($sort, ENT_QUOTES); ?>" />
				<input type="hidden" name="exportSortDirection" value="<?php echo htmlentities($sortDirection, ENT_QUOTES); ?>" />
				<div class="tablenav">
					<div class="alignleft actions">
						<select id="rsvp-action-top" name="action">
							<option value="" selected="selected"><?php _e('Bulk Actions', 'rsvp'); ?></option>
							<option value="delete"><?php _e('Delete', 'rsvp'); ?></option>
						</select>
						<input type="submit" value="<?php _e('Apply', 'rsvp'); ?>" name="doaction" id="doaction" class="button-secondary action" onclick="document.getElementById('rsvp-bulk-action').value = document.getElementById('rsvp-action-top').value;" />
						<input type="submit" value="<?php _e('Export Attendees', 'rsvp'); ?>" name="exportButton" id="exportButton" class="button-secondary action" onclick="document.getElementById('rsvp-bulk-action').value = 'export';" />
					</div>
					<div class="clear"></div>
				</div>
			<table class="widefat post fixed" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" class="manage-column column-cb check-column" style=""><input type="checkbox" id="cb" /></th>
						<th scope="col" id="rsvpAlias" class="manage-column column-title" style="">Family<br />
							<a href="admin.php?page=rsvp-admin-familylist&amp;sort=alias&amp;sortDirection=asc">
								<img src="<?php echo plugins_url(); ?>/myRSVP/uparrow<?php 
									echo ((($sort == "alias") && ($sortDirection == "asc")) ? "_selected" : ""); ?>.gif" width="11" height="9" 
									alt="Sort Ascending Alias" title="Sort Ascending Alias" border="0"></a> &nbsp;
							<a href="admin.php?page=rsvp-admin-familylist&amp;sort=alias&amp;sortDirection=desc">
								<img src="<?php echo plugins_url(); ?>/myRSVP/downarrow<?php 
									echo ((($sort == "alias") && ($sortDirection == "desc")) ? "_selected" : ""); ?>.gif" width="11" height="9" 
									alt="Sort Descending Alias" title="Sort Descending Alias" border="0"></a>
						</th>			
						<th scope="col" id="rsvpEmail" class="manage-column column-title">Email</th>
						<th scope="col" id="rsvpComments" class="manage-column column-title" style="">Comments</th>
						<th scope="col" id="rsvpPin" class="manage-column column-title" style="">PIN</th>
					</tr>
				</thead>
			</table>
			<div style="overflow: auto;height: 450px;">
				<table class="widefat post fixed" cellspacing="0">
				<?php
					$i = 0;
					foreach($families as $family) {
					?>
						<tr class="<?php echo (($i % 2 == 0) ? "alternate" : ""); ?> author-self">
							<th scope="row" class="check-column"><input type="checkbox" name="family[]" value="<?php echo $family->id; ?>" /></th>						
							<td><a href="<?php echo get_option("siteurl"); ?>/wp-admin/admin.php?page=rsvp-admin-family&amp;id=<?php echo $family->id; ?>"><?php echo htmlspecialchars(stripslashes($family->alias)); ?></a></td>
							<td><?php echo htmlspecialchars(stripslashes($family->email)); ?></td>
							<td><?php echo htmlspecialchars(stripslashes($family->comments)); ?></td>
							<td><?php echo htmlspecialchars(stripslashes($family->pin)); ?></td>
						</tr>
					<?php
						$i++;
					}
				?>
				</table>
			</div>
			</form>
		</div>
	<?php
	}
	
	//ATTENDEE LIST
	function rsvp_admin_attendeelist() {
		global $wpdb;		
		
		//Check if the database needs to be updated
		if(get_option("rsvp_db_version") != RSVP_DB_VERSION) {
			rsvp_database_setup();
		}
		
		//Bulk Delete
		if((count($_POST) > 0) && ($_POST['rsvp-bulk-action'] == "delete") && (is_array($_POST['attendee']) && (count($_POST['attendee']) > 0))) {
			foreach($_POST['attendee'] as $attendee) {
				if(is_numeric($attendee) && ($attendee > 0)) {
					$wpdb->query($wpdb->prepare("DELETE FROM ".ATTENDEES_TABLE." WHERE id = %d", 
																			$attendee));
				}
			}
		}
		
		$sql = "SELECT id, family, name, attending, food FROM ".ATTENDEES_TABLE;
		$orderBy = " name";
		if(isset($_GET['sort'])) {
			if(strToLower($_GET['sort']) == "name") {
				$orderBy = " name ".((strtolower($_GET['sortDirection']) == "desc") ? "DESC" : "ASC");
			}
			else if(strToLower($_GET['sort']) == "attending") {
				$orderBy = " attending ".((strtolower($_GET['sortDirection']) == "desc") ? "DESC" : "ASC") .", ".$orderBy;
			}	
			else if(strToLower($_GET['sort']) == "family") {
				$orderBy = " family ".((strtolower($_GET['sortDirection']) == "desc") ? "DESC" : "ASC") .", ".$orderBy;
			}	
			else if(strToLower($_GET['sort']) == "food") {
				$orderBy = " food ".((strtolower($_GET['sortDirection']) == "desc") ? "DESC" : "ASC") .", ".$orderBy;
			}			
		}
		$sql .= " ORDER BY ".$orderBy;
		$attendees = $wpdb->get_results($sql);
		
		$sort = "";
		$sortDirection = "asc";
		if(isset($_GET['sort'])) {
			$sort = $_GET['sort'];
		}
		if(isset($_GET['sortDirection'])) {
			$sortDirection = $_GET['sortDirection'];
		}
	?>
		<script type="text/javascript" language="javascript">
			jQuery(document).ready(function() {
				jQuery("#cb").click(function() {
					if(jQuery("#cb").attr("checked")) {
						jQuery("input[name='attendee[]']").attr("checked", "checked");
					} else {
						jQuery("input[name='attendee[]']").removeAttr("checked");
					}
				});
			});
		</script>
		<div class="wrap">	
			<div id="icon-edit" class="icon32"><br /></div>	
			<h2>List of current attendees</h2>
			<form method="post" id="rsvp-form" enctype="multipart/form-data">
				<input type="hidden" id="rsvp-bulk-action" name="rsvp-bulk-action" />
				<input type="hidden" id="sortValue" name="sortValue" value="<?php echo htmlentities($sort, ENT_QUOTES); ?>" />
				<input type="hidden" name="exportSortDirection" value="<?php echo htmlentities($sortDirection, ENT_QUOTES); ?>" />
				<div class="tablenav">
					<div class="alignleft actions">
						<select id="rsvp-action-top" name="action">
							<option value="" selected="selected"><?php _e('Bulk Actions', 'rsvp'); ?></option>
							<option value="delete"><?php _e('Delete', 'rsvp'); ?></option>
						</select>
						<input type="submit" value="<?php _e('Apply', 'rsvp'); ?>" name="doaction" id="doaction" class="button-secondary action" onclick="document.getElementById('rsvp-bulk-action').value = document.getElementById('rsvp-action-top').value;" />
						<input type="submit" value="<?php _e('Export Attendees', 'rsvp'); ?>" name="exportButton" id="exportButton" class="button-secondary action" onclick="document.getElementById('rsvp-bulk-action').value = 'export';" />
					</div>
					<?php
						$yesResults = $wpdb->get_results("SELECT COUNT(*) AS yesCount FROM ".ATTENDEES_TABLE." WHERE attending = 'Yes'");
						$noResults = $wpdb->get_results("SELECT COUNT(*) AS noCount FROM ".ATTENDEES_TABLE." WHERE attending = 'No'");
						$noResponseResults = $wpdb->get_results("SELECT COUNT(*) AS noResponseCount FROM ".ATTENDEES_TABLE." WHERE attending = ''");
					?>
					<div class="alignright">RSVP Count -  
						Yes: <strong><?php echo $yesResults[0]->yesCount; ?></strong> &nbsp; &nbsp;  &nbsp; &nbsp; 
						No: <strong><?php echo $noResults[0]->noCount; ?></strong> &nbsp; &nbsp;  &nbsp; &nbsp; 
						No Response: <strong><?php echo $noResponseResults[0]->noResponseCount; ?></strong> &nbsp; &nbsp;  &nbsp; &nbsp; 
					</div>
					<div class="clear"></div>
				</div>
			<table class="widefat post fixed" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" class="manage-column column-cb check-column" style=""><input type="checkbox" id="cb" /></th>
						<th scope="col" id="rsvpFamily" class="manage-column column-title">Family</th>
						<th scope="col" id="attendeeName" class="manage-column column-title" style="">Attendee<br />
							<a href="admin.php?page=rsvp-admin-attendeelist&amp;sort=name&amp;sortDirection=asc">
								<img src="<?php echo plugins_url(); ?>/myRSVP/uparrow<?php 
									echo ((($sort == "name") && ($sortDirection == "asc")) ? "_selected" : ""); ?>.gif" width="11" height="9" 
									alt="Sort Ascending Attendee Name" title="Sort Ascending Attendee Name" border="0"></a> &nbsp;
							<a href="admin.php?page=rsvp-admin-attendeelist&amp;sort=name&amp;sortDirection=desc">
								<img src="<?php echo plugins_url(); ?>/myRSVP/downarrow<?php 
									echo ((($sort == "name") && ($sortDirection == "desc")) ? "_selected" : ""); ?>.gif" width="11" height="9" 
									alt="Sort Descending Attendee Name" title="Sort Descending Attendee Name" border="0"></a>
						</th>			
						<th scope="col" id="rsvpStatus" class="manage-column column-title">Attending<br />
							<a href="admin.php?page=rsvp-admin-attendeelist&amp;sort=attending&amp;sortDirection=asc">
								<img src="<?php echo plugins_url(); ?>/myRSVP/uparrow<?php 
									echo ((($sort == "attending") && ($sortDirection == "asc")) ? "_selected" : ""); ?>.gif" width="11" height="9" 
									alt="Sort Ascending RSVP Status" title="Sort Ascending RSVP Status" border="0"></a> &nbsp;
							<a href="admin.php?page=rsvp-admin-attendeelist&amp;sort=attending&amp;sortDirection=desc">
								<img src="<?php echo plugins_url(); ?>/myRSVP/downarrow<?php 
									echo ((($sort == "attending") && ($sortDirection == "desc")) ? "_selected" : ""); ?>.gif" width="11" height="9" 
									alt="Sort Descending RSVP Status" title="Sort Descending RSVP Status" border="0"></a>						
						</th>
						<th scope="col" id="rsvpFood" class="manage-column column-title" style="">Food<br />
							<a href="admin.php?page=rsvp-admin-attendeelist&amp;sort=food&amp;sortDirection=asc">
								<img src="<?php echo plugins_url(); ?>/myRSVP/uparrow<?php 
									echo ((($sort == "food") && ($sortDirection == "asc")) ? "_selected" : ""); ?>.gif" width="11" height="9" 
									alt="Sort Ascending Food" title="Sort Ascending Food" border="0"></a>
							<a href="admin.php?page=rsvp-admin-attendeelist&amp;sort=food&amp;sortDirection=desc">
								<img src="<?php echo plugins_url(); ?>/myRSVP/downarrow<?php 
									echo ((($sort == "food") && ($sortDirection == "desc")) ? "_selected" : ""); ?>.gif" width="11" height="9" 
									alt="Sort Descending Food" title="Sort Descending Food" border="0"></a>
						</th>
					</tr>
				</thead>
			</table>
			<div style="overflow: auto;height: 450px;">
				<table class="widefat post fixed" cellspacing="0">
				<?php
					$i = 0;
					foreach($attendees as $attendee) {
					?>
						<tr class="<?php echo (($i % 2 == 0) ? "alternate" : ""); ?> author-self">
							<th scope="row" class="check-column"><input type="checkbox" name="attendee[]" value="<?php echo $attendee->id; ?>" /></th>						
							<td>
								<a href="<?php echo get_option("siteurl"); ?>/wp-admin/admin.php?page=rsvp-admin-family&amp;id=<?php echo $attendee->family; ?>"><?php echo htmlspecialchars(stripslashes($attendee->family)); ?></a>
							</td>
							<td>
								<a href="<?php echo get_option("siteurl"); ?>/wp-admin/admin.php?page=rsvp-admin-guest&amp;id=<?php echo $attendee->id; ?>&amp;family=<?php echo $attendee->family; ?>"><?php echo htmlspecialchars(stripslashes($attendee->name)); ?></a>
							</td>
							<td><?php echo htmlspecialchars(stripslashes($attendee->attending)); ?></td>
							<td><?php echo htmlspecialchars(stripslashes($attendee->food)); ?></td>
						</tr>
					<?php
						$i++;
					}
				?>
				</table>
			</div>
			</form>
		</div>
	<?php
	}
	

	//Add/Edit Family
	function rsvp_admin_family() {
		global $wpdb;
		if((count($_POST) > 0) && !empty($_POST['alias']) && !empty($_POST['pin'])) {
			check_admin_referer('rsvp_add_family');
						
			if(isset($_SESSION[EDIT_SESSION_KEY]) && is_numeric($_SESSION[EDIT_SESSION_KEY])) {
				$wpdb->update(FAMILIES_TABLE, 
											array("pin" => trim($_POST['pin']),
											      "email" => trim($_POST['email']), 
												  "alias" => trim($_POST['alias']), 
												  "comments" => trim($_POST['comments'])), 
											array("id" => $_SESSION[EDIT_SESSION_KEY]), 
											array("%s", "%s", "%s", "%s"), 
											array("%d"));
				$attendeeId = $_SESSION[EDIT_SESSION_KEY];
				rsvp_printQueryDebugInfo();     
			} else {
				$wpdb->insert(FAMILIES_TABLE, array("pin" => trim($_POST['pin']), 
				                                     "email" => trim($_POST['email']),
													 "alias" => trim($_POST['alias']), 
													 "comments" => trim($_POST['comments'])), 
				                                     array('%s', '%s', '%s', '%s'));
					
				$attendeeId = $wpdb->insert_id;
			}

		?>
			<p>Family <?php echo htmlspecialchars(stripslashes($_POST['alias']));?> has been successfully saved</p>
			<p>
				<a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=rsvp-top-level">Continue to Family List</a> | 
				<a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=rsvp-admin-guest&family=<?php echo $attendeeId ?>">Add a Guest to This Family</a> 
			</p>
	<?php
		} else {
			$family = null;
			unset($_SESSION[EDIT_SESSION_KEY]);
			$pin = "";
			$email = "";
			$alias = "";
			$comments = "";
			$link = "";
			
			if(isset($_GET['id']) && is_numeric($_GET['id'])) {
				$family = $wpdb->get_row("SELECT id, pin, date, ip, email, alias, comments FROM ".FAMILIES_TABLE." WHERE id = ".$_GET['id']);
				if($family != null) {
					$_SESSION[EDIT_SESSION_KEY] = $family->id;
					$pin = stripslashes($family->pin);
					$email = stripslashes($family->email);
					$alias = stripslashes($family->alias);
					$comments = stripslashes($family->comments);
					$link = "<a href=\"" . get_option('siteurl') ."/wp-admin/admin.php?page=rsvp-admin-guest&family=" . $family->id  ."\">Add a Guest to This Family</a> ";
				} 
			} 
	?>
			<form name="contact" action="admin.php?page=rsvp-admin-family" method="post">
				<?php wp_nonce_field('rsvp_add_family'); ?>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save'); ?>" />
				</p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="alias"><?php echo __("Family Alias", 'rsvp-plugin'); ?>:</label></th>
						<td align="left"><input type="text" name="alias" id="alias" size="30" value="<?php echo htmlspecialchars($alias); ?>"/></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="email"><?php echo __("Email", 'rsvp-plugin'); ?>:</label></th>
						<td align="left"><input type="text" name="email" id="email" size="30" value="<?php echo htmlspecialchars($email); ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="pin"><?php echo __("PIN", 'rsvp-plugin'); ?>:</label></th>
						<td align="left"><input type="text" name="pin" id="pin" size="30" value="<?php echo htmlspecialchars($pin); ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="comments"><?php echo __("Comments", 'rsvp-plugin'); ?>:</label></th>
						<td align="left"><input type="text" name="comments" id="comments" size="30" value="<?php echo htmlspecialchars($comments); ?>" /></td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save'); ?>" />
				</p>
				<p>
					<?php echo $link; ?>
				</p>
			</form>
			<?php
				$sql = "SELECT id, family, name, attending, food FROM ".ATTENDEES_TABLE." WHERE family = " . $_GET['id'] . ";";
				$attendees = $wpdb->get_results($sql);
			?>
			
			Attendee List:
						<table class="widefat post fixed" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" id="attendeeName" class="manage-column column-title" style="">Attendee</th>			
						<th scope="col" id="rsvpStatus" class="manage-column column-title">Attending</th>
						<th scope="col" id="rsvpFood" class="manage-column column-title" style="">Food</th>
					</tr>
				</thead>
			</table>
			<table class="widefat post fixed" cellspacing="0">
			<?php
				$i = 0;
				foreach($attendees as $attendee) {
				?>
					<tr class="<?php echo (($i % 2 == 0) ? "alternate" : ""); ?> author-self">
						<td>
							<a href="<?php echo get_option("siteurl"); ?>/wp-admin/admin.php?page=rsvp-admin-guest&amp;id=<?php echo $attendee->id; ?>&amp;family=<?php echo $attendee->family; ?>"><?php echo htmlspecialchars(stripslashes($attendee->name)); ?></a>
						</td>
						<td><?php echo htmlspecialchars(stripslashes($attendee->attending)); ?></td>
						<td><?php echo htmlspecialchars(stripslashes($attendee->food)); ?></td>
					</tr>
				<?php
					$i++;
				}
				if(!$attendees) echo "<tr><td><strong>No Attendees Yet!</strong></td></tr>";
			?>
			</table>
			
<?php
		}
	}
	
	//Add/Edit Guest
	function rsvp_admin_guest() {
		global $wpdb;
		if((count($_POST) > 0) && !empty($_POST['name']) && !empty($_POST['family'])) {
			check_admin_referer('rsvp_add_guest');
					
			if(isset($_SESSION[EDIT_SESSION_KEY]) && is_numeric($_SESSION[EDIT_SESSION_KEY])) {
				
				$wpdb->update(ATTENDEES_TABLE, 
											array("family" => trim($_POST['family']),
												  "name" => trim($_POST['name']), 
											      "attending" => trim($_POST['attending']), 
												  "food" => trim($_POST['food'])), 
											array("id" => $_SESSION[EDIT_SESSION_KEY]), 
											array("%d", "%s", "%s", "%s"), 
											array("%d"));
				$attendeeId = $_SESSION[EDIT_SESSION_KEY];
				rsvp_printQueryDebugInfo();     
			} else {
				echo "Inserting";
				$wpdb->insert(ATTENDEES_TABLE, array("family" => trim($_POST['family']), 
				                                     "name" => trim($_POST['name']),
													 "attending" => trim($_POST['attending']), 
													 "food" => trim($_POST['food'])), 
				                                     array('%d', '%s', '%s', '%s'));
					
				$attendeeId = $wpdb->insert_id;
				rsvp_printQueryDebugInfo();
			}

		?>
			<p>Attendee <?php echo htmlspecialchars(stripslashes($_POST['name']));?> has been successfully saved</p>
			<p>
				<a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=rsvp-admin-attendeelist">Continue to Attendee List</a> | 
				<a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=rsvp-admin-guest&family=<?php echo htmlspecialchars(stripslashes($_POST['family']));?>">Add another guest to this family</a> 
			</p>
	<?php
		} else {
			$attendee = null;
			unset($_SESSION[EDIT_SESSION_KEY]);
			$name = "";
			$family = "";
			$email = "";
			$attending = "NoResponse";
			$food = "";
			
			if((isset($_GET['family']) && is_numeric($_GET['family'])) || (isset($_POST['family']) && is_numeric($_POST['family']))) {
				$family = $_GET['family'];
			}
			else { //Check to make sure the family ID is specified.			
?>
			<p>Cannot access this page like this!</p>
			<p>
				<a href="<?php echo get_option('siteurl'); ?>/wp-admin/admin.php?page=rsvp-top-level">Back to Attendee List</a> | 
			</p>
<?php			
				return;
			}
			
			
			if(isset($_GET['id']) && is_numeric($_GET['id'])) {
				$attendee = $wpdb->get_row("SELECT id, family, name, attending, food FROM ".ATTENDEES_TABLE." WHERE id = ".$_GET['id']);
				if($attendee != null) {
					$_SESSION[EDIT_SESSION_KEY] = $attendee->id;
					$family = stripslashes($attendee->family);
					$name = stripslashes($attendee->name);
					$attending = stripslashes($attendee->attending);
					$food = stripslashes($attendee->food);
				} 
				rsvp_printQueryDebugInfo();
			} 
	?>
			<form name="contact" action="admin.php?page=rsvp-admin-guest&family=<?php echo htmlspecialchars($family); ?>" method="post">
				<?php wp_nonce_field('rsvp_add_guest'); ?>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save'); ?>" />
				</p>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="family"><?php echo __("Family", 'rsvp-plugin'); ?>:</label></th>
						<td align="left"><input type="text" name="family" id="family" size="30" value="<?php echo htmlspecialchars($family); ?>" readonly="readonly"/></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="name"><?php echo __("Name", 'rsvp-plugin'); ?>:</label></th>
						<td align="left"><input type="text" name="name" id="name" size="30" value="<?php echo htmlspecialchars($name); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="attending">RSVP Status</label></th>
						<td align="left">
							<select name="attending" id="attending" size="1">
								<option value="NoResponse" <?php
									echo (($attending == "NoResponse") ? " selected=\"selected\"" : "");
								?>>No Response</option>
								<option value="Yes" <?php
									echo (($attending == "Yes") ? " selected=\"selected\"" : "");
								?>>Yes</option>									
								<option value="No" <?php
									echo (($attending == "No") ? " selected=\"selected\"" : "");
								?>>No</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="food">Food</label></th>
						<td align="left">
							<select name="food" id="food" size="1">
								<option value="NoResponse" <?php
									echo (($food == "NoResponse") ? " selected=\"selected\"" : "");
								?>>No Response</option>
								<option value="Meat" <?php
									echo (($food == "Meat") ? " selected=\"selected\"" : "");
								?>>Meat</option>									
								<option value="Fish" <?php
									echo (($food == "Fish") ? " selected=\"selected\"" : "");
								?>>Fish</option>
								<option value="Veg" <?php
									echo (($food == "Veg") ? " selected=\"selected\"" : "");
								?>>Vegetarian</option>
							</select>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save'); ?>" />
				</p>
			</form>
<?php
		}
	}
	
	
	function rsvp_modify_menu() {
		

		$page = add_menu_page("myRSVP Plugin", 
									"myRSVP Plugin", 
									"publish_posts", 
									"rsvp-top-level", 
									"rsvp_admin_familylist");
		add_action('admin_print_scripts-' . $page, 'rsvp_admin_scripts'); 
		
		$page = add_submenu_page("rsvp-top-level", 
										 "All Families",
										 "All Families",
										 "publish_posts", 
										 "rsvp-admin-familylist",
										 "rsvp_admin_familylist");
		add_action('admin_print_scripts-' . $page, 'rsvp_admin_scripts');
		
		$page = add_submenu_page("rsvp-top-level", 
										 "Add Family",
										 "Add Family",
										 "publish_posts", 
										 "rsvp-admin-family",
										 "rsvp_admin_family");
		add_action('admin_print_scripts-' . $page, 'rsvp_admin_scripts'); 
		
		$page = add_submenu_page("rsvp-top-level", 
										 "All Guests",
										 "All Guests",
										 "publish_posts", 
										 "rsvp-admin-attendeelist",
										 "rsvp_admin_attendeelist");
		add_action('admin_print_scripts-' . $page, 'rsvp_admin_scripts'); 
		
		$page = add_submenu_page("rsvp-top-level", 
										 "Add Guest",
										 "Add Guest",
										 "publish_posts", 
										 "rsvp-admin-guest",
										 "rsvp_admin_guest");
		add_action('admin_print_scripts-' . $page, 'rsvp_admin_scripts'); 
		
		$page = add_submenu_page("rsvp-top-level",
                       'myRSVP Options',	//page title
	                   'myRSVP Options',	//subpage title
	                   'manage_options',	//access
	                   'rsvp-options',		//current file
	                   'rsvp_admin_options'	//options function above
	                );
		add_action('admin_print_scripts-' . $page, 'rsvp_admin_scripts'); 
	}
	
	function rsvp_register_settings() {
		register_setting('rsvp-option-group', OPTION_OPENDATE);
		register_setting('rsvp-option-group', OPTION_GREETING);
		register_setting('rsvp-option-group', OPTION_THANKYOU);
		register_setting('rsvp-option-group', OPTION_NOTE_VERBIAGE);
		register_setting('rsvp-option-group', OPTION_YES_VERBIAGE);
		register_setting('rsvp-option-group', OPTION_NO_VERBIAGE);
		register_setting('rsvp-option-group', OPTION_DEADLINE);
		register_setting('rsvp-option-group', OPTION_THANKYOU);
		register_setting('rsvp-option-group', OPTION_NOTIFY_EMAIL);
		register_setting('rsvp-option-group', OPTION_NOTIFY_ON_RSVP);
		register_setting('rsvp-option-group', OPTION_WELCOME_TEXT);
		register_setting('rsvp-option-group', OPTION_RSVP_QUESTION);
		register_setting('rsvp-option-group', OPTION_RSVP_CUSTOM_YES_NO);
		register_setting('rsvp-option-group', RSVP_OPTION_HIDE_NOTE);
		register_setting('rsvp-option-group', OPTION_RSVP_GUEST_EMAIL_CONFIRMATION);
		register_setting('rsvp-option-group', OPTION_RSVP_DISABLE_CUSTOM_EMAIL_FROM);
		register_setting('rsvp-option-group', OPTION_RSVP_EMAIL_TEXT);
		register_setting('rsvp-option-group', OPTION_DEBUG_RSVP_QUERIES);
    
		wp_register_script('jquery_table_sort', plugins_url('jquery.tablednd_0_5.js',RSVP_PLUGIN_FILE));
		wp_register_script('jquery_ui', rsvp_getHttpProtocol()."://ajax.microsoft.com/ajax/jquery.ui/1.8.5/jquery-ui.js");
		wp_register_style('jquery_ui_stylesheet', rsvp_getHttpProtocol()."://ajax.microsoft.com/ajax/jquery.ui/1.8.5/themes/redmond/jquery-ui.css");
	}
	
	function rsvp_admin_scripts() {
		wp_enqueue_script("jquery");
		wp_enqueue_script("jquery-ui-datepicker");
		wp_enqueue_script("jquery_table_sort");
		wp_enqueue_style( 'jquery_ui_stylesheet');
	}
	
	function rsvp_init() {
		wp_register_script('jquery_validate', rsvp_getHttpProtocol()."://ajax.aspnetcdn.com/ajax/jquery.validate/1.10.0/jquery.validate.min.js");
		wp_register_script('rsvp_plugin', plugins_url("rsvp_plugin.js", RSVP_PLUGIN_FILE));
		wp_register_style('rsvp_css', plugins_url("rsvp_plugin.css", RSVP_PLUGIN_FILE));
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery_validate');
		wp_enqueue_script('rsvp_plugin');
		wp_enqueue_style("rsvp_css");

		load_plugin_textdomain('rsvp-plugin', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}
	
	function rsvp_printQueryDebugInfo() {
		global $wpdb;
		
		if(get_option(OPTION_DEBUG_RSVP_QUERIES) == "Y" || 1==1) {
			echo "<br />Sql Output: ";
			$wpdb->print_error();
			echo "<br />";
		}
	}
	
	/*
	This function checks to see if the page is running over SSL/HTTPs and will return the proper HTTP protocol.
	
	Postcondition: The caller will receive the proper HTTP protocol to use at the beginning of a URL. 
	*/
	function rsvp_getHttpProtocol() {
		if(isset($_SERVER['HTTPS'])  && (trim($_SERVER['HTTPS']) != "") && (strtolower(trim($_SERVER['HTTPS'])) != "off")) {
			return "https";
		}
		return "http";
	}
  
	function rsvp_getCurrentPageURL() {
		$pageURL = rsvp_getHttpProtocol();

		$pageURL .= "://";
		$url = get_site_url();
		$server_info = parse_url($url);
		$domain = $server_info['host'];
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $domain.":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $domain.$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
  
	function rsvpshortcode_func($atts) {
		return rsvp_frontend_handler("my-rsvp-plugin");
	}
	add_shortcode( 'rsvp', 'rsvpshortcode_func' );

	add_action('admin_menu', 'rsvp_modify_menu');
	add_action('admin_init', 'rsvp_register_settings');
	add_action('init', 'rsvp_init');
	add_filter('the_content', 'rsvp_frontend_handler');
	register_activation_hook(__FILE__,'rsvp_database_setup');
?>
