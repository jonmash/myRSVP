<?php
    header("Content-type: text/css; charset: UTF-8");

   $mainColor = "#004408";

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
	border-top:1px solid <?php echo $mainColor; ?>;
	border-left:1px solid <?php echo $mainColor; ?>;
	border-bottom:1px solid <?php echo $mainColor; ?>;
	border-right:1px solid <?php echo $mainColor; ?>;
	padding:1px;
	color:#333;
	margin: 0.1em 0;
}
fieldset select.rsvp {
	border-top:1px solid <?php echo $mainColor; ?>;
	border-left:1px solid <?php echo $mainColor; ?>;
	border-bottom:1px solid <?php echo $mainColor; ?>;
	border-right:1px solid <?php echo $mainColor; ?>;
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
	background:#DDDDDD;
	color:#000;
}
input.rsvp:hover, textarea.rsvp:hover, select.rsvp:hover {
	background:#DDDDDD;
	color:#000;
}

.error-attending, .error-food {
	display: none;
	color:red;
}
