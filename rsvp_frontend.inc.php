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
			case("newattendee"):
				return rsvp_handlenewattendee($output, $text);
				break;
			case("addattendee"):
				return rsvp_handleNewRsvp($output, $text);
				break;
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
			case("foundattendee") :
				$output = rsvp_foundAttendee($output, $text);
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
	
	$form .= RSVP_START_PARA;
	if(trim(get_option(OPTION_RSVP_QUESTION)) != "") {
		$form .= trim(get_option(OPTION_RSVP_QUESTION));
	} else {
		$form .= __("So, how about it?", 'rsvp-plugin');
	}
	$form .= RSVP_END_PARA;
	
    $form .= rsvp_BeginningFormField("", "rsvpBorderTop").
      RSVP_START_PARA."<label for=\"mainEmail\">".__("Email Address", 'rsvp-plugin')."</label>".RSVP_END_PARA.
        "<input type=\"text\" name=\"mainEmail\" id=\"mainEmail\" value=\"".htmlspecialchars($attendee->email)."\" />".
      RSVP_END_FORM_FIELD;
	
	
  	$form .= RSVP_START_PARA.$noteVerbiage.RSVP_END_PARA.
      rsvp_BeginningFormField("", "").
        "<textarea name=\"rsvp_note\" id=\"rsvp_note\" rows=\"7\" cols=\"50\">".((!empty($attendee->note)) ? $attendee->note : $rsvp_saved_form_vars['rsvp_note'])."</textarea>".RSVP_END_FORM_FIELD;
	
	
	$sql = "SELECT id, family, name, attending, food FROM ".ATTENDEES_TABLE." WHERE family = %s;";
	
	$attendees = $wpdb->get_results($wpdb->prepare($sql, $familyID));
	if(count($attendees) > 0) {
		$form .= "<h3>".__("The following people are associated with you.  At this time you can RSVP for them as well.", 'rsvp-plugin')."</h3>";
		foreach($attendees as $a) {
			$form .= "<div class=\"rsvpAdditionalAttendee\">\r\n";
			$form .= "<div class=\"rsvpAdditionalAttendeeQuestions\">\r\n";
			$form .= rsvp_BeginningFormField("", "").RSVP_START_PARA.sprintf(__(" Will %s be attending?", 'rsvp-plugin'), htmlspecialchars($a->name)).RSVP_END_PARA.
					"<input type=\"radio\" name=\"attending".$a->id."\" value=\"Yes\" id=\"attending".$a->id."Y\" " . (($a->attending == "Yes") ? " checked=\"checked\"" : "") . "/>".
					"<label for=\"attending".$a->id."Y\">$yesVerbiage</label> <br />".
					"<input type=\"radio\" name=\"attending".$a->id."\" value=\"No\" id=\"attending".$a->id."N\" " . (($a->attending == "No") ? " checked=\"checked\"" : "") . "/>".
					"<label for=\"attending".$a->id."N\">$noVerbiage</label> <br />".
					"<label for=\"food".$a->id."\">Food</label><select name=\"food".$a->id."\" id=\"food\" size=\"1\">".
								"<option value=\"NoResponse\"" . (($a->food == "NoResponse") ? " selected=\"selected\"" : "") . ">No Response</option>".
								"<option value=\"Meat\"" . (($a->food == "Meat") ? " selected=\"selected\"" : "") . ">Meat</option>".									
								"<option value=\"Fish\"" . (($a->food == "Fish") ? " selected=\"selected\"" : "") . ">Fish</option>".
								"<option value=\"Veg\"" . (($a->food == "Veg") ? " selected=\"selected\"" : "") . ">Vegetarian</option>".
							"</select>".
					RSVP_END_FORM_FIELD;

			$form .= "</div>\r\n"; //-- rsvpAdditionalAttendeeQuestions
			$form .= "</div>\r\n";
		}
	}
	
						
	$form .= RSVP_START_PARA."<input type=\"submit\" value=\"RSVP\" />".RSVP_END_PARA;
	$form .= "</form>\r\n";
	
	return $form;
}

function rsvp_revtrievePreviousAnswer($attendeeID, $questionID) {
	global $wpdb;
	$answers = "";
	if(($attendeeID > 0) && ($questionID > 0)) {
		$rs = $wpdb->get_results($wpdb->prepare("SELECT answer FROM ".ATTENDEE_ANSWERS." WHERE questionID = %d AND attendeeID = %d", $questionID, $attendeeID));
		if(count($rs) > 0) {
			$answers = stripslashes($rs[0]->answer);
		}
	}
	
	return $answers;
}

