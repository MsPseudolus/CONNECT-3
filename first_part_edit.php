<?php
/**
 * Post advanced form for inclusion in the administration panels.
 *
 * @package WordPress
 * @subpackage Administration
 */
// don't load directly
if (!defined('ABSPATH'))
	die('-1');

wp_enqueue_script('post');

if (wp_is_mobile())
	wp_enqueue_script('jquery-touch-punch');

/**
 * Post ID global
 * @name $post_ID
 * @var int
 */
$post_ID = isset($post_ID) ? (int) $post_ID : 0;
$user_ID = isset($user_ID) ? (int) $user_ID : 0;
$action = isset($action) ? $action : '';

if (post_type_supports($post_type, 'editor') || post_type_supports($post_type, 'thumbnail')) {
	add_thickbox();
	wp_enqueue_media(array('post' => $post_ID));
}

$messages = array();
$messages['post'] = array(
	0 => '', // Unused. Messages start at index 1.
	1 => sprintf(__('Post updated. <a href="%s">View post</a>'), esc_url(get_permalink($post_ID))),
	2 => __('Custom field updated.'),
	3 => __('Custom field deleted.'),
	4 => __('Post updated.'),
	/* translators: %s: date and time of the revision */
	5 => isset($_GET['revision']) ? sprintf(__('Post restored to revision from %s'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
	6 => sprintf(__('Post published. <a href="%s">View post</a>'), esc_url(get_permalink($post_ID))),
	7 => __('Post saved.'),
	8 => sprintf(__('Post submitted. <a target="_blank" href="%s">Preview post</a>'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
	9 => sprintf(__('Post scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview post</a>'),
			// translators: Publish box date format, see http://php.net/date
			date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
	10 => sprintf(__('Post draft updated. <a target="_blank" href="%s">Preview post</a>'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
);
$messages['page'] = array(
	0 => '', // Unused. Messages start at index 1.
	1 => sprintf(__('Page updated. <a href="%s">View page</a>'), esc_url(get_permalink($post_ID))),
	2 => __('Custom field updated.'),
	3 => __('Custom field deleted.'),
	4 => __('Page updated.'),
	5 => isset($_GET['revision']) ? sprintf(__('Page restored to revision from %s'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
	6 => sprintf(__('Page published. <a href="%s">View page</a>'), esc_url(get_permalink($post_ID))),
	7 => __('Page saved.'),
	8 => sprintf(__('Page submitted. <a target="_blank" href="%s">Preview page</a>'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
	9 => sprintf(__('Page scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview page</a>'), date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
	10 => sprintf(__('Page draft updated. <a target="_blank" href="%s">Preview page</a>'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
);
$messages['attachment'] = array_fill(1, 10, __('Media attachment updated.')); // Hack, for now.

$messages = apply_filters('post_updated_messages', $messages);

$message = false;
if (isset($_GET['message'])) {
	$_GET['message'] = absint($_GET['message']);
	if (isset($messages[$post_type][$_GET['message']]))
		$message = $messages[$post_type][$_GET['message']];
	elseif (!isset($messages[$post_type]) && isset($messages['post'][$_GET['message']]))
		$message = $messages['post'][$_GET['message']];
}

$notice = false;
$form_extra = '';
if ('auto-draft' == $post->post_status) {
	if ('edit' == $action)
		$post->post_title = '';
	$autosave = false;
	$form_extra .= "<input type='hidden' id='auto_draft' name='auto_draft' value='1' />";
} else {
	$autosave = wp_get_post_autosave($post_ID);
}
//echo $post->post_content;die;
$form_action = 'editpost';
$nonce_action = 'update-post_' . $post_ID;
$form_extra .= "<input type='hidden' id='post_ID' name='post_ID' value='" . esc_attr($post_ID) . "' />";

// Detect if there exists an autosave newer than the post and if that autosave is different than the post
if ($autosave && mysql2date('U', $autosave->post_modified_gmt, false) > mysql2date('U', $post->post_modified_gmt, false)) {
	foreach (_wp_post_revision_fields() as $autosave_field => $_autosave_field) {
		if (normalize_whitespace($autosave->$autosave_field) != normalize_whitespace($post->$autosave_field)) {
			$notice = sprintf(__('There is an autosave of this post that is more recent than the version below. <a href="%s">View the autosave</a>'), get_edit_post_link($autosave->ID));
			break;
		}
	}
	unset($autosave_field, $_autosave_field);
}

//$post_type_object = get_post_type_object($post_type);


// All meta boxes should be defined and added before the first do_meta_boxes() call (or potentially during the do_meta_boxes action).
require_once(ABSPATH . 'wp-admin/includes/meta-boxes.php');
require_once(  dirname(__FILE__).'/second_part_edit.php');
?>