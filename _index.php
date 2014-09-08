<?php
/*
  Plugin Name: Kuratur Connect
  Description: The painless way to always have fresh relevant content on your blog. Curate a blog page of automated content using your favorite social media sources.
  Version: 1.1
  Author: Kirsten Lambertsen and Minh Nguyen
 *
  Author URI: http://kuratur.com
 */
 /*	Copyright 2014 Kuratur, Inc.

	This file is part of Kuratur Connect.

    Kuratur Connect is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Kuratur Connect is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Kuratur Connect.  If not, see http://www.gnu.org/licenses/.
*/
/*
  add_filter('the_content', 'before_show');

  function before_show($content){

  } */
global $kuratur_db_version;
$kuratur_db_version = '1.0';
global $kuraturUrl;
$kuraturUrl		=	esc_url_raw ( 'http://www.kuratur.com' );
add_action('admin_menu', 'kuratur_create_menu');
//add_action('admin_menu','add_new_post');

//this is where add_content_kuratur gets called
function kuratur_create_menu() {
	//create custom top-level menu
	add_menu_page('Kuratur Importer', 'Kuratur', 'manage_options', __FILE__, 'add_content_kuratur', plugins_url('/images/favicon.ico', __FILE__), 500);
	add_submenu_page(__FILE__, 'Create content from Kuratur', 'Create Content', 'manage_options', __FILE__, 'add_content_kuratur');
//	add_submenu_page(__FILE__, 'Manage Kuratur Digests', 'Manage Digests', 'manage_options', __FILE__ . '_manage_digests', 'manage_digests');
}
/* some day we'll add full magazine creation/management here
if (!function_exists('manage_digests')) {

	function manage_digests() {
		echo 'sdfsdfsd';
		die;
	}

}
*/
//MAKE THIS A CLASS?
if ( !function_exists( 'add_content_kuratur' ) ) {
	function add_content_kuratur() {
		$imgPath = plugins_url('/images/logo.png', __FILE__);
		$imgnextPath = plugins_url('/images/next_button.png', __FILE__);
		$createPath = admin_url('/admin.php?page='.basename(dirname(__FILE__)).'/index.php', __FILE__);
		wp_enqueue_script('action_Script', plugin_dir_url(__FILE__) . 'js/action.js', false, false, true);
		$contentOb	=	$content = '';
		$keyAPI		=	'';
		
		//handler for post submission -- checks contents of submission
		if ( !empty( $_POST['key_api'] ) ) {
			$keyAPI	= sanitize_text_field( trim( $_POST['key_api'] ) );
			$contents = getWebcontent( '/contents/content/'.$keyAPI );
			if( !empty( $contents['content'] ) ){
				$contentOb = json_decode( $contents['content'] );
				if( !empty( $contentOb->update_frequency ) ){
					$content = $contentOb->content;
					$_POST['kuratur_update_frequency'] = intval( $contentOb->update_frequency );
				}
			}
			$contents = null;
		}//end post submission handler
		if( empty( $content ) || empty( $_POST['key_api'] ) ) {
			$imgPath = plugins_url('/images/logo.png', __FILE__);
			echo <<<HEADER
			<div class="kuratur_introduction" style="padding:30px 20px;text-align:center;"><br>
				<img src="$imgPath" alt=""/><br /><br /><br /><br /><br /><br />
				<p style="text-align:center;width:100%;font-size: 18px;">
					Enter the API key from your Kuratur magazine to import it automatically as a page in your WordPress site.<br />
					If you don't have a key, please go to Kuratur.com to create an account and a magazine to import. It's free :-)<br />
				</p><br /><br /><br /><br />
				<form action="$createPath" method="post" id="import_content_from_kuratur" style="padding:20px 200px;text-align:left;">
					<p style="font-size: 16px;">
						<label for="key_api">Key of digest(from Kuratur) : </label> <input type="text" id="key_api" name="key_api" style="width:500px" value="$keyAPI" />
					</p>
					<p style="font-size: 16px;">
						<!--label for="create_post">Import as Post&nbsp;&nbsp;</label><input type="radio" id="create_post" name="create_post" /-->
						<label for="create_page">Import as Page</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" id="create_page" name="create_post" value="3"/>
					</p><br /><br /><br /><br />
					<p>	<input type="submit" value="Import Content"/></p>
				</form>
			</div>
HEADER;
		} else {		
		//check to see if the api key already exists in one of our custom posts		
		$kurcheck_args = array(
			'post_type' =>	'kur_mag_import',
			'post_status' =>	'private',
			'meta_query' => array(
						array(
						'key'     => '_kur_apikey',
						'value'   => $keyAPI,
						'compare' => '===')
						)
			);
		
		$kur_matches = get_posts( $kurcheck_args );
		if ( $kur_matches ) { 
			foreach ( $kur_matches as $kur_match ) :
			$kur_update_id = get_post_field( 'ID', $kur_match->ID );
			endforeach;
			} 
		
		else { $kur_update_id = 'false';}
/*
*****  function that adds content to custom post type post
*/		
		//if the api_key doesn't already exist, create a new custom post
		if ($kur_update_id === 'false') {
			$count_kur_mags = wp_count_posts('post');
			$kur_mag_count = $count_kur_mags->publish;
			$kur_post_title = "Kuratur magazine #" . $kur_mag_count;
			$kur_post = array(
			  'post_content'   => wp_kses_post( $content ), // The full text of the post.
			  'post_title'     => $kur_post_title, // The title of your post.
			  'post_status'    => 'private', // Default 'draft'.
			  'post_type'      => 'kur_mag_import', // Default 'post'.
			  'comment_status' => 'closed' // Default is the option 'default_comment_status', or 'closed'.
			);  		
			//insert the post and the post meta
			$kur_post_id = wp_insert_post( $kur_post );
			update_post_meta( $kur_post_id, sanitize_text_field( '_kur_apikey' ), ($_POST['key_api']) );
			update_post_meta( $kur_post_id, intval( '_kur_freq' ), ( $_POST['kuratur_update_frequency'] ) );
			$kur_redirect = get_edit_post_link( $kur_post_id );
				$kur_shortcode_scr = plugins_url('/images/kur-shortcode-screen.png', __FILE__);
				echo '
				<div class="kuratur_introduction" style="width: 80%; text-align:center; margin-left: 7%; margin-top: 6%;">
					<div style="margin-top: 25px;">
						<img src="' .$imgPath .' " alt=""/><br /><br />
					</div>
					<div style="margin-top: 40px;">
						<div style="width: 40%; float: left; display: inline-block; text-align:left;font-size: 18px; line-height: 1.5;">
							<b>Time to get your shortcode!</b><br /><br />Your Kuratur magazine has been imported. <a href="' . $kur_redirect . '">click here</a> to get the shortcode for this magazine. You will be taken to a standard-looking page editor, and you will find the shortcode just below the content.<br /><br />Paste the shortcode anywhere you want to display that magazine content on your site. The content will automically update anywhere that you include the shortcode.<br /><br />Retrieve the shortcode for any Kuratur magazine that you have imported by clicking on Kuratur Magazines in the left nav.
						</div>
						<div style="width: 50%; display: inline-block;">
							<img src="' . $kur_shortcode_scr . '" style="width: 100%;">
						</div>
					</div>
				</div>';
			}		
			else
			{
	//if api_key exists, update the matching post with the content
			$kur_update = array(
				  'ID'	=> $kur_update_id,
				  'post_content' => wp_kses_post( $content)
			);		
			// Update the post and post meta
				wp_update_post( $kur_update );
				update_post_meta( $kur_update_id, sanitize_text_field( '_kur_apikey' ), $keyAPI );
				update_post_meta( $kur_update_id, intval( '_kur_freq' ), ( $_POST['kuratur_update_frequency'] ) );
				$kur_shortcode_scr = plugins_url('/images/kur-shortcode-screen.png', __FILE__);
				$kur_redirect = get_edit_post_link( $kur_update_id );
				echo '<div class="kuratur_introduction" style="width: 80%; text-align:center; margin-left: 7%; margin-top: 6%;">
					<div style="margin-top: 25px;">
						<img src="' .$imgPath .' " alt=""/><br /><br />
					</div>
					<div style="margin-top: 40px;">
						<div style="width: 40%; float: left; display: inline-block; text-align:left;font-size: 18px; line-height: 1.5;">
							<b>Time to get your shortcode!</b><br /><br />Your Kuratur magazine has been updated. The content will also be updated anywhere that you have included its shortcode. <a href="' . $kur_redirect . '">click here</a> to get the shortcode for this magazine. You will be taken to a standard-looking page editor, and you will find the shortcode just below the content.<br /><br />Paste the shortcode anywhere you want to display that magazine content on your site. The content will automically update anywhere that you include the shortcode.<br /><br />Retrieve the shortcode for any Kuratur magazine that you have imported by clicking on Kuratur Magazines in the left nav.
						</div>
						<div style="width: 50%; display: inline-block;">
							<img src="' . $kur_shortcode_scr . '" style="width: 100%;">
						</div>
					</div>
				</div>';
			}
			wp_reset_postdata();		
			if ( ! current_user_can( 'edit_posts' ) || ! current_user_can( 'create_posts' ) )
				wp_die( __( '' ) );

			// Schedule auto-draft cleanup
			if ( ! wp_next_scheduled( 'wp_scheduled_auto_draft_delete' ) )
				wp_schedule_event( time(), 'daily', 'wp_scheduled_auto_draft_delete' );
			wp_enqueue_script( 'autosave' );
			// Show post form.
			include(dirname(__FILE__).'/first_part_edit.php');
		}
		return true;
	}

}
function getWebcontent($path) {
	global $kuraturUrl;
	$path	=	$kuraturUrl.$path;
	$options = array(
		CURLOPT_RETURNTRANSFER => true, // return web page
		CURLOPT_HEADER => false, // don't return headers
		CURLOPT_FOLLOWLOCATION => false, // follow redirects
		CURLOPT_ENCODING => '', // handle compressed
		CURLOPT_USERAGENT => 'include_request', // who am i
		CURLOPT_AUTOREFERER => true, // set referer on redirect
		CURLOPT_CONNECTTIMEOUT => 1200, // timeout on connect
		CURLOPT_TIMEOUT => 1200, // timeout on response
		CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
//		CURLOPT_COOKIE => $strCookie,
//			CURLOPT_POST			=>	true,
//			CURLOPT_POSTFIELDS		=>	$query,
		CURLOPT_URL => $path, 
	);

	session_write_close();

	$ch = curl_init();
	curl_setopt_array($ch, $options);
	$content = curl_exec($ch);
	$err = curl_errno($ch);
	$errmsg = curl_error($ch);
	$header = curl_getinfo($ch);
	curl_close($ch);
	$header['errno'] = $err;
	$header['errmsg'] = $errmsg;
	$header['content'] = $content;
	return $header;
}

