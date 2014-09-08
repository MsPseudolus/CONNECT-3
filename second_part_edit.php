<?php
//$post_type	=	'page';
if ('attachment' == $post_type) {
	wp_enqueue_script('image-edit');
	wp_enqueue_style('imgareaselect');
	add_meta_box('submitdiv', __('Save'), 'attachment_submit_meta_box', null, 'side', 'core');
	add_action('edit_form_after_title', 'edit_form_image_editor');
} else {
	add_meta_box('submitdiv', __('Publish'), 'post_submit_meta_box', null, 'side', 'core');
}

if (current_theme_supports('post-formats') && post_type_supports($post_type, 'post-formats'))
	add_meta_box('formatdiv', _x('Format', 'post format'), 'post_format_meta_box', null, 'side', 'core');

// all taxonomies
foreach (get_object_taxonomies($post) as $tax_name) {
	$taxonomy = get_taxonomy($tax_name);
	if (!$taxonomy->show_ui)
		continue;

	$label = $taxonomy->labels->name;

	if (!is_taxonomy_hierarchical($tax_name))
		add_meta_box('tagsdiv-' . $tax_name, $label, 'post_tags_meta_box', null, 'side', 'core', array('taxonomy' => $tax_name));
	else
		add_meta_box($tax_name . 'div', $label, 'post_categories_meta_box', null, 'side', 'core', array('taxonomy' => $tax_name));
}

if (post_type_supports($post_type, 'page-attributes'))
	add_meta_box('pageparentdiv', 'page' == $post_type ? __('Page Attributes') : __('Attributes'), 'page_attributes_meta_box', null, 'side', 'core');

if (current_theme_supports('post-thumbnails', $post_type) && post_type_supports($post_type, 'thumbnail'))
	add_meta_box('postimagediv', __('Featured Image'), 'post_thumbnail_meta_box', null, 'side', 'low');

if (post_type_supports($post_type, 'excerpt'))
	add_meta_box('postexcerpt', __('Excerpt'), 'post_excerpt_meta_box', null, 'normal', 'core');

if (post_type_supports($post_type, 'trackbacks'))
	add_meta_box('trackbacksdiv', __('Send Trackbacks'), 'post_trackback_meta_box', null, 'normal', 'core');

//if (post_type_supports($post_type, 'custom-fields'))
//	add_meta_box('postcustom', __('Custom Fields'), 'post_custom_meta_box', null, 'normal', 'core');

do_action('dbx_post_advanced');
//if (post_type_supports($post_type, 'comments'))
//	add_meta_box('commentstatusdiv', __('Discussion'), 'post_comment_status_meta_box', null, 'normal', 'core');

if (( 'publish' == get_post_status($post) || 'private' == get_post_status($post) ) && post_type_supports($post_type, 'comments'))
	add_meta_box('commentsdiv', __('Comments'), 'post_comment_meta_box', null, 'normal', 'core');
//
//if (!( 'pending' == get_post_status($post) && !current_user_can($post_type_object->cap->publish_posts) ))
//	add_meta_box('slugdiv', __('Slug'), 'post_slug_meta_box', null, 'normal', 'core');

//if (post_type_supports($post_type, 'author')) {
//	if (is_super_admin() || current_user_can($post_type_object->cap->edit_others_posts))
//		add_meta_box('authordiv', __('Author'), 'post_author_meta_box', null, 'normal', 'core');
//}

if (post_type_supports($post_type, 'revisions') && 0 < $post_ID && wp_get_post_revisions($post_ID))
	add_meta_box('revisionsdiv', __('Revisions'), 'post_revisions_meta_box', null, 'normal', 'core');

do_action('add_meta_boxes', $post_type, $post);
do_action('add_meta_boxes_' . $post_type, $post);

do_action('do_meta_boxes', $post_type, 'normal', $post);
do_action('do_meta_boxes', $post_type, 'advanced', $post);
do_action('do_meta_boxes', $post_type, 'side', $post);

add_screen_option('layout_columns', array('max' => 2, 'default' => 2));