function rsvp_buildAdditionalQuestions($attendeeID, $prefix) {
	global $wpdb, $rsvp_saved_form_vars;
	$output = "<div class=\"rsvpCustomQuestions\">";
	
	$sql = "SELECT q.id, q.question, questionType FROM ".QUESTIONS_TABLE." q 
					INNER JOIN ".QUESTION_TYPE_TABLE." qt ON qt.id = q.questionTypeID 
					WHERE q.permissionLevel = 'public' 
					  OR (q.permissionLevel = 'private' AND q.id IN (SELECT questionID FROM ".QUESTION_ATTENDEES_TABLE." WHERE attendeeID = $attendeeID))
					ORDER BY q.sortOrder ";
  $questions = $wpdb->get_results($sql);
	if(count($questions) > 0) {
		foreach($questions as $q) {
			$oldAnswer = rsvp_revtrievePreviousAnswer($attendeeID, $q->id);
			
			$output .= rsvp_BeginningFormField("", "").RSVP_START_PARA.stripslashes($q->question).RSVP_END_PARA;
				
				if($q->questionType == QT_MULTI) {
					$oldAnswers = explode("||", $oldAnswer);
					
					$answers = $wpdb->get_results($wpdb->prepare("SELECT id, answer FROM ".QUESTION_ANSWERS_TABLE." WHERE questionID = %d", $q->id));
					if(count($answers) > 0) {
						$i = 0;
						foreach($answers as $a) {
							$output .= rsvp_BeginningFormField("", "rsvpCheckboxCustomQ")."<input type=\"checkbox\" name=\"".$prefix."question".$q->id."[]\" id=\"".$prefix."question".$q->id.$a->id."\" value=\"".$a->id."\" "
							  .((in_array(stripslashes($a->answer), $oldAnswers)) ? " checked=\"checked\"" : "")." />".
                "<label for=\"".$prefix."question".$q->id.$a->id."\">".stripslashes($a->answer)."</label>\r\n".RSVP_END_FORM_FIELD;
							$i++;
						}
            $output .= "<div class=\"rsvpClear\">&nbsp;</div>\r\n";
					}
				} else if ($q->questionType == QT_DROP) {
					//$oldAnswers = explode("||", $oldAnswer);
					
					$output .= "<select name=\"".$prefix."question".$q->id."\" size=\"1\">\r\n".
						"<option value=\"\">--</option>\r\n";
					$answers = $wpdb->get_results($wpdb->prepare("SELECT id, answer FROM ".QUESTION_ANSWERS_TABLE." WHERE questionID = %d", $q->id));
					if(count($answers) > 0) {
						foreach($answers as $a) {
							$output .= "<option value=\"".$a->id."\" ".((stripslashes($a->answer) == $oldAnswer) ? " selected=\"selected\"" : "").">".stripslashes($a->answer)."</option>\r\n";
						}
					}
					$output .= "</select>\r\n";
				} else if ($q->questionType == QT_LONG) {
					$output .= "<textarea name=\"".$prefix."question".$q->id."\" rows=\"5\" cols=\"35\">".htmlspecialchars($oldAnswer)."</textarea>";
				} else if ($q->questionType == QT_RADIO) {
					//$oldAnswers = explode("||", $oldAnswer);
					$answers = $wpdb->get_results($wpdb->prepare("SELECT id, answer FROM ".QUESTION_ANSWERS_TABLE." WHERE questionID = %d", $q->id));
					if(count($answers) > 0) {
						$i = 0;
						$output .= RSVP_START_PARA;
						foreach($answers as $a) {
							$output .= "<input type=\"radio\" name=\"".$prefix."question".$q->id."\" id=\"".$prefix."question".$q->id.$a->id."\" value=\"".$a->id."\" "
							  .((stripslashes($a->answer) == $oldAnswer) ? " checked=\"checked\"" : "")." /> ".
              "<label for=\"".$prefix."question".$q->id.$a->id."\">".stripslashes($a->answer)."</label>\r\n";
							$i++;
						}
						$output .= RSVP_END_PARA;
					}
				} else {
					// normal text input
					$output .= "<input type=\"text\" name=\"".$prefix."question".$q->id."\" value=\"".htmlspecialchars($oldAnswer)."\" size=\"25\" />";
				}
				
			$output .= RSVP_END_FORM_FIELD;
		}
	}
	
	return $output."</div>";
}