/*****custom hook for our cron function************************************/
add_action( 'kurcronhook', 'kurautoupdate');
if( !wp_next_scheduled( 'kurcronhook' ) ) {
	wp_schedule_event( time(), 'hourly', 'kurcronhook');
	}
//put the wp_schedule_event in a function that is called upon register plugin?

/*****new method to update existing pgs hourly using wp_cron******/
function kurautoupdate() {
	$kurcron_args = array( 			
					'post_type' =>	'kur_mag_import', 			
					'post_status' =>	'private' ); 		 		
	$kur_crons = get_posts( $kurcron_args ); 		
	if ( $kur_crons ) {  			
		foreach ( $kur_crons as $kur_cron ) : 					
			$kur_update_id = get_post_field( 'ID', $kur_cron->ID ); 			
			$kurcronAPI = get_post_meta( $kur_cron->ID, '_kur_apikey', true );
			$contents	=	getWebcontent( '/contents/content/'. $kurcronAPI );							
			if(!empty($contents['content'])){
				$contentOb	=	json_decode($contents['content']);
				$content	=	$contentOb->content;
				$kur_update = array(
				  'ID'	=> $kur_update_id,
				  'post_content' => $content);		
				// Update the post content
				wp_update_post( $kur_update );
			}		
		endforeach;
		} 
	
	else return;
}	   

