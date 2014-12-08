<?php 
$rsvp_form_action = htmlspecialchars(rsvp_getCurrentPageURL());

$rsvp_saved_form_vars = array();
// load some defaults
$rsvp_saved_form_vars['mainRsvp'] = "";
$rsvp_saved_form_vars['rsvp_note'] = "";

function rsvp_handle_output ($intialText, $text) {	
	$text = "<a name=\"rsvpArea\" id=\"rsvpArea\"></a>".$text;
	
	//Remove the filter that turns double newline to paragraphs.
	remove_filter("the_content", "wpautop");
	return str_replace(RSVP_FRONTEND_TEXT_CHECK, $text, $intialText);
}

function rsvp_frontend_handler($text) {
	global $wpdb; 
		
	//QUIT if the replacement string doesn't exist
	if (!strstr($text,RSVP_FRONTEND_TEXT_CHECK)) return $text;
	
	// See if we should allow people to RSVP, etc...
	$openDate = get_option(OPTION_OPENDATE);
	$closeDate = get_option(OPTION_DEADLINE);
	if((strtotime($openDate) !== false) && (strtotime($openDate) > time())) {
		return rsvp_handle_output($text, sprintf(__(RSVP_START_PARA."I am sorry but the ability to RSVP for our wedding won't open till <strong>%s</strong>".RSVP_END_PARA, 'rsvp-plugin'), date("m/d/Y", strtotime($openDate))));
	} 
	
	if((strtotime($closeDate) !== false) && (strtotime($closeDate) < time())) {
		return rsvp_handle_output($text, __(RSVP_START_PARA."The deadline to RSVP for this wedding has passed, please contact the bride and groom to see if there is still a seat for you.".RSVP_END_PARA, 'rsvp-plugin'));
	}
	
	if(isset($_POST['rsvpStep'])) {
		$output = "";
		switch(strtolower($_POST['rsvpStep'])) {
			case("handlersvp") :
				$output = rsvp_handlersvp($output, $text);
				if(!empty($output)) 
					return $output;
				break;
			case("editfamily") :
				$output = rsvp_editFamily($output, $text);
				if(!empty($output)) 
					return $output;
				break;
			case("find") :
				$output = rsvp_find($output, $text);
				if(!empty($output))
					return $output;
				break;
			case("newsearch"):
			default:
				return rsvp_handle_output($text, rsvp_frontend_greeting());
				break;
		}
	} else {
		return rsvp_handle_output($text, rsvp_frontend_greeting());
	}
}

function rsvp_handlenewattendee($output, $text) {
	$output = RSVP_START_CONTAINER;
	$output .= rsvp_frontend_main_form(0, "addAttendee");
	$output .= RSVP_END_CONTAINER;

	return rsvp_handle_output($text, $output);
}

function rsvp_frontend_prompt_to_edit($family) {
	global $rsvp_form_action;
	$prompt = RSVP_START_CONTAINER; 
	$editGreeting = __("Hi %s it looks like you have already RSVP'd. Would you like to edit your reservation?", 'rsvp-plugin');
	$prompt .= sprintf(RSVP_START_PARA.$editGreeting.RSVP_END_PARA, htmlspecialchars(stripslashes($family->name." ".$family->lastName)));
	$prompt .= "<form method=\"post\" action=\"$rsvp_form_action\">\r\n
					<input type=\"hidden\" name=\"familyID\" value=\"".$family->id."\" />
					<input type=\"hidden\" name=\"rsvpStep\" id=\"rsvpStep\" value=\"editfamily\" />
					<input type=\"submit\" value=\"".__("Yes", 'rsvp-plugin')."\" onclick=\"document.getElementById('rsvpStep').value='editfamily';\" />
					<input type=\"submit\" value=\"".__("No", 'rsvp-plugin')."\" onclick=\"document.getElementById('rsvpStep').value='newsearch';\"  />
				</form>\r\n";
	$prompt .= RSVP_END_CONTAINER;
	return $prompt;
}