if ('post' == $post_type) {
	$customize_display = '<p>' . __('The title field and the big Post Editing Area are fixed in place, but you can reposition all the other boxes using drag and drop. You can also minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to unhide more boxes (Excerpt, Send Trackbacks, Custom Fields, Discussion, Slug, Author) or to choose a 1- or 2-column layout for this screen.') . '</p>';

	get_current_screen()->add_help_tab(array(
		'id' => 'customize-display',
		'title' => __('Customizing This Display'),
		'content' => $customize_display,
	));

	$title_and_editor = '<p>' . __('<strong>Title</strong> - Enter a title for your post. After you enter a title, you&#8217;ll see the permalink below, which you can edit.') . '</p>';
	$title_and_editor .= '<p>' . __('<strong>Post editor</strong> - Enter the text for your post. There are two modes of editing: Visual and Text. Choose the mode by clicking on the appropriate tab. Visual mode gives you a WYSIWYG editor. Click the last icon in the row to get a second row of controls. The Text mode allows you to enter HTML along with your post text. Line breaks will be converted to paragraphs automatically. You can insert media files by clicking the icons above the post editor and following the directions. You can go to the distraction-free writing screen via the Fullscreen icon in Visual mode (second to last in the top row) or the Fullscreen button in Text mode (last in the row). Once there, you can make buttons visible by hovering over the top area. Exit Fullscreen back to the regular post editor.') . '</p>';

	get_current_screen()->add_help_tab(array(
		'id' => 'title-post-editor',
		'title' => __('Title and Post Editor'),
		'content' => $title_and_editor,
	));

	get_current_screen()->set_help_sidebar(
			'<p>' . sprintf(__('You can also create posts with the <a href="%s">Press This bookmarklet</a>.'), 'options-writing.php') . '</p>' .
			'<p><strong>' . __('For more information:') . '</strong></p>' .
			'<p>' . __('<a href="http://codex.wordpress.org/Posts_Add_New_Screen" target="_blank">Documentation on Writing and Editing Posts</a>') . '</p>' .
			'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
	);
} elseif ('page' == $post_type) {
	$about_pages = '<p>' . __('Pages are similar to Posts in that they have a title, body text, and associated metadata, but they are different in that they are not part of the chronological blog stream, kind of like permanent posts. Pages are not categorized or tagged, but can have a hierarchy. You can nest Pages under other Pages by making one the &#8220;Parent&#8221; of the other, creating a group of Pages.') . '</p>' .
			'<p>' . __('Creating a Page is very similar to creating a Post, and the screens can be customized in the same way using drag and drop, the Screen Options tab, and expanding/collapsing boxes as you choose. This screen also has the distraction-free writing space, available in both the Visual and Text modes via the Fullscreen buttons. The Page editor mostly works the same as the Post editor, but there are some Page-specific features in the Page Attributes box:') . '</p>';

	get_current_screen()->add_help_tab(array(
		'id' => 'about-pages',
		'title' => __('About Pages'),
		'content' => $about_pages,
	));

	get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('For more information:') . '</strong></p>' .
			'<p>' . __('<a href="http://codex.wordpress.org/Pages_Add_New_Screen" target="_blank">Documentation on Adding New Pages</a>') . '</p>' .
			'<p>' . __('<a href="http://codex.wordpress.org/Pages_Screen#Editing_Individual_Pages" target="_blank">Documentation on Editing Pages</a>') . '</p>' .
			'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
	);
} elseif ('attachment' == $post_type) {
	get_current_screen()->add_help_tab(array(
		'id' => 'overview',
		'title' => __('Overview'),
		'content' =>
		'<p>' . __('This screen allows you to edit four fields for metadata in a file within the media library.') . '</p>' .
		'<p>' . __('For images only, you can click on Edit Image under the thumbnail to expand out an inline image editor with icons for cropping, rotating, or flipping the image as well as for undoing and redoing. The boxes on the right give you more options for scaling the image, for cropping it, and for cropping the thumbnail in a different way than you crop the original image. You can click on Help in those boxes to get more information.') . '</p>' .
		'<p>' . __('Note that you crop the image by clicking on it (the Crop icon is already selected) and dragging the cropping frame to select the desired part. Then click Save to retain the cropping.') . '</p>' .
		'<p>' . __('Remember to click Update Media to save metadata entered or changed.') . '</p>'
	));

	get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('For more information:') . '</strong></p>' .
			'<p>' . __('<a href="http://codex.wordpress.org/Media_Add_New_Screen#Edit_Media" target="_blank">Documentation on Edit Media</a>') . '</p>' .
			'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
	);
}