function rsvp_find(&$output, &$text) {
	global $wpdb, $rsvp_form_action;

	$pin = "";
	if(isset($_REQUEST['pin'])) {
		$pin = $_REQUEST['pin'];
	}

  	$family = $wpdb->get_row($wpdb->prepare("SELECT id, pin, date, ip, email, alias, comments   FROM ".FAMILIES_TABLE." 
																								WHERE pin = %s", $pin));
	
	printf("SELECT id, pin, date, ip, email, alias, comments   FROM ".FAMILIES_TABLE." WHERE pin = %s", $pin);
	
	print_r($family);
	rsvp_printQueryDebugInfo();
	if($family != null) {
		// hey we found something, we should move on and print out any associated users and let them rsvp
		$output = "<div>\r\n";
		if(strtolower($family->rsvpStatus) == "noresponse") {
			$output .= RSVP_START_PARA."Hi ".htmlspecialchars(stripslashes($family->firstName." ".$family->lastName))."!".RSVP_END_PARA;
						
			if(trim(get_option(OPTION_WELCOME_TEXT)) != "") {
				$output .= RSVP_START_PARA.trim(get_option(OPTION_WELCOME_TEXT)).RSVP_END_PARA;
			} else {
				$output .= RSVP_START_PARA.__("There are a few more questions we need to ask you if you could please fill them out below to finish up the RSVP process.", 'rsvp-plugin').RSVP_END_PARA;
			}
						
			$output .= rsvp_frontend_main_form($family->id);
		} else {
			$output .= rsvp_frontend_prompt_to_edit($family);
		}
		
		return rsvp_handle_output($text, $output."</div>\r\n");
	}

	$notFoundText = sprintf(__(RSVP_START_PARA.'<strong>We were unable to find anyone with the password you specified.</strong>'.RSVP_END_PARA, 'rsvp-plugin'));
  	
	$notFoundText .= rsvp_frontend_greeting();
	return rsvp_handle_output($text, $notFoundText);
}

function rsvp_handleNewRsvp(&$output, &$text) {
  global $wpdb, $rsvp_saved_form_vars;
  $thankYouPrimary = "";
  $thankYouAssociated = array();
  foreach($_POST as $key=>$val) {
    $rsvp_saved_form_vars[$key] = $val;
  }
  
  if(empty($_POST['attendeeFirstName']) || empty($_POST['attendeeLastName'])) {
    return rsvp_handlenewattendee($output, $text);
  }
  
  $rsvpPassword = "";
  $rsvpStatus = "No";
	if(strToUpper($_POST['mainRsvp']) == "Y") {
		$rsvpStatus = "Yes";
	}
  $kidsMeal = ((isset($_POST['mainKidsMeal']) && (strToUpper($_POST['mainKidsMeal']) == "Y")) ? "Y" : "N");
  $veggieMeal = ((isset($_POST['mainVeggieMeal']) && (strToUpper($_POST['mainVeggieMeal']) == "Y")) ? "Y" : "N");
  $thankYouPrimary = $_POST['attendeeFirstName'];
	$wpdb->insert(ATTENDEES_TABLE, array("rsvpDate" => date("Y-m-d"), 
                                       "firstName" => $_POST['attendeeFirstName'], 
                                       "lastName"  => $_POST['attendeeLastName'], 
                                       "email"     => $_POST['mainEmail'], 
                                       "rsvpStatus" => $rsvpStatus, 
                                       "note" => $_POST['rsvp_note'], 
                                       "kidsMeal" => $kidsMeal, 
                                       "veggieMeal" => $veggieMeal), 
																 array("%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s"));
	rsvp_printQueryDebugInfo();									
  $attendeeID = $wpdb->insert_id;
  
	if(rsvp_require_passcode()) {
    $rsvpPassword = trim(rsvp_generate_passcode());
		$wpdb->update(ATTENDEES_TABLE, 
									array("passcode" => $rsvpPassword), 
									array("id"=>$attendeeID), 
									array("%s"), 
									array("%d"));
	}
  
	rsvp_handleAdditionalQuestions($attendeeID, "mainquestion");
																			
	$sql = "SELECT id, firstName FROM ".ATTENDEES_TABLE." 
	 	WHERE (id IN (SELECT attendeeID FROM ".ASSOCIATED_ATTENDEES_TABLE." WHERE associatedAttendeeID = %d) 
			OR id in (SELECT associatedAttendeeID FROM ".ASSOCIATED_ATTENDEES_TABLE." WHERE attendeeID = %d)) ";
	$associations = $wpdb->get_results($wpdb->prepare($sql, $attendeeID, $attendeeID));
	foreach($associations as $a) {
		if(isset($_POST['attending'.$a->id]) && (($_POST['attending'.$a->id] == "Y") || ($_POST['attending'.$a->id] == "N"))) {
			if($_POST['attending'.$a->id] == "Y") {
				$rsvpStatus = "Yes";
			} else {
				$rsvpStatus = "No";
			}
      $thankYouAssociated[] = stripslashes($a->firstName);
      if(get_option(OPTION_RSVP_HIDE_EMAIL_FIELD) != "Y") { 
  			$wpdb->update(ATTENDEES_TABLE, array("rsvpDate" => date("Y-m-d"), 
  							"rsvpStatus" => $rsvpStatus, 
                "email" => $_POST['attending'.$a->id."Email"], 
  							"kidsMeal" => ((strToUpper((isset($_POST['attending'.$a->id.'KidsMeal']) ? $_POST['attending'.$a->id.'KidsMeal'] : "N")) == "Y") ? "Y" : "N"), 
  							"veggieMeal" => ((strToUpper((isset($_POST['attending'.$a->id.'VeggieMeal']) ? $_POST['attending'.$a->id.'VeggieMeal'] : "N")) == "Y") ? "Y" : "N")),
  							array("id" => $a->id), 
  							array("%s", "%s", "%s", "%s", "%s"), 
  							array("%d"));
          
      } else {
  			$wpdb->update(ATTENDEES_TABLE, array("rsvpDate" => date("Y-m-d"), 
  							"rsvpStatus" => $rsvpStatus, 
  							"kidsMeal" => ((strToUpper((isset($_POST['attending'.$a->id.'KidsMeal']) ? $_POST['attending'.$a->id.'KidsMeal'] : "N")) == "Y") ? "Y" : "N"), 
  							"veggieMeal" => ((strToUpper((isset($_POST['attending'.$a->id.'VeggieMeal']) ? $_POST['attending'.$a->id.'VeggieMeal'] : "N")) == "Y") ? "Y" : "N")),
  							array("id" => $a->id), 
  							array("%s", "%s", "%s", "%s"), 
  							array("%d"));
        
      }
			rsvp_printQueryDebugInfo();
			rsvp_handleAdditionalQuestions($a->id, $a->id."question");
		}
	}
				
	if(get_option(OPTION_HIDE_ADD_ADDITIONAL) != "Y") {
		if(is_numeric($_POST['additionalRsvp']) && ($_POST['additionalRsvp'] > 0)) {
			for($i = 1; $i <= $_POST['additionalRsvp']; $i++) {
        $numGuests = 3;
        if(get_option(OPTION_RSVP_NUM_ADDITIONAL_GUESTS) != "") {
          $numGuests = get_optioN(OPTION_RSVP_NUM_ADDITIONAL_GUESTS);
          if(!is_numeric($numGuests) || ($numGuests < 0)) {
            $numGuests = 3;
          }
        }
				if(($i <= $numGuests) && 
				   !empty($_POST['newAttending'.$i.'FirstName']) && 
				   !empty($_POST['newAttending'.$i.'LastName'])) {		
          $thankYouAssociated[] = $_POST['newAttending'.$i.'FirstName'];
					$wpdb->insert(ATTENDEES_TABLE, array("firstName" => trim($_POST['newAttending'.$i.'FirstName']), 
									"lastName" => trim($_POST['newAttending'.$i.'LastName']), 
                  "email" => trim($_POST['newAttending'.$i."Email"]), 
									"rsvpDate" => date("Y-m-d"), 
									"rsvpStatus" => (($_POST['newAttending'.$i] == "Y") ? "Yes" : "No"), 
									"kidsMeal" => (isset($_POST['newAttending'.$i.'KidsMeal']) ? $_POST['newAttending'.$i.'KidsMeal'] : "N"), 
									"veggieMeal" => (isset($_POST['newAttending'.$i.'VeggieMeal']) ? $_POST['newAttending'.$i.'VeggieMeal'] : "N"), 
									"additionalAttendee" => "Y"), 
									array('%s', '%s', '%s', '%s', '%s', '%s', '%s'));
					rsvp_printQueryDebugInfo();
					$newAid = $wpdb->insert_id;
					rsvp_handleAdditionalQuestions($newAid, $i.'question');
					// Add associations for this new user
					$wpdb->insert(ASSOCIATED_ATTENDEES_TABLE, array("attendeeID" => $newAid, 
										"associatedAttendeeID" => $attendeeID), 
										array("%d", "%d"));
					rsvp_printQueryDebugInfo();
					$wpdb->query("INSERT INTO ".ASSOCIATED_ATTENDEES_TABLE."(attendeeID, associatedAttendeeID)
																			 SELECT ".$newAid.", associatedAttendeeID 
																			 FROM ".ASSOCIATED_ATTENDEES_TABLE." 
																			 WHERE attendeeID = ".$attendeeID);
					rsvp_printQueryDebugInfo();
				}
			}
		}
	}
				
	if((get_option(OPTION_NOTIFY_ON_RSVP) == "Y") && (get_option(OPTION_NOTIFY_EMAIL) != "")) {
		$sql = "SELECT firstName, lastName, rsvpStatus, note, kidsMeal, veggieMeal FROM ".ATTENDEES_TABLE." WHERE id= ".$attendeeID;
		$attendee = $wpdb->get_results($sql);
		if(count($attendee) > 0) {
			$body = "Hello, \r\n\r\n";
						
			$body .= stripslashes($attendee[0]->firstName)." ".stripslashes($attendee[0]->lastName).
							 " has submitted their RSVP and has RSVP'd with '".$attendee[0]->rsvpStatus."'.\r\n";
      
      if(get_option(OPTION_HIDE_KIDS_MEAL) != "Y") {
        $body .= "Kids Meal: ".$attendee[0]->kidsMeal."\r\n";
      }
      
      if(get_option(OPTION_HIDE_VEGGIE) != "Y") {
        $body .= "Vegetarian Meal: ".$attendee[0]->veggieMeal."\r\n";
      }
      
      if(get_option(RSVP_OPTION_HIDE_NOTE) != "Y") {
        $body .= "Note: ".stripslashes($attendee[0]->note)."\r\n";
      }
      
			$sql = "SELECT question, answer FROM ".QUESTIONS_TABLE." q 
				LEFT JOIN ".ATTENDEE_ANSWERS." ans ON q.id = ans.questionID AND ans.attendeeID = %d 
				ORDER BY q.sortOrder, q.id";
			$aRs = $wpdb->get_results($wpdb->prepare($sql, $attendeeID));
			if(count($aRs) > 0) {
        $body .= "\r\n\r\n--== Custom Questions ==--\r\n";
				foreach($aRs as $a) {
          $body .= stripslashes($a->question).": ".stripslashes($a->answer)."\r\n";
				}
			}
      
			$sql = "SELECT firstName, lastName, rsvpStatus FROM ".ATTENDEES_TABLE." 
			 	WHERE id IN (SELECT attendeeID FROM ".ASSOCIATED_ATTENDEES_TABLE." WHERE associatedAttendeeID = %d) 
					OR id in (SELECT associatedAttendeeID FROM ".ASSOCIATED_ATTENDEES_TABLE." WHERE attendeeID = %d)";
		
			$associations = $wpdb->get_results($wpdb->prepare($sql, $attendeeID, $attendeeID));
      if(count($associations) > 0) {
  			foreach($associations as $a) {
          $body .= "\r\n\r\n--== Associated Attendees ==--\r\n";
          $body .= stripslashes($a->firstName." ".$a->lastName)." rsvp status: ".$a->rsvpStatus."\r\n";
  			}
      }
			
      $emailAddy = get_option(OPTION_NOTIFY_EMAIL);		
      $headers = "";
      if(get_option(OPTION_RSVP_DISABLE_CUSTOM_EMAIL_FROM) != "Y")	
        $headers = 'From: '. $emailAddy . "\r\n";
      
			wp_mail($emailAddy, "New RSVP Submission", $body, $headers);
		}
	}
  
  if((get_option(OPTION_RSVP_GUEST_EMAIL_CONFIRMATION) == "Y") && !empty($_POST['mainEmail'])) {
		$sql = "SELECT firstName, lastName, email, rsvpStatus FROM ".ATTENDEES_TABLE." WHERE id= ".$attendeeID;
		$attendee = $wpdb->get_results($sql);
		if(count($attendee) > 0) {
			$body = "Hello ".stripslashes($attendee[0]->firstName)." ".stripslashes($attendee[0]->lastName).", \r\n\r\n";
						
      if(get_option(OPTION_RSVP_EMAIL_TEXT) != "") {
        $body .= "\r\n";
        $body .= get_option(OPTION_RSVP_EMAIL_TEXT);
        $body .= "\r\n";
      }
            
			$body .= "You have successfully RSVP'd with '".$attendee[0]->rsvpStatus."'.";
      
			$sql = "SELECT firstName, lastName, rsvpStatus FROM ".ATTENDEES_TABLE." 
			 	WHERE id IN (SELECT attendeeID FROM ".ASSOCIATED_ATTENDEES_TABLE." WHERE associatedAttendeeID = %d) 
					OR id in (SELECT associatedAttendeeID FROM ".ASSOCIATED_ATTENDEES_TABLE." WHERE attendeeID = %d)";
		
			$associations = $wpdb->get_results($wpdb->prepare($sql, $attendeeID, $attendeeID));
      if(count($associations) > 0) {
  			foreach($associations as $a) {
          $body .= "\r\n\r\n--== Associated Attendees ==--\r\n";
          $body .= stripslashes($a->firstName." ".$a->lastName)." rsvp status: ".$a->rsvpStatus."\r\n";
  			}
      }
      $emailAddy = get_option(OPTION_NOTIFY_EMAIL);	
      $headers = "";
      if(!empty($emailAddy) && (get_option(OPTION_RSVP_DISABLE_CUSTOM_EMAIL_FROM) != "Y")) {
        $headers = 'From: '. $emailAddy . "\r\n";
      }
      wp_mail($attendee[0]->email, "RSVP Confirmation", $body, $headers);
    }
  }
				
	return rsvp_handle_output($text, rsvp_frontend_new_atendee_thankyou($thankYouPrimary, $thankYouAssociated, $rsvpPassword));
}

function rsvp_handlersvp(&$output, &$text) {
	global $wpdb;
  $thankYouPrimary = "";
  $thankYouAssociated = array();
	if(is_numeric($_POST['attendeeID']) && ($_POST['attendeeID'] > 0)) {
		// update their information and what not....
		if(strToUpper($_POST['mainRsvp']) == "Y") {
			$rsvpStatus = "Yes";
		} else {
			$rsvpStatus = "No";
		}
		$attendeeID = $_POST['attendeeID'];
    // Get Attendee first name
    $thankYouPrimary = $wpdb->get_var($wpdb->prepare("SELECT firstName FROM ".ATTENDEES_TABLE." WHERE id = %d", $attendeeID));
    if(get_option(OPTION_RSVP_HIDE_EMAIL_FIELD) != "Y") { 
  		$wpdb->update(ATTENDEES_TABLE, array("rsvpDate" => date("Y-m-d"), 
  						"rsvpStatus" => $rsvpStatus, 
  						"note" => $_POST['rsvp_note'],
              "email" => $_POST['mainEmail'],  
  						"kidsMeal" => ((isset($_POST['mainKidsMeal']) && (strToUpper($_POST['mainKidsMeal']) == "Y")) ? "Y" : "N"), 
  						"veggieMeal" => ((isset($_POST['mainVeggieMeal']) && (strToUpper($_POST['mainVeggieMeal']) == "Y")) ? "Y" : "N")), 
  																	array("id" => $attendeeID), 
  																	array("%s", "%s", "%s", "%s", "%s", "%s"), 
  																	array("%d"));
    } else {
  		$wpdb->update(ATTENDEES_TABLE, array("rsvpDate" => date("Y-m-d"), 
  						"rsvpStatus" => $rsvpStatus, 
  						"note" => $_POST['rsvp_note'],
  						"kidsMeal" => ((isset($_POST['mainKidsMeal']) && (strToUpper($_POST['mainKidsMeal']) == "Y")) ? "Y" : "N"), 
  						"veggieMeal" => ((isset($_POST['mainVeggieMeal']) && (strToUpper($_POST['mainVeggieMeal']) == "Y")) ? "Y" : "N")), 
  																	array("id" => $attendeeID), 
  																	array("%s", "%s", "%s", "%s", "%s"), 
  																	array("%d"));
    }
							
		rsvp_handleAdditionalQuestions($attendeeID, "mainquestion");
																				
		$sql = "SELECT id, firstName FROM ".ATTENDEES_TABLE." 
		 	WHERE (id IN (SELECT attendeeID FROM ".ASSOCIATED_ATTENDEES_TABLE." WHERE associatedAttendeeID = %d) 
				OR id in (SELECT associatedAttendeeID FROM ".ASSOCIATED_ATTENDEES_TABLE." WHERE attendeeID = %d)) ";
		$associations = $wpdb->get_results($wpdb->prepare($sql, $attendeeID, $attendeeID));
		foreach($associations as $a) {
      if(isset($_POST['attending'.$a->id]) && (($_POST['attending'.$a->id] == "Y") || ($_POST['attending'.$a->id] == "N"))) {
        $thankYouAssociated[] = $a->firstName;
				if($_POST['attending'.$a->id] == "Y") {
					$rsvpStatus = "Yes";
				} else {
					$rsvpStatus = "No";
				}
        if(get_option(OPTION_RSVP_HIDE_EMAIL_FIELD) != "Y") { 
  				$wpdb->update(ATTENDEES_TABLE, array("rsvpDate" => date("Y-m-d"), 
  								"rsvpStatus" => $rsvpStatus,
                  "email" => $_POST['attending'.$a->id."Email"], 
  								"kidsMeal" => ((strToUpper((isset($_POST['attending'.$a->id.'KidsMeal']) ? $_POST['attending'.$a->id.'KidsMeal'] : "N")) == "Y") ? "Y" : "N"), 
  								"veggieMeal" => ((strToUpper((isset($_POST['attending'.$a->id.'VeggieMeal']) ? $_POST['attending'.$a->id.'VeggieMeal'] : "N")) == "Y") ? "Y" : "N")),
  								array("id" => $a->id), 
  								array("%s", "%s", "%s", "%s", "%s"), 
  								array("%d"));
        } else {
  				$wpdb->update(ATTENDEES_TABLE, array("rsvpDate" => date("Y-m-d"), 
  								"rsvpStatus" => $rsvpStatus,
  								"kidsMeal" => ((strToUpper((isset($_POST['attending'.$a->id.'KidsMeal']) ? $_POST['attending'.$a->id.'KidsMeal'] : "N")) == "Y") ? "Y" : "N"), 
  								"veggieMeal" => ((strToUpper((isset($_POST['attending'.$a->id.'VeggieMeal']) ? $_POST['attending'.$a->id.'VeggieMeal'] : "N")) == "Y") ? "Y" : "N")),
  								array("id" => $a->id), 
  								array("%s", "%s", "%s", "%s"), 
  								array("%d"));
        }
				
				rsvp_printQueryDebugInfo();
				rsvp_handleAdditionalQuestions($a->id, $a->id."question");
			}
		}
					
		if(get_option(OPTION_HIDE_ADD_ADDITIONAL) != "Y") {
			if(is_numeric($_POST['additionalRsvp']) && ($_POST['additionalRsvp'] > 0)) {
				for($i = 1; $i <= $_POST['additionalRsvp']; $i++) {
          $numGuests = 3;
          if(get_option(OPTION_RSVP_NUM_ADDITIONAL_GUESTS) != "") {
            $numGuests = get_optioN(OPTION_RSVP_NUM_ADDITIONAL_GUESTS);
            if(!is_numeric($numGuests) || ($numGuests < 0)) {
              $numGuests = 3;
            }
          }
					if(($i <= $numGuests) && 
					   !empty($_POST['newAttending'.$i.'FirstName']) && 
					   !empty($_POST['newAttending'.$i.'LastName'])) {		
            $thankYouAssociated[] = $_POST['newAttending'.$i.'FirstName'];
						$wpdb->insert(ATTENDEES_TABLE, array("firstName" => trim($_POST['newAttending'.$i.'FirstName']), 
										"lastName" => trim($_POST['newAttending'.$i.'LastName']), 
                    "email" => trim($_POST['newAttending'.$i.'Email']), 
										"rsvpDate" => date("Y-m-d"), 
										"rsvpStatus" => (($_POST['newAttending'.$i] == "Y") ? "Yes" : "No"), 
										"kidsMeal" => (isset($_POST['newAttending'.$i.'KidsMeal']) ? $_POST['newAttending'.$i.'KidsMeal'] : "N"), 
										"veggieMeal" => (isset($_POST['newAttending'.$i.'VeggieMeal']) ? $_POST['newAttending'.$i.'VeggieMeal'] : "N"), 
										"additionalAttendee" => "Y"), 
										array('%s', '%s', '%s', '%s', '%s', '%s', '%s'));
						rsvp_printQueryDebugInfo();
						$newAid = $wpdb->insert_id;
						rsvp_handleAdditionalQuestions($newAid, $i.'question');
						// Add associations for this new user
						$wpdb->insert(ASSOCIATED_ATTENDEES_TABLE, array("attendeeID" => $newAid, 
											"associatedAttendeeID" => $attendeeID), 
											array("%d", "%d"));
						rsvp_printQueryDebugInfo();
						$wpdb->query($wpdb->prepare("INSERT INTO ".ASSOCIATED_ATTENDEES_TABLE."(attendeeID, associatedAttendeeID)
																				 SELECT ".$newAid.", associatedAttendeeID 
																				 FROM ".ASSOCIATED_ATTENDEES_TABLE." 
																				 WHERE attendeeID = %d", $attendeeID));
						rsvp_printQueryDebugInfo();
					}
				}
			}
		}
		
    $email = get_option(OPTION_NOTIFY_EMAIL);
    
		if((get_option(OPTION_NOTIFY_ON_RSVP) == "Y") && ($email != "")) {
			$sql = "SELECT firstName, lastName, rsvpStatus, kidsMeal, veggieMeal, note FROM ".ATTENDEES_TABLE." WHERE id= ".$attendeeID;
			$attendee = $wpdb->get_results($sql);
			if(count($attendee) > 0) {
				$body = "Hello, \r\n\r\n";
							
				$body .= stripslashes($attendee[0]->firstName)." ".stripslashes($attendee[0]->lastName).
								 " has submitted their RSVP and has RSVP'd with '".$attendee[0]->rsvpStatus."'.";
				
      
        if(get_option(RSVP_OPTION_HIDE_NOTE) != "Y") {
          $body .= "Note: ".stripslashes($attendee[0]->note)."\r\n";
        }
      
  			$sql = "SELECT question, answer FROM ".QUESTIONS_TABLE." q 
  				LEFT JOIN ".ATTENDEE_ANSWERS." ans ON q.id = ans.questionID AND ans.attendeeID = %d 
  				ORDER BY q.sortOrder, q.id";
  			$aRs = $wpdb->get_results($wpdb->prepare($sql, $attendeeID));
  			if(count($aRs) > 0) {
          $body .= "\r\n\r\n--== Custom Questions ==--\r\n";
  				foreach($aRs as $a) {
            $body .= stripslashes($a->question).": ".stripslashes($a->answer)."\r\n";
  				}
  			}
        
  			$sql = "SELECT firstName, lastName, rsvpStatus FROM ".ATTENDEES_TABLE." 
  			 	WHERE id IN (SELECT attendeeID FROM ".ASSOCIATED_ATTENDEES_TABLE." WHERE associatedAttendeeID = %d) 
  					OR id in (SELECT associatedAttendeeID FROM ".ASSOCIATED_ATTENDEES_TABLE." WHERE attendeeID = %d)";
		
  			$associations = $wpdb->get_results($wpdb->prepare($sql, $attendeeID, $attendeeID));
        if(count($associations) > 0) {
          $body .= "\r\n\r\n--== Associated Attendees ==--\r\n";
    			foreach($associations as $a) {
            $body .= stripslashes($a->firstName." ".$a->lastName)." RSVP status: ".$a->rsvpStatus."\r\n";
    			}
        }
        $headers = "";
				if(get_option(OPTION_RSVP_DISABLE_CUSTOM_EMAIL_FROM) != "Y")
          $headers = 'From: '. $email . "\r\n";		
        
				wp_mail($email, "New RSVP Submission", $body, $headers);
			}
		}
    
    if((get_option(OPTION_RSVP_GUEST_EMAIL_CONFIRMATION) == "Y") && !empty($_POST['mainEmail'])) {
  		$sql = "SELECT firstName, lastName, email, rsvpStatus FROM ".ATTENDEES_TABLE." WHERE id= ".$attendeeID;
  		$attendee = $wpdb->get_results($sql);
  		if(count($attendee) > 0) {
  			$body = "Hello ".stripslashes($attendee[0]->firstName)." ".stripslashes($attendee[0]->lastName).", \r\n\r\n";
						
        if(get_option(OPTION_RSVP_EMAIL_TEXT) != "") {
			$body .= "\r\n";
			$body .= get_option(OPTION_RSVP_EMAIL_TEXT);
			$body .= "\r\n";
        }

		$body .= "You have successfully RSVP'd with '".$attendee[0]->rsvpStatus."'.";

		$sql = "SELECT firstName, lastName, rsvpStatus FROM ".ATTENDEES_TABLE." 
				WHERE id IN (SELECT attendeeID FROM ".ASSOCIATED_ATTENDEES_TABLE." WHERE associatedAttendeeID = %d) 
				OR id in (SELECT associatedAttendeeID FROM ".ASSOCIATED_ATTENDEES_TABLE." WHERE attendeeID = %d)";

		$associations = $wpdb->get_results($wpdb->prepare($sql, $attendeeID, $attendeeID));
		
        if(count($associations) > 0) {
			foreach($associations as $a) {
				$body .= "\r\n\r\n--== Associated Attendees ==--\r\n";
				$body .= stripslashes($a->firstName." ".$a->lastName)." rsvp status: ".$a->rsvpStatus."\r\n";
			}
        }
        $headers = "";
        if(!empty($email) && (get_option(OPTION_RSVP_DISABLE_CUSTOM_EMAIL_FROM) != "Y")) {
			$headers = 'From: '. $email . "\r\n";		
        }
        wp_mail($attendee[0]->email, "RSVP Confirmation", $body, $headers);
      }
    }
					
		return rsvp_handle_output($text, frontend_rsvp_thankyou($thankYouPrimary, $thankYouAssociated));
	} else {
		return rsvp_handle_output($text, rsvp_frontend_greeting());
	}
}

function rsvp_editFamily(&$output, &$text) {
	global $wpdb;
	
	if(is_numeric($_POST['familyID']) && ($_POST['familyID'] > 0)) {
		// Try to find the user.
		$attendee = $wpdb->get_row($wpdb->prepare("SELECT id, pin, date, ip, email, alias, comments 
													FROM ".FAMILIES_TABLE." 
													WHERE id = %d", $_POST['familyID']));
		if($attendee != null) {
			$output .= RSVP_START_CONTAINER;
			$output .= RSVP_START_PARA.__("Welcome back", 'rsvp-plugin')." ".htmlspecialchars($attendee->firstName." ".$attendee->lastName)."!".RSVP_END_PARA;
			$output .= rsvp_frontend_main_form($attendee->id);
			return rsvp_handle_output($text, $output.RSVP_END_CONTAINER);
		}
	}
}

function rsvp_foundAttendee(&$output, &$text) {
	global $wpdb;
	
	if(is_numeric($_POST['attendeeID']) && ($_POST['attendeeID'] > 0)) {
		$attendee = $wpdb->get_row($wpdb->prepare("SELECT id, firstName, lastName, rsvpStatus 
																							 FROM ".ATTENDEES_TABLE." 
																							 WHERE id = %d", $_POST['attendeeID']));
		if($attendee != null) {
			$output = RSVP_START_CONTAINER;
			if(strtolower($attendee->rsvpStatus) == "noresponse") {
				$output .= RSVP_START_PARA.__("Hi", 'rsvp-plugin')." ".htmlspecialchars(stripslashes($attendee->firstName." ".$attendee->lastName))."!".RSVP_END_PARA;
							
				if(trim(get_option(OPTION_WELCOME_TEXT)) != "") {
					$output .= RSVP_START_PARA.trim(get_option(OPTION_WELCOME_TEXT)).RSVP_END_PARA;
				} else {
					$output .= RSVP_START_PARA.__("There are a few more questions we need to ask you if you could please fill them out below to finish up the RSVP process.", 'rsvp-plugin').RSVP_END_PARA;
				}
												
				$output .= rsvp_frontend_main_form($attendee->id);
			} else {
				$output .= rsvp_frontend_prompt_to_edit($attendee);
			}
			return rsvp_handle_output($text, $output.RSVP_END_CONTAINER);
		} 
					
		return rsvp_handle_output($text, rsvp_frontend_greeting());
	} else {
		return rsvp_handle_output($text, rsvp_frontend_greeting());
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

function rsvp_frontend_new_atendee_thankyou($thankYouPrimary, $thankYouAssociated, $password = "") {
	/*$customTy = get_option(OPTION_THANKYOU);
	if(!empty($customTy)) {
		return nl2br($customTy);
	} else {*/
    $thankYouText = __("Thank you ", 'rsvp-plugin');
    if(!empty($thankYouPrimary)) {
      $thankYouText .= htmlspecialchars($thankYouPrimary);
    }
    $thankYouText .= __(" for RSVPing. To modify your RSVP just come back ".
                    "to this page and enter in your first and last name.", 'rsvp-plugin');
    if(!empty($password)) {
      $thankYouText .= __(" You will also need to know your password which is", 'rsvp-plugin').
                      " - <strong>$password</strong>";
    }
    
    if(count($thankYouAssociated) > 0) {
      $thankYouText .= __("<br /><br />You have also RSVPed for - ", 'rsvp-plugin');
      foreach($thankYouAssociated as $name) {
        $thankYouText .= htmlspecialchars(" ".$name).", ";
      }
      $thankYouText = rtrim(trim($thankYouText), ",").".";
    }

		return RSVP_START_CONTAINER.RSVP_START_PARA.$thankYouText.RSVP_END_PARA.RSVP_END_CONTAINER;
	//}
}

function rsvp_chomp_name($name, $maxLength) {
	for($i = $maxLength; $maxLength >= 1; $i--) {
		if(strlen($name) >= $i) {
			return substr($name, 0, $i);
		}
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

	if(get_option(OPTION_RSVP_OPEN_REGISTRATION) == "Y") {
		$output .= "<form name=\"rsvpNew\" method=\"post\" id=\"rsvpNew\" action=\"$rsvp_form_action\">\r\n";
		$output .= "	<input type=\"hidden\" name=\"rsvpStep\" value=\"newattendee\" />";
		$output .= "<input type=\"submit\" value=\"".__("New Attendee Registration", "rsvp-plugin")."\" />\r\n";
		$output .= "</form>\r\n";

		$output .= "<hr />";
		$output .= RSVP_START_PARA.__("Need to modify your registration? Start with the below form.", "rsvp-plugin").RSVP_END_PARA;
	}

	$output .= "<form name=\"rsvp\" method=\"post\" id=\"rsvp\" action=\"$rsvp_form_action\" autocomplete=\"off\">\r\n";
	$output .= "	<input type=\"hidden\" name=\"rsvpStep\" value=\"find\" />";

	$output .= RSVP_START_PARA."<label for=\"pin\">".__("PIN", 'rsvp-plugin').":</label> 
									<input type=\"password\" name=\"pin\" id=\"pin\" size=\"30\" value=\"".htmlspecialchars($pin)."\" class=\"required\" autocomplete=\"off\" />".RSVP_END_PARA;
	
	$output .= RSVP_START_PARA."<input type=\"submit\" value=\"".__("Complete your RSVP!", 'rsvp-plugin')."\" />".RSVP_END_PARA;
	$output .= "</form>\r\n";
	$output .= RSVP_END_CONTAINER;
	return $output;
}
?>
