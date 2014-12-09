jQuery(document).ready(function(){
						
	/* First step, where they search for a name */
	jQuery("#rsvp").validate({
		rules: {
			pin: "required"
		}, 
		messages: {
			pin: "<br />Please enter your PIN"
		}
	});

});