/*******************old code which checks the db***********************
	empty($post->kuratur_update_frequency) ? 12 : $post->kuratur_update_frequency;
	if(!empty($post->from_kuratur) && !empty($kuratur_update_frequency) && $post->from_kuratur_date	+ $kuratur_update_frequency*60*60 <= time()){
		$contents	=	getWebcontent('/contents/content/'.$post->from_kuratur);
		if(!empty($contents['content'])){
			$contentOb	=	json_decode($contents['content']);
			if(!empty($contentOb->update_frequency)){
				global $wpdb;
				$content	=	$contentOb->content;//$_POST['kuratur_update_frequency']	=		intval($contentOb->update_frequency);
				$wpdb->query($wpdb->prepare("UPDATE $wpdb->posts SET post_content=%s, from_kuratur_date=%d, kuratur_update_frequency=%d WHERE ID=%d",$contentOb->content,time(),intval($contentOb->update_frequency), $post->ID));
			}
		}
	}
	return $content;
}
*/


register_activation_hook(__FILE__, 'kuratur_install');

function kuratur_install() {
}

	//add our Kuratur custom post type & metaboxes
	add_action( 'init', 'register_kur_mag', 5 );
	//create the Kuratur custom post type to store magazine content and data
	function register_kur_mag() {
		$labels = array( 
			'name' => _x( 'Kuratur Magazines', 'kur_mag_imports' ),
			'singular_name' => _x( 'Kuratur Magazine', 'kur_mag_import' ),
			'not_found' => _x( 'No Kuratur magazines found', 'kur_mag_import' ),
			'not_found_in_trash' => _x( 'No Kuratur magazines found in Trash', 'kur_mag_import' )
		);
	
		$args = array( 
			'labels' => $labels,
			'hierarchical' => true,
			'description' => 'Kuratur Magazine',
			'supports' => array( 'title', 'editor', 'custom-fields' ),
			
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 20,
			
			'show_in_nav_menus' => true,
			'publicly_queryable' =>  false,
			'exclude_from_search' => true,
			'has_archive' => false,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'capabilities' => array(
				'create_posts' => true, // Removes support for the "Add New" function
				 ),
			'map_meta_cap' => true // Set to false if users are not allowed to edit/delete existing posts	 
		);
		
		register_post_type( 'kur_mag_import', $args );
	}



