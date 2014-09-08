	jQuery(document).ready(function($) {
		
		var kSize = $( "#Kuratur" ).parent().width();
             if (kSize < 900) {
		$('#Kuratur').attr("class", "kNarrow");
	} 
	else {
		$("#Kuratur").attr("class","");
	}
	
	});	