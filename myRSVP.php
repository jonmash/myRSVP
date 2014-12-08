<?php
/*
	Plugin Name: myRSVP
	Plugin URI: http://jonmash.ca
	Description: A handy RSVP plug-in for WordPress. Useful for Weddings and other "closed" events.
	Version: 0.1
	Author: Jonathan Mash
	Author URI: http://jonmash.ca
	License: GPL2
	License URI: https://www.gnu.org/licenses/gpl-2.0.html
	Domain Path: /languages
	Text Domain: myRSVP
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
	define("OPTION_DEBUG_RSVP_QUERIES", "rsvp_debug_queries");
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
  
	require_once("includes/rsvp_frontend.inc.php");
	
	if ( is_admin ) {
		// We are in admin mode
		require_once( dirname(__file__).'/admin/rsvp_admin.inc.php' );
	}
	
	/*
	 * Description: Database setup for the rsvp plug-in.  
	 */
	function rsvp_database_setup() {
		global $wpdb;
		//require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		require_once("admin/rsvp_db_setup.inc.php");
	}
	
 	function rsvp_uninstall() {
		global $wpdb;
		require_once("admin/rsvp_uninstall.inc.php");
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

	
	
	function rsvp_modify_menu() {
		$page = add_menu_page("myRSVP Plugin", 
									"myRSVP Plugin", 
									"publish_posts", 
									"rsvp-top-level", 
									"rsvp_admin_options",
									'dashicons-book-alt');
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
		wp_register_script('rsvp_plugin', plugins_url("js/rsvp_plugin.js", RSVP_PLUGIN_FILE));
		wp_register_style('rsvp_css', plugins_url("css/rsvp_plugin.css", RSVP_PLUGIN_FILE));
		wp_register_style('rsvp_form_css', plugins_url("css/rsvp_form.css", RSVP_PLUGIN_FILE));
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery_validate');
		wp_enqueue_script('rsvp_plugin');
		wp_enqueue_style("rsvp_css");
		wp_enqueue_style("rsvp_form_css");

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
	register_uninstall_hook(__FILE__,'rsvp_uninstall');
?>