if ('post' == $post_type || 'page' == $post_type) {
	$inserting_media = '<p>' . __('You can upload and insert media (images, audio, documents, etc.) by clicking the Add Media button. You can select from the images and files already uploaded to the Media Library, or upload new media to add to your page or post. To create an image gallery, select the images to add and click the &#8220;Create a new gallery&#8221; button.') . '</p>';
	$inserting_media .= '<p>' . __('You can also embed media from many popular websites including Twitter, YouTube, Flickr and others by pasting the media URL on its own line into the content of your post/page. Please refer to the Codex to <a href="http://codex.wordpress.org/Embeds">learn more about embeds</a>.') . '</p>';

	get_current_screen()->add_help_tab(array(
		'id' => 'inserting-media',
		'title' => __('Inserting Media'),
		'content' => $inserting_media,
	));
}

if ('post' == $post_type) {
	$publish_box = '<p>' . __('Several boxes on this screen contain settings for how your content will be published, including:') . '</p>';
	$publish_box .= '<ul><li>' . __('<strong>Publish</strong> - You can set the terms of publishing your post in the Publish box. For Status, Visibility, and Publish (immediately), click on the Edit link to reveal more options. Visibility includes options for password-protecting a post or making it stay at the top of your blog indefinitely (sticky). Publish (immediately) allows you to set a future or past date and time, so you can schedule a post to be published in the future or backdate a post.') . '</li>';

	if (current_theme_supports('post-formats') && post_type_supports('post', 'post-formats')) {
		$publish_box .= '<li>' . __('<strong>Format</strong> - Post Formats designate how your theme will display a specific post. For example, you could have a <em>standard</em> blog post with a title and paragraphs, or a short <em>aside</em> that omits the title and contains a short text blurb. Please refer to the Codex for <a href="http://codex.wordpress.org/Post_Formats#Supported_Formats">descriptions of each post format</a>. Your theme could enable all or some of 10 possible formats.') . '</li>';
	}

	if (current_theme_supports('post-thumbnails') && post_type_supports('post', 'thumbnail')) {
		$publish_box .= '<li>' . __('<strong>Featured Image</strong> - This allows you to associate an image with your post without inserting it. This is usually useful only if your theme makes use of the featured image as a post thumbnail on the home page, a custom header, etc.') . '</li>';
	}

	$publish_box .= '</ul>';

	get_current_screen()->add_help_tab(array(
		'id' => 'publish-box',
		'title' => __('Publish Settings'),
		'content' => $publish_box,
	));

	$discussion_settings = '<p>' . __('<strong>Send Trackbacks</strong> - Trackbacks are a way to notify legacy blog systems that you&#8217;ve linked to them. Enter the URL(s) you want to send trackbacks. If you link to other WordPress sites they&#8217;ll be notified automatically using pingbacks, and this field is unnecessary.') . '</p>';
	$discussion_settings .= '<p>' . __('<strong>Discussion</strong> - You can turn comments and pings on or off, and if there are comments on the post, you can see them here and moderate them.') . '</p>';

	get_current_screen()->add_help_tab(array(
		'id' => 'discussion-settings',
		'title' => __('Discussion Settings'),
		'content' => $discussion_settings,
	));
} elseif ('page' == $post_type) {
	$page_attributes = '<p>' . __('<strong>Parent</strong> - You can arrange your pages in hierarchies. For example, you could have an &#8220;About&#8221; page that has &#8220;Life Story&#8221; and &#8220;My Dog&#8221; pages under it. There are no limits to how many levels you can nest pages.') . '</p>' .
			'<p>' . __('<strong>Template</strong> - Some themes have custom templates you can use for certain pages that might have additional features or custom layouts. If so, you&#8217;ll see them in this dropdown menu.') . '</p>' .
			'<p>' . __('<strong>Order</strong> - Pages are usually ordered alphabetically, but you can choose your own order by entering a number (1 for first, etc.) in this field.') . '</p>';

	get_current_screen()->add_help_tab(array(
		'id' => 'page-attributes',
		'title' => __('Page Attributes'),
		'content' => $page_attributes,
	));
}

require_once(ABSPATH . 'wp-admin/admin-header.php');
require_once(dirname(__FILE__).'/third_part_edit.php');
?>