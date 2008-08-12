<?php
/*
 Plugin Name: OpenID
 Plugin URI: http://wordpress.org/extend/plugins/openid
 Description: Allows the use of OpenID for account registration, authentication, and commenting.  <em>By <a href="http://verselogic.net">Alan Castonguay</a>.</em>
 Author: Will Norris
 Author URI: http://willnorris.com/
 Version: trunk
 License: Dual GPL (http://www.fsf.org/licensing/licenses/info/GPLv2.html) and Modified BSD (http://www.fsf.org/licensing/licenses/index_html#ModifiedBSD)
 */

define ( 'WPOPENID_PLUGIN_REVISION', preg_replace( '/\$Rev: (.+) \$/', 'svn-\\1',
	'$Rev$') ); // this needs to be on a separate line so that svn:keywords can work its magic

define ( 'WPOPENID_DB_REVISION', 24426);      // last plugin revision that required database schema changes


require_once( dirname(__FILE__) . '/common.php');
require_once( dirname(__FILE__) . '/admin_panels.php');
require_once( dirname(__FILE__) . '/comments.php');
require_once( dirname(__FILE__) . '/wp-login.php');
require_once( dirname(__FILE__) . '/server.php');

@session_start();

// -- public functions

/**
 * Check if the user has any OpenIDs.
 *
 * @param mixed $user the username or ID  If not provided, the current user will be used.
 * @return bool true if the user has any OpenIDs
 */
function is_user_openid($user = null) {
	$urls = get_user_openids($user);
	return (!empty($urls));
}


/**
 * If the current comment was submitted with OpenID, return true
 * useful for  <?php echo ( is_comment_openid() ? 'Submitted with OpenID' : '' ); ?>
 */
// TODO: should we take a parameter here?
function is_comment_openid() {
	global $comment;
	return ( $comment->openid == 1 );
}


/**
 * Get the OpenID identities for the specified user.
 *
 * @param mixed $id_or_name the username or ID.  If not provided, the current user will be used.
 * @return array array of user's OpenID identities
 */
function get_user_openids($id_or_name = null) {
	$user = get_userdata_by_various($id_or_name);
	return openid_get_identities($user->ID);
}


/**
 * Get the user associated with the specified OpenID.
 *
 * @param string $openid identifier to match
 * @return int|null ID of associated user, or null if no associated user
 */
function get_user_by_openid($url) {
	global $wpdb;
	return $wpdb->get_var( $wpdb->prepare('SELECT user_id FROM '.openid_identity_table().' WHERE url = %s', $url) );
}


/**
 * Get a simple OpenID input field, used for disabling unobtrusive mode.
 */
function openid_input() {
	return '<input type="text" id="openid_identifier" name="openid_identifier" />';
}


/**
 * Convenience method to get user data by ID, username, or from current user.
 *
 * @param mixed $id_or_name the username or ID.  If not provided, the current user will be used.
 * @return bool|object False on failure, User DB row object
 */
if (!function_exists('get_user_by_various')) :
function get_userdata_by_various($id_or_name = null) {
	if ($id_or_name === null) {
		$user = wp_get_current_user();
		if ($user == null) return false;
		return $user->data;
	} else if (is_numeric($id_or_name)) {
		return get_userdata($id_or_name);
	} else {
		return get_userdatabylogin($id_or_name);
	}
}
endif;

?>
