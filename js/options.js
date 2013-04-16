jQuery(document).ready(function() {
	
	if (jQuery('#gallery_background_colourpicker').length > 0) {
		jQuery('#gallery_background_colourpicker').hide();
	    jQuery('#gallery_background_colourpicker').farbtastic("#gallery_background_colour");
	    jQuery("#gallery_background_colour").click(function(){jQuery('#gallery_background_colourpicker').slideToggle()});	
	}
	
	if (jQuery('#gallery_border_colourpicker').length > 0) {
		jQuery('#gallery_border_colourpicker').hide();
	    jQuery('#gallery_border_colourpicker').farbtastic("#gallery_border_colour");
	    jQuery("#gallery_border_colour").click(function(){jQuery('#gallery_border_colourpicker').slideToggle()});	
	}
	
	if (jQuery('#item_background_colourpicker').length > 0) {
		jQuery('#item_background_colourpicker').hide();
	    jQuery('#item_background_colourpicker').farbtastic("#item_background_colour");
	    jQuery("#item_background_colour").click(function(){jQuery('#item_background_colourpicker').slideToggle()});	
	}
	
	if (jQuery('#item_border_colourpicker').length > 0) {
		jQuery('#item_border_colourpicker').hide();
	    jQuery('#item_border_colourpicker').farbtastic("#item_border_colour");
	    jQuery("#item_border_colour").click(function(){jQuery('#item_border_colourpicker').slideToggle()});	
	}
	
	if (jQuery('#image_background_colourpicker').length > 0) {
		jQuery('#image_background_colourpicker').hide();
	    jQuery('#image_background_colourpicker').farbtastic("#image_background_colour");
	    jQuery("#image_background_colour").click(function(){jQuery('#image_background_colourpicker').slideToggle()});	
	}
	
	if (jQuery('#image_border_colourpicker').length > 0) {
		jQuery('#image_border_colourpicker').hide();
	    jQuery('#image_border_colourpicker').farbtastic("#image_border_colour");
	    jQuery("#image_border_colour").click(function(){jQuery('#image_border_colourpicker').slideToggle()});	
	}
	
	if (jQuery('#selected_image_border_colourpicker').length > 0) {	
		jQuery('#selected_image_border_colourpicker').hide();
	    jQuery('#selected_image_border_colourpicker').farbtastic("#selected_image_border_colour");
	    jQuery("#selected_image_border_colour").click(function(){jQuery('#selected_image_border_colourpicker').slideToggle()});	
	}
	
});
