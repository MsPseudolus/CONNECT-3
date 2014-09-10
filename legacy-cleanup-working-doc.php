<?php

//check for the option originally created when kuratur db columns were added
if( get_option ( 'kuratur_db_version' ) ) { 

$kur_legs = $wpdb->get_results( "SELECT ID, from_kuratur FROM wp_posts WHERE from_kuratur != '' ", ARRAY_A) ; return $kur_legs;

	foreach ( $kur_legs as $kur_leg ) {
	
		//ask the user permission to do this, only proceed with permission
	
		//create a Kuratur cpt using the api key 
		
		//get the shortcode from the cpt
		
		//replace the content of the page with the shortcode and save
		
	
	}

}


?>