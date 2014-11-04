var row_seats_submitted = false;
//alert('hiiiiiiiiiiiiiiiii');
function row_seats_presubmit(showid) {
//alert(showid);
//alert(jQuery.cookie("rst_cart_2"));
//return false;

row_seats_submitted = true;
jQuery("#rssubmit").attr("disabled","disabled");
jQuery("#rsloading").css("display", "inline-block");
jQuery("#rsmessage5").slideUp("slow");
document.getElementById('mycartitems').value=jQuery.cookie('rst_cart_'+showid);

}





function row_seats_load() {

//alert('load');


	if (row_seats_submitted) {


		row_seats_submitted = false;


		var data = jQuery("#rsiframe").contents().find("body").html();

     
		jQuery("#rssubmit").removeAttr("disabled");


		jQuery("#rsloading").fadeOut(200);


		if (data.match("row_seats_confirmation_info") != null) {

			//alert('row_seats_confirmation_info');
			jQuery("#rsmessage5").slideUp("slow");

			jQuery("#rssignup_form").fadeOut(500, function() {

				jQuery("#rsconfirmation_container2").html(data);


				jQuery("#rsconfirmation_container2").fadeIn(500, function() {});


			});


		} else if (data.match("row_seats_error_message") != null) {

		//alert('row_seats_error_message');
			jQuery("#rsmessage5").html(data);


			jQuery("#rsmessage5").slideDown("slow");


		}


	}


}

function row_seats_edit() {


	jQuery("#rsconfirmation_container2").fadeOut(500, function() {


		jQuery("#rssignup_form").fadeIn(500, function() {});


	});


}