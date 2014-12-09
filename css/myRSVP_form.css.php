<?php
	function PluginUrl() {
        //Try to find manually... can't work if wp-content was renamed or is redirected
        $path = dirname(__FILE__);
        $path = str_replace("\\","/",$path);
        $path = substr($path, 0, strpos($path,"wp-content/"));
        return $path;
    }
	
	function adjustBrightness($hex, $steps) {
		// Steps should be between -255 and 255. Negative = darker, positive = lighter
		$steps = max(-255, min(255, $steps));

		// Format the hex color string
		$hex = str_replace('#', '', $hex);
		if (strlen($hex) == 3) {
			$hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
		}

		// Get decimal values
		$r = hexdec(substr($hex,0,2));
		$g = hexdec(substr($hex,2,2));
		$b = hexdec(substr($hex,4,2));

		// Adjust number of steps and keep it inside 0 to 255
		$r = max(0,min(255,$r + $steps));
		$g = max(0,min(255,$g + $steps));  
		$b = max(0,min(255,$b + $steps));

		$r_hex = str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
		$g_hex = str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
		$b_hex = str_pad(dechex($b), 2, '0', STR_PAD_LEFT);

		return '#'.$r_hex.$g_hex.$b_hex;
	}
	
	define('WP_USE_THEMES', false);
	require(PluginUrl().'wp-load.php');

    header("Content-type: text/css; charset: UTF-8");
	$mainColor = get_option(OPTION_RSVP_MAIN_COLOUR);


?>
@charset "utf-8";
/* CSS Document */

.frm-title {
	margin: 0.5em 0;
	padding:0;
	color: <?php echo $mainColor; ?>;
	background:transparent;
	font-size:1.4em;
	font-weight:bold;
}

p.frm-desc {
	margin: 0 0.5em;
		
}

form.rsvp {
	margin:0;
	padding:0;
	width: 450px;
}
fieldset.rsvp {
	margin:0.25em 0;
	border:none;
	padding: 0.75em;
	border-top:1px solid <?php echo $mainColor; ?>;
}
legend.rsvp {
	margin:0.25em 0;
	padding:0 .5em;
	color:<?php echo $mainColor; ?>;
	background:transparent;
	font-size:1.3em;
	font-weight:bold;
	text-align: center;
}
label.rsvp {
	float:left;
	width:120px;
	padding:0 1em;
	text-align:right;
	/*margin: 0.1em 0;*/
}

fieldset input.rsvp, fieldset textarea.rsvp {
	width:250px;
	border-top:1px solid <?php echo adjustBrightness($mainColor, 20); ?>;
	border-left:1px solid <?php echo adjustBrightness($mainColor, 20); ?>;
	border-bottom:1px solid <?php echo adjustBrightness($mainColor, -20); ?>;
	border-right:1px solid <?php echo adjustBrightness($mainColor, -20); ?>;
	padding:1px;
	color:#333;
	margin: 0.1em 0;
}
fieldset select.rsvp {
	border-top:1px solid <?php echo adjustBrightness($mainColor, 20); ?>;
	border-left:1px solid <?php echo adjustBrightness($mainColor, 20); ?>;
	border-bottom:1px solid <?php echo adjustBrightness($mainColor, -20); ?>;
	border-right:1px solid <?php echo adjustBrightness($mainColor, -20); ?>;
	width: 160px;
	color:#333;
	padding:1px;
	margin: 0.1em 0;
}

textarea.fullwidth, label.fullwidth {
	width:inherit !important;
	text-align:left;
	padding:0 0.2em;
}

.fm-submit {
	clear:both;
	padding-top:0.5em;
	text-align:center;
	font-weight: bold;
}
.fm-submit input {
	border:1px solid #333;
	padding:2px 1em;
	background:<?php echo $mainColor; ?>;
	color:#fff;
	font-size:100%;
}
input.rsvp:focus, textarea.rsvp:focus, select.rsvp:focus {
	background:#FAFAFA;
	color:#000;
}
input.rsvp:hover, textarea.rsvp:hover, select.rsvp:hover {
	background:#FAFAFA;
	color:#000;
}

.error-attending, .error-food {
	display: none;
	color:red;
}
