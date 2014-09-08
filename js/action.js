/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if( jQuery('#import_content_from_kuratur').length){
	jQuery('#import_content_from_kuratur').submit(function(){
		if( !jQuery.trim(jQuery('#key_api').val())){
			return false;
		}
		return true;
	});	
}

