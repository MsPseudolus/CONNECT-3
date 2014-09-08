<?php

//collect the content to display from our .txt file and store it in a variable
function kur_short_getid() {
	$kur_short_pgid = get_the_id();
	return $kur_short_pgid;
}

//invoke the content from the function above and return it
function kuratur_short() {
	kur_short_getid();
	return $kur_short_pgid;
}

//create our shortcode which will display the content from our .txt file
add_shortcode ('kuratur', 'kuratur_short');