function rsvp_frontend_main_form($familyID, $rsvpStep = "handleRsvp") {
	global $wpdb, $rsvp_form_action, $rsvp_saved_form_vars;
	$family = $wpdb->get_row($wpdb->prepare("SELECT id, pin, date, ip, email, alias, comments FROM ".FAMILIES_TABLE." WHERE id = %d", $familyID));

	$yesVerbiage = ((trim(get_option(OPTION_YES_VERBIAGE)) != "") ? get_option(OPTION_YES_VERBIAGE) : 
	__("Yes, of course I will be there! Who doesn't like family, friends, weddings, and a good time?", 'rsvp-plugin'));
	
	$noVerbiage = ((trim(get_option(OPTION_NO_VERBIAGE)) != "") ? get_option(OPTION_NO_VERBIAGE) : 
	__("Um, unfortunately, there is a Star Trek marathon on that day that I just cannot miss.", 'rsvp-plugin'));

	$noteVerbiage = ((trim(get_option(OPTION_NOTE_VERBIAGE)) != "") ? get_option(OPTION_NOTE_VERBIAGE) : 
	__("If you have any <strong style=\"color:red;\">food allergies</strong>, please indicate what they are in the &quot;notes&quot; section below.  Or, if you just want to send us a note, please feel free.  If you have any questions, please send us an email.", 'rsvp-plugin'));

	$form = "<form id=\"rsvpForm\" name=\"rsvpForm\" method=\"post\" action=\"$rsvp_form_action\" autocomplete=\"off\">";
	$form .= "	<input type=\"hidden\" name=\"familyID\" value=\"".$familyID."\" />";
	$form .= "	<input type=\"hidden\" name=\"rsvpStep\" value=\"$rsvpStep\" />";

	$sql = "SELECT id, family, name, attending, food FROM ".ATTENDEES_TABLE." WHERE family = %s;";
	
	if(trim(get_option(OPTION_RSVP_QUESTION)) != "") {
		$question = trim(get_option(OPTION_RSVP_QUESTION));
	} else {
		$question = __("So, how about it?", 'rsvp-plugin');
	}
	
	$attendees = $wpdb->get_results($wpdb->prepare($sql, $familyID));
	if(count($attendees) > 0) {
		foreach($attendees as $a) {
			$form .= "<fieldset class=\"rsvp\">".
                     "<legend class=\"rsvp\">". htmlspecialchars($a->name) ."</legend>";
					 

			$form .="<label for=\"attending".$a->id."Y\" class=\"rsvp\">$question</label>".
					"<select name=\"attending".$a->id."\" id=\"attending".$a->id."\" class=\"rsvp\"/>".
						"<option value=\"Y\"". (($a->attending == "Yes") ? " selected=\"selected\"" : "") . " class=\"rsvp\">$yesVerbiage</option>".
						"<option value=\"N\"". (($a->attending == "No") ? " selected=\"selected\"" : "") . " class=\"rsvp\">$noVerbiage</option>".
					"</select>".
					"<label for=\"food".$a->id."\" class=\"rsvp\">Food</label>".
					"<select name=\"food".$a->id."\" id=\"food\" size=\"1\" class=\"rsvp\">".
						"<option value=\"NoResponse\"" . (($a->food == "NoResponse") ? " selected=\"selected\"" : "") . " class=\"rsvp\">No Response</option>".
						"<option value=\"Meat\"" . (($a->food == "Meat") ? " selected=\"selected\"" : "") . " class=\"rsvp\">Meat</option>".									
						"<option value=\"Fish\"" . (($a->food == "Fish") ? " selected=\"selected\"" : "") . " class=\"rsvp\">Fish</option>".
						"<option value=\"Veg\"" . (($a->food == "Veg") ? " selected=\"selected\"" : "") . " class=\"rsvp\">Vegetarian</option>".
					"</select>";
			$form .= "</fieldset>\r\n"; 
		}
	}
	$form .= "<fieldset class=\"rsvp\">".
		 "<legend class=\"rsvp\">More Info</legend>";

	$form .= "<label for=\"email\" class=\"rsvp\">".__("Email Address", 'rsvp-plugin')."</label>".
			 "<input type=\"text\" name=\"email\" id=\"email\" value=\"".htmlspecialchars($family->email)."\"  class=\"rsvp\"/><br />";
	$form .= '<p style="font-size: 12px; padding-left: 150px">(We\'ll use your email address in the unlikely event of any changes to wedding timelines, parking, etc.)</p>';
	
  	$form .= "<label for=\"comments\" class=\"rsvp fullwidth\">".$noteVerbiage."</label><br />".
			 "<textarea name=\"comments\" id=\"comments\" rows=\"7\" cols=\"50\" class=\"rsvp fullwidth\">".((!empty($family->comments)) ? $family->comments : "")."</textarea>";
	$form .= "</fieldset>\r\n"; 
	$form .= "<div class=\"fm-submit\">\r\n"; 
	$form .= RSVP_START_PARA."<input type=\"submit\" value=\"RSVP\"  class=\"rsvp\"/>".RSVP_END_PARA;
	$form .= "</div></form>\r\n";
	
	return $form;
}