//form the shortcode to display in the cpt shortcode metabox
function show_kuratur_short() {
	$kur_short_pid = get_the_id();
	echo '[kuratur kur_page_id="' . $kur_short_pid .'" ]';
}
//get the update frequency to display in the cpt frequency metabox
function show_kuratur_freq() {
	echo 'Every ' . get_post_field('_kur_freq', ( get_the_id() ) ) . ' hour(s)';
}
// add meta boxes to our cpt to show the shortcode, update freq, and key
add_action( 'add_meta_boxes', 'kur_meta_boxes');

function kur_meta_boxes() {
	add_meta_box( 'kur_short_metab', 'Shortcode for this magazine:', 'show_kuratur_short', 'kur_mag_import', 'normal',
			 'high' );
	
	add_meta_box( 'kur_freq_metab', 'Update frequency', 'show_kuratur_freq', 'kur_mag_import', 'normal',
			 'high' );
}

//include the Kuratur style sheet in the header
if( !function_exists( 'kuratur_styles') ) :
	function kuratur_styles() {
		$kurfeedstyles = plugins_url( '/css/kuratur-feed-styles.css', __FILE__ );
		?>
		<link rel="stylesheet" id="kurstyles" href="<?php echo $kurfeedstyles;?>" type="text/css" media="all">
		<?php
	}
endif;
add_action( 'wp_head', 'kuratur_styles' );

//uninstall actions, delete the old db fields
register_deactivation_hook( __FILE__, 'kuratur_uninstall');

function kuratur_uninstall(){
	if( get_option ( 'kuratur_db_version' ) )
		kur_clean_db();
	
	function kur_clean_db() {
		global $wpdb;
		delete_option( 'kuratur_db_version' );
		@$wpdb->query("ALTER table $wpdb->posts	DROP COLUMN `from_kuratur`");
		@$wpdb->query("ALTER table $wpdb->posts	DROP COLUMN `from_kuratur_date` ");
		@$wpdb->query("ALTER table $wpdb->posts	DROP COLUMN `kuratur_update_frequency` ");
	}
}

//invoke the content from the function above and return it
function kuratur_short( $atts ) {
	$kur_code_atts = shortcode_atts( array(
		'kur_page_id' => '', //get the pid from the shortcode
	), $atts );
	// output the content from the cptpage
	return get_post_field( 'post_content', ($kur_code_atts['kur_page_id']), 'raw' ); 
}
//add our shortcode which will display the content from our .txt file
add_shortcode ('kuratur', 'kuratur_short');
