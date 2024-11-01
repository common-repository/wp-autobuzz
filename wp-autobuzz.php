<?php
/*
Plugin Name: WP AutoBuzz
Plugin URI: http://godlessons.com/plugins/
Description: Automatically add your posts to Google Buzz.
Version: 1.1.1
Author: Godlessons
Author URI: http://godlessons.com/plugins/
License: GPL2
*/
/*  Copyright 2010  Timm Simpkins  (email : poduck@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
 
function wpab_localize () {
	load_plugin_textdomain ('wp_autobuzz','/wp-content/plugins/wp-autobuzz/');
}
function add_buzz_identifier() {
	global $wp_query;
	if (is_home()) {
		echo '<link rel="me" type="text/html" href="http://www.google.com/profiles/' . get_option('autoBuzzUser') . '" />';
	}
	if (is_author()) {
		$user_info = $wp_query->get_queried_object();
		if (get_the_author_meta('associate_google_profile',$user_info->ID) == 'on') {
			if (get_option('allow_author') == 'on') {
				echo '<link rel="me" type="text/html" href="http://www.google.com/profiles/' . esc_attr(get_the_author_meta('google_profile', $user_info->ID)) . '" />';
			}
		}
	}
}
function init_wpab(){
	add_option('autoBuzzUser','');
	add_option('allow_author','');
}
function wpabAdmin() {
	add_options_page('WP AutoBuzz','WP AutoBuzz','administrator','wpaboptions','createAdminPage');
}
function add_google_profile($user) {
?>
<h3><?php _e('Google Profile Information','wp_autobuzz'); ?></h3>
<table class="form-table">
  <tr>
    <th><label for="google"><?php _e('Google Profile','wp_autobuzz'); ?></label></th>
    <td><input type="text" name="google_profile" id="google_profile" value="<?php echo esc_attr(get_the_author_meta('google_profile', $user->ID)); ?>" class="regular-text" />
      <span class="description"><?php _e('Please enter your Google Profile ID (*ID*@gmail.com)','wp_autobuzz'); ?></span></td>
  </tr>
  <tr>
    <th><label for="google"><?php _e('Associate Google Profile','wp_autobuzz'); ?></label></th>
    <td><input name="associate_google_profile" type="checkbox"<?php
    if (get_the_author_meta('associate_google_profile', $user->ID) == 'on') {
    	echo 'checked="on"';
    } ?>" />
      <span class="description"><?php _e('Add link to allow Google to associate author feed with your profile','wp_autobuzz'); ?></span></td>
  </tr>
</table>
<?php
}
function update_google_profile($user_id) {
	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
	update_usermeta( $user_id, 'google_profile', $_POST['google_profile'] );
	update_usermeta( $user_id, 'associate_google_profile', $_POST['associate_google_profile'] );
}
function createAdminPage(){
	if(!isset($_POST['update_wpab_settings'])) {
		$account = get_option('autoBuzzUser');
		$allow_author = get_option('allow_author');
	} else {
		update_option('autoBuzzUser',$_POST['googleAccount']);
		update_option('allow_author',$_POST['allow_author']);
		$account = $_POST['googleAccount'];
		$allow_author = $_POST['allow_author'];
	}?>
<div class="wrap">
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<h2><?php _e('WP AutoBuzz','wp_autobuzz');?></h2>
<h3><?php _e('Settings','wp_autobuzz'); ?></h3>
<table class="form-table">
	<tr>
		<th><label for="google"><?php _e('Home Page Google Account:','wp_autobuzz')?></label></th> 
		<td><input name="googleAccount" type="text" id="googleAccount" value="<?php echo apply_filters('format_to_edit',$account) ?>" />
		<span class="description"><?php _e('Enter Google account name that appears before @ in your email address.','wp_autobuzz');?></span></td>
	</tr>
	<tr>
		<th><label><?php _e('Allow per author profile','wp_autobuzz')?></label></th>
		<td><input name="allow_author" type="checkbox" <?php if ($allow_author == 'on') {echo 'checked="on" ';} ?>/>
  			<span class="description"><?php _e('Allows authors to add their author feed to their own Google Buzz','wp_autobuzz'); ?></span></td>
	</tr>
</table>
</p>
<?php if (isset($_POST['update_wpab_settings'])) {?><p><h3><?php _e('Updated Successfully', 'wp_autobuzz');?></h3></p><?php }?>
<p><span style="color: #F00"><?php _e('IMPORTANT: Before Google Buzz will import your RSS feed, you must link this site with Google Buzz.','wp_autobuzz')?></span><br />
  <?php _e("Go to your Google Profile and edit it.  At the bottom will be a section on the right that says &quot;My links&quot;.  If you don't see your blog there, below that should be an area that says, &quot;Add custom links to my profile.&quot;  Enter the link to your blog there and submit.", 'wp_autobuzz');?>
</p>
<p><?php _e("Once you have linked your blog to your profile, you can either wait for google to crawl your blog, or you can force it to crawl your blog by going to the <a href=\"https://sgapi-recrawl.appspot.com/\">recrawl tool</a>. Once there and logged in, click the recrawl button next to the website you want to add. After that, all you have to do is add it to your connected sites in buzz and voila.", 'wp_autobuzz');?></p>
<div class="submit">
  <input type="submit" name="update_wpab_settings" value="Update Settings" /></div>
</form>
 </div>
<?php
}
add_action ('init', 'wpab_localize');
register_activation_hook(__FILE__,'init_wpab');
if (is_admin()){
	add_action('admin_menu','wpabAdmin');
	if (get_option('allow_author') == 'on'){
		add_action('show_user_profile','add_google_profile');
		add_action('edit_user_profile','add_google_profile');
		add_action('personal_options_update','update_google_profile');
		add_action('edit_user_profile_update','update_google_profile');
	}
} else {
	add_action('wp_footer','add_buzz_identifier');
}
?>