function rsvp_find(&$output, &$text) {
	global $wpdb, $rsvp_form_action;

	$pin = "";
	if(isset($_REQUEST['pin'])) {
		$pin = $_REQUEST['pin'];
	}

  	$family = $wpdb->get_row($wpdb->prepare("SELECT id, pin, date, ip, email, alias, comments   FROM ".FAMILIES_TABLE." 
																								WHERE pin = %s", $pin));
		
	rsvp_printQueryDebugInfo();
	if($family != null) {
		// hey we found something, we should move on and print out any associated users and let them rsvp
		$output = "<div>\r\n";
		$output .= RSVP_START_PARA."Hi ".htmlspecialchars(stripslashes($family->alias))."!".RSVP_END_PARA;
						
		if(trim(get_option(OPTION_WELCOME_TEXT)) != "") {
			$output .= RSVP_START_PARA.trim(get_option(OPTION_WELCOME_TEXT)).RSVP_END_PARA;
		} else {
			$output .= RSVP_START_PARA.__("There are a few more questions we need to ask you if you could please fill them out below to finish up the RSVP process.", 'rsvp-plugin').RSVP_END_PARA;
		}
						
		$output .= rsvp_frontend_main_form($family->id);

		return rsvp_handle_output($text, $output."</div>\r\n");
	}

	$notFoundText = sprintf(__(RSVP_START_PARA.'<strong>We were unable to find anyone with the password you specified.</strong>'.RSVP_END_PARA, 'rsvp-plugin'));
  	
	$notFoundText .= rsvp_frontend_greeting();
	return rsvp_handle_output($text, $notFoundText);
}

function rsvp_handlersvp(&$output, &$text) {
	global $wpdb;

	if(is_numeric($_POST['familyID']) && ($_POST['familyID'] > 0)) {
		$familyID = $_POST['familyID'];
		
		// Get Attendee first name
		$wpdb->update(FAMILIES_TABLE, array("comments" => $_POST['comments'],
							"email" => $_POST['email'],
							"ip" => $_SERVER['REMOTE_ADDR']), 
							array("id" => $familyID), 
							array("%s", "%s", "%s"), 
							array("%d"));
								
								
		$sql = "SELECT alias, comments, email, ip FROM ".FAMILIES_TABLE." 
				WHERE id = %s";
		$family = $wpdb->get_row($wpdb->prepare($sql, $familyID));

		$sql = "SELECT id, name, attending, food FROM ".ATTENDEES_TABLE." 
				WHERE family = %s";
		$attendees = $wpdb->get_results($wpdb->prepare($sql, $familyID));


		foreach($attendees as $a) {
			if(isset($_POST['attending'.$a->id]) && (($_POST['attending'.$a->id] == "Y") || ($_POST['attending'.$a->id] == "N"))) {
				if($_POST['attending'.$a->id] == "Y") {
					$rsvpStatus = "Yes";
				} else {
					$rsvpStatus = "No";
				}
				$wpdb->update(ATTENDEES_TABLE, array("attending" => $rsvpStatus,
								"food" => $_POST['food'.$a->id]), 
								array("id" => $a->id), 
								array("%s", "%s", "%s", "%s", "%s"), 
								array("%d"));
			
				rsvp_printQueryDebugInfo();
			}
		}

		$email = get_option(OPTION_NOTIFY_EMAIL);
    
		if((get_option(OPTION_NOTIFY_ON_RSVP) == "Y") && ($email != "")) {
			
			$body = "Hello, \r\n\r\n";
						
			$body .= stripslashes($family->alias)." has submitted their RSVP.\r\n\r\n";
			
			if(get_option(RSVP_OPTION_HIDE_NOTE) != "Y") {
				$body .= "Note: ".stripslashes($family->comments)."\r\n";
			}

			if(count($attendees) > 0) {
				$body .= "\r\n\r\n--== Attendees ==--\r\n";
				foreach($attendees as $a) {
					$body .= stripslashes($a->name)." RSVP status: ".$a->attending." Food: ".$a->food."\r\n";
				}
			}
			$headers = "";
			if(get_option(OPTION_RSVP_DISABLE_CUSTOM_EMAIL_FROM) != "Y")
				$headers = 'From: '. $email . "\r\n";		
	
			wp_mail($email, "New RSVP Submission", $body, $headers);
		}
    
		if((get_option(OPTION_RSVP_GUEST_EMAIL_CONFIRMATION) == "Y") && !empty($family->email)) {
			$body = "Hello ".stripslashes($family->alias).", \r\n\r\n";

			if(get_option(OPTION_RSVP_EMAIL_TEXT) != "") {
				$body .= "\r\n";
				$body .= get_option(OPTION_RSVP_EMAIL_TEXT);
				$body .= "\r\n";
			}

			$body .= "You have successfully RSVP'd.";

			if(count($attendees) > 0) {
				foreach($attendees as $a) {
					$body .= "\r\n\r\n--== Attendees ==--\r\n";
					$body .= stripslashes($a->name)." RSVP Status: ".$a->attending." Food Choice: ".$a->food."\r\n";
				}
			}
			$headers = "";
			if(!empty($email) && (get_option(OPTION_RSVP_DISABLE_CUSTOM_EMAIL_FROM) != "Y")) {
				$headers = 'From: '. $email . "\r\n";		
			}
			wp_mail($family->email, "RSVP Confirmation", $body, $headers);
		}
		//TODO Add thank you note
		$thankYouPrimary = "";
		$thankYouAssociated = array();
		return rsvp_handle_output($text, frontend_rsvp_thankyou($thankYouPrimary, $thankYouAssociated));
	} else {
		return rsvp_handle_output($text, rsvp_frontend_greeting());
	}
}

function rsvp_editFamily(&$output, &$text) {
	global $wpdb;
	
	if(is_numeric($_POST['familyID']) && ($_POST['familyID'] > 0)) {
		// Try to find the user.
		$family = $wpdb->get_row($wpdb->prepare("SELECT id, pin, date, ip, email, alias, comments 
													FROM ".FAMILIES_TABLE." 
													WHERE id = %d", $_POST['familyID']));
		if($family != null) {
			$output .= RSVP_START_CONTAINER;
			$output .= RSVP_START_PARA.__("Welcome back", 'rsvp-plugin')." ".htmlspecialchars($family->alias)."!".RSVP_END_PARA;
			$output .= rsvp_frontend_main_form($family->id);
			return rsvp_handle_output($text, $output.RSVP_END_CONTAINER);
		}
	}
}

function frontend_rsvp_thankyou($thankYouPrimary, $thankYouAssociated) {
	$customTy = get_option(OPTION_THANKYOU);
	if(!empty($customTy)) {
		return nl2br($customTy);
	} else {    
		$tyText = __("Thank you", 'rsvp-plugin');
		if(!empty($thankYouPrimary)) {
			$tyText .= " ".htmlspecialchars($thankYouPrimary);
		}
		$tyText .= __(" for RSVPing.", 'rsvp-plugin');

		if(count($thankYouAssociated) > 0) {
			$tyText .= __(" You have also RSVPed for - ", 'rsvp-plugin');
			foreach($thankYouAssociated as $name) {
				$tyText .= htmlspecialchars(" ".$name).", ";
			}
			$tyText = rtrim(trim($tyText), ",").".";
		}
		return RSVP_START_CONTAINER.RSVP_START_PARA.$tyText.RSVP_END_PARA.RSVP_END_CONTAINER;
	}
}

function rsvp_BeginningFormField($id, $additionalClasses) {
	return "<div ".(!empty($id) ? "id=\"$id\"" : "")." class=\"rsvpFormField ".(!empty($additionalClasses) ? $additionalClasses : "")."\">";
}

function rsvp_frontend_greeting() {
	global $rsvp_form_action;
	$customGreeting = get_option(OPTION_GREETING);
	
	$output = RSVP_START_PARA.__("Please enter your passcode to RSVP.", 'rsvp-plugin').RSVP_END_PARA;
		
	$firstName = "";
	$lastName = "";
	$passcode = "";
	if(isset($_SESSION['rsvpFirstName'])) {
		$firstName = $_SESSION['rsvpFirstName'];
	}
	if(isset($_SESSION['rsvpLastName'])) {
		$lastName = $_SESSION['rsvpLastName'];
	}
	if(isset($_SESSION['rsvpPin'])) {
		$pin = $_SESSION['rsvpPin'];
	}
	if(!empty($customGreeting)) {
		$output = RSVP_START_PARA.nl2br($customGreeting).RSVP_END_PARA;
	} 
  
	$output .= RSVP_START_CONTAINER;

	$output .= "<form name=\"rsvp\" method=\"post\" id=\"rsvp\" action=\"$rsvp_form_action\" autocomplete=\"off\">\r\n";
	$output .= "	<input type=\"hidden\" name=\"rsvpStep\" value=\"find\" />";

	$output .= "<label for=\"pin\" class=\"rsvp\">".__("PIN", 'rsvp-plugin').":</label> 
									<input type=\"password\" name=\"pin\" id=\"pin\" size=\"30\" value=\"".htmlspecialchars($pin)."\" class=\"required rsvp\" autocomplete=\"off\" />";
	
	$output .= "<br /><br /><input type=\"submit\" value=\"".__("Complete your RSVP!", 'rsvp-plugin')."\" />";
	$output .= "</form>\r\n";
	$output .= RSVP_END_CONTAINER;
	return $output;
}
?>
