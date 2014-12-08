<?php
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
					
					<tr valign="top">
						<th scope="row"><label for="rsvp_debug_queries">Debug SQL Queries</label></th>
						<td align="left"><input type="checkbox" name="rsvp_debug_queries" id="rsvp_debug_queries" value="Y" 
							<?php echo ((get_option(OPTION_DEBUG_RSVP_QUERIES) == "Y") ? " checked=\"checked\"" : ""); ?> /></td>
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
?>