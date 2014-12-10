<?php
	$installed_ver = get_option("rsvp_db_version");
	
	//FAMILIES TABLE
	$table = FAMILIES_TABLE;
	if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
		$sql = "CREATE TABLE IF NOT EXISTS `".$table."` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `pin` varchar(10) NOT NULL,
			  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  `ip` varchar(45) DEFAULT NULL,
			  `email` varchar(100) DEFAULT NULL,
			  `alias` varchar(100) NOT NULL,
			  `comments` varchar(500) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `pin` (`pin`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$wpdb->query($sql);
	}
	
	// ATTENDEES Table
	$table = ATTENDEES_TABLE;
	if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
		$sql = "CREATE TABLE IF NOT EXISTS `".$table."` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`family` int(11) NOT NULL,
				`name` varchar(100) NOT NULL,
				`attending` varchar(10) DEFAULT NULL,
				`food` varchar(20) DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `family` (`family`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$wpdb->query($sql);
	}
	
	//Attempts Table
	$table = ATTEMPTS_TABLE;
	if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
		$sql = "CREATE TABLE IF NOT EXISTS `".$table."` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `ip` varchar(45) NOT NULL,
			  `starttime` datetime NOT NULL,
			  `endtime` datetime NOT NULL,
			  `attempts` int(11) NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `ip` (`ip`)
		)ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$wpdb->query($sql);
	}			

	//Forms Table
	$table = FORMS_TABLE;
	if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
		$sql = "CREATE TABLE IF NOT EXISTS `".$table."` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(60) NOT NULL,
			  `starttime` datetime NOT NULL,
			  `endtime` datetime NOT NULL,
			  `settings` TEXT NOT NULL,
			  `family_form` TEXT NOT NULL,
			  `attendee_form` TEXT NOT NULL,
			  PRIMARY KEY (`id`)
		)ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$wpdb->query($sql);
	}	
	
	add_option("rsvp_db_version", "0");
	update_option( "rsvp_db_version", RSVP_DB_VERSION);
?>