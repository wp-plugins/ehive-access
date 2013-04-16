<?php
/*
	Plugin Name: eHive Access
	Plugin URI: http://developers.ehive.com/wordpress-plugins/
	Description: Base authentication and API access for eHive Wordpress plugins.
	Author: Vernon Systems limited
	Version: 2.1.0
	Author URI: http://vernonsystems.com
	License: GPL2+
*/
/*
	Copyright (C) 2012 Vernon Systems Limited

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
if (!class_exists('EHiveAccess')) {

	class eHiveAccess {
	
		
		function __construct() {
	
			add_action("admin_init", array(&$this, "ehive_access_admin_options_init"));
	
			add_action("admin_menu", array(&$this, "ehive_access_admin_menu"));	
		}

		/*
		 * Admin init
		 */
		function ehive_access_admin_options_init(){
			
			wp_register_style($handle = 'eHiveAdminCSS', $src = plugins_url('eHiveAdmin.css', '/eHiveAccess/css/eHiveAdmin.css'));
			wp_enqueue_style('eHiveAdminCSS');
			
			wp_register_script($handle = 'options', $src = plugins_url('options.js', '/eHiveAccess/js/options.js'), $deps = array('jquery'), $ver = '1.0.0', false);
			wp_enqueue_script( 'options' );
				
			register_setting('ehive_access_options', 'ehive_access_options', array(&$this, 'plugin_options_validate') );
		
			add_settings_section('comment_section', '', array(&$this, 'comment_section_text_fn'), __FILE__);
		
			add_settings_section('oauth_section', 'OAuth Credentials', array(&$this, 'oauth_section_text_fn'), __FILE__);
			
			add_settings_section('site_section', 'Site type', array(&$this, 'site_section_text_fn'), __FILE__);
				
			add_settings_section('page_configuration_section', 'Page configuration', array(&$this, 'page_configuration_section_text_fn'), __FILE__);
			
			add_settings_section('error_notification_section', 'API Error Notification', array(&$this, 'error_notification_section_fn'), __FILE__);
		}
		
		/*
		 * Add admin stylesheet
		 */
		function ehive_access_admin_enqueue_styles() {
			wp_enqueue_style('eHiveAdminCSS');
		}
		
		/*
		 * Validation
		 */
		function plugin_options_validate($input) {
			add_settings_error('ehive_access_options', 'updated', 'eHive Access settings saved.', 'updated');
			return $input;
		}
		
		/*
		 * Options page content
		 */
		function comment_section_text_fn() {
			echo "<p><em>An overview of the plugin is available in the help.</em></p>";
		}
		
		function oauth_section_text_fn() {
			add_settings_field('client_id', 'OAuth2 client id', array(&$this, 'client_id_fn'), __FILE__, 'oauth_section');
			add_settings_field('client_secret', 'OAuth2 client secret', array(&$this, 'client_secret_fn'), __FILE__, 'oauth_section');
			add_settings_field('tracking_id', 'eHive tracking id', array(&$this, 'tracking_id_fn'), __FILE__, 'oauth_section');
		}
		
		function site_section_text_fn() {
			add_settings_field('site_type', 'Site type', array(&$this, 'site_type_fn'), __FILE__, 'site_section');
			add_settings_field('account_id', 'eHive account id', array(&$this, 'account_id_fn'), __FILE__, 'site_section');
			add_settings_field('community_id', 'eHive community id', array(&$this, 'community_id_fn'), __FILE__, 'site_section');
		}
		
		function page_configuration_section_text_fn() {
			add_settings_field('account_details_page', 'Account Details Page', array(&$this, 'account_details_page_fn'), __FILE__, 'page_configuration_section');
			add_settings_field('object_details_page', 'Object Details Page', array(&$this, 'object_details_page_fn'), __FILE__, 'page_configuration_section');
			add_settings_field('search_page', 'Search Page', array(&$this, 'search_page_fn'), __FILE__, 'page_configuration_section');
		}
		
		function error_notification_section_fn() {
			add_settings_field('ehive_api_error_message', "Error message", array(&$this, 'ehive_api_error_message_section_fn'), __FILE__, 'error_notification_section');
		}
		
		/*************************
		 * OAUTH OPTIONS SECTION *
		 *************************/
		function client_id_fn() {
			$options = get_option('ehive_access_options');
			echo '<input class="regular-text" id="client_id" name="ehive_access_options[client_id]" type="text" value="'.$options['client_id'].'" />';
		}
		
		function client_secret_fn() {
			$options = get_option('ehive_access_options');
			echo '<input class="regular-text" id="client_secret" name="ehive_access_options[client_secret]" type="text" value="'.$options['client_secret'].'" />';
		}
		
		function tracking_id_fn() {
			$options = get_option('ehive_access_options');
			echo '<input class="regular-text" id="tracking_id" name="ehive_access_options[tracking_id]" type="text" value="'.$options['tracking_id'].'" />';
		}
		
		/************************
		 * SITE OPTIONS SECTION *
		 ************************/
		function site_type_fn() {
			$options = get_option('ehive_access_options');
			$items = array("Account", "Community", "eHive");
			foreach($items as $item) {
				$checked = ($options['site_type']==$item) ? ' checked="checked" ' : '';
				echo "<label><input ".$checked." value='$item' name='ehive_access_options[site_type]' type='radio' /> $item</label><br />";
			}
		}
		
		function account_id_fn() {
			$options = get_option('ehive_access_options');
			echo '<input class="small-text" id="account_id" name="ehive_access_options[account_id]" type="number" value="'.$options['account_id'].'" />';
		}
		
		function community_id_fn() {
			$options = get_option('ehive_access_options');
			echo '<input class="small-text" id="community_id" name="ehive_access_options[community_id]" type="number" value="'.$options['community_id'].'" />';
		}
		
		/**************************************
		 * PAGE CONFIGURATION OPTIONS SECTION *
		 **************************************/
		function account_details_page_fn() {
			$options = get_option('ehive_access_options');
			$pages = get_pages();
			echo "<select id='account_details_page' name='ehive_access_options[account_details_page]'>";
			foreach($pages as $page) {
				$selected = ($options['account_details_page']==$page->ID) ? 'selected="selected"' : '';
				echo "<option value='{$page->ID}' {$selected}>{$page->post_title}</option>";
			}
			echo "</select>";
		}
		
		function object_details_page_fn() {
			$options = get_option('ehive_access_options');
			$pages = get_pages();
			echo "<select id='object_details_page' name='ehive_access_options[object_details_page]'>";
			foreach($pages as $page) {
				$selected = ($options['object_details_page']==$page->ID) ? 'selected="selected"' : '';
				echo "<option value='{$page->ID}' {$selected}>{$page->post_title}</option>";
			}
			echo "</select>";
		}
		
		function  search_page_fn() {
			$options = get_option('ehive_access_options');
			$pages = get_pages();
			echo "<select id='search_page' name='ehive_access_options[search_page]'>";
			foreach($pages as $page) {
				$selected = ($options['search_page']==$page->ID) ? 'selected="selected"' : '';
				echo "<option value='{$page->ID}' {$selected}>{$page->post_title}</option>";
			}
			echo "</select>";
		}
				
		/**************************************
		 * ERROR NOTIFICATION OPTIONS SECTION *
		**************************************/
		function ehive_api_error_message_section_fn() {
			$options = get_option('ehive_access_options');
			if(isset($options['ehive_api_error_notification_enabled']) && $options['ehive_api_error_notification_enabled'] == 'on') {
				$checked = ' checked="checked" ';
			}
			echo "<textarea class='regular-text' id='ehive_api_error_message' name='ehive_access_options[ehive_api_error_message]' style='resize:none;' cols='40' rows='5' >{$options['ehive_api_error_message']}</textarea>";
			echo "<td><input ".$checked." id='ehive_api_error_notification_enabled' name='ehive_access_options[ehive_api_error_notification_enabled]' type='checkbox' /></td>";
			echo "<tr><th colspan='3'><i>*This message is displayed when access to the eHive API fails.</i></th></tr>";
		}
		
		/*
		 * Admin menu setup
		 */
		function ehive_access_admin_menu() {
		
			global $ehive_access_options_page;
		
			$ehive_access_options_page = add_menu_page('eHive Base settings', 'eHive', 'manage_options', 'ehive_access', array(&$this, 'ehive_access_options_page'), plugin_dir_url( __FILE__ ).'images/eHiveMenu.ico' );
			                             
			add_submenu_page('EHive', 'eHive Access', 'Api Access', 'manage_options', 'EHive', array(&$this, 'ehive_access_options_page'));
		
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'ehive_access_plugin_action_links'), 10, 2);				
			
			add_action("admin_print_styles-" . $ehive_access_options_page, array(&$this, "ehive_access_admin_enqueue_styles") );
				
			add_action("load-$ehive_access_options_page",array(&$this, "ehive_access_options_help"));				
		}
		
		/*
		 * Admin menu link
		 */
		function ehive_access_plugin_action_links($links, $file) {
			$settings_link = '<a href="admin.php?page=ehive_access">' . __('Settings') . '</a>';
			array_unshift($links, $settings_link); // before other links
			return $links;
		}
	
		/*
		 * Plugin options help setup
		 */
		function ehive_access_options_help() {
			global $ehive_access_options_page;
	
			$screen = get_current_screen();
			if ($screen->id != $ehive_access_options_page) {
				return;
			}
			
			$screen->add_help_tab( array('id'		=> 'ehive-access-overview',
	                                	 'title'	=> 'Overview',
	                                	 'content'	=> '<p>eHive Access</p>',
			));
			
			$screen->set_help_sidebar('<p><strong>For more information:</strong></p><p><a href="http://developers.ehive.com/wordpress-plugins#ehiveaccess/" target="_blank">Documentation for eHive plugins</a></p>');
		}
	
		/*
		 * Options page setup
		 */
		function ehive_access_options_page() {
			?>
		    <div class="wrap">
				<div class="icon32" id="icon-options-ehive"><br></div>
				<h2>eHive Access Settings</h2>   
				<?php settings_errors();?>     		
				<form action="options.php" method="post">
					<?php settings_fields('ehive_access_options'); ?>
					<?php do_settings_sections(__FILE__); ?>
					<p class="submit">
						<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
					</p>
				</form>
			</div>
			<?php
		}

		/*
		 * eHive plugin suite utility functions
		 */
		public function eHiveApi() {
			
			$dir = plugin_dir_path(__FILE__);
			
			require_once  plugin_dir_path(__FILE__).'ehive_api_client-php/EHiveApi.php';
			
			$options = get_option('ehive_access_options');
			
			$oauthTokenCallback = function($oauthToken) {
				if( !isset($options['oauth_token']) ) {
					add_option('ehive_access_options', 'oauth_token', $oauthToken);
				} else {
					update_option('oauth_token', $oauthToken);
				}				
			};
			
			$oauthToken = '';
			if (isset($options['oauth_token'])) {
				$oauthToken = $options['oauth_token'];
			}
									
			$eHiveApi = new EHiveApi($options['client_id'], $options['client_secret'], $options['tracking_id'], $oauthToken,  $oauthTokenCallback);
			return $eHiveApi;
		}
		
		
		public function getSiteType() {
			$options = get_option('ehive_access_options');
			return $options[site_type];				
		}
		
		public function getAccountId() {
			$options = get_option('ehive_access_options');
			return $options[account_id];
		}
		
		public function getCommunityId() {
			$options = get_option('ehive_access_options');
			return $options[community_id];
		}
				
		public function getAccountDetailsPageId(){
			$options = get_option('ehive_access_options');
			$pageId = $options['account_details_page'];
			return $pageId;
		}
		
		public function getIsErrorNotificationEnabled(){
			$options = get_option('ehive_access_options');
			$eHiveApiErrorNotificationEnabled = $options['ehive_api_error_notification_enabled'];
			return ($eHiveApiErrorNotificationEnabled == 'on' ? true : false);
		}
		
		public function getErrorMessage(){
			$options = get_option('ehive_access_options');
			$eHiveApiErrorMessage = $options['ehive_api_error_message'];
			return $eHiveApiErrorMessage;
		}
		
		public function getAccountDetailsPageLink( $accountId ){
			
			$options = get_option('ehive_access_options');
			
			$pageId = $options['account_details_page'];
		
			if ($pageId != 0) {
				return get_permalink( $pageId ).$accountId;
			} else {
				return '#';
			}
		}
		
		
		public function getObjectDetailsPageId(){
			$options = get_option('ehive_access_options');			
			$pageId = $options['object_details_page'];			
			return $pageId;
		}
		
		public function getObjectDetailsPageLink( $objectRecordId ){
			
			$options = get_option('ehive_access_options');

			$pageId = $options['object_details_page'];
		
			if ($pageId != 0) {
				return get_permalink( $pageId ).$objectRecordId;
			} else {
				return '#';
			}
		}
		
		public function getSearchPageId(){
			$options = get_option('ehive_access_options');
			$pageId = $options['search_page'];
			return $pageId;
		}
		
		function getSearchPageLink( $args = '' ){

			$options = get_option('ehive_access_options');

			$pageId = $options['search_page'];
		
			if ($pageId != 0) {		
				$link = get_permalink( $pageId );
				$link = rtrim($link, "/");
		
				return $link.$args;
			} else {
				return '#';
			}
		}
		
		/*
		 * On plugin activate
		 */
		public function activate() {
			$defaultMessage = 'Some content on '.get_bloginfo('name','display').' is currently unavailable please try again later.'; 
			$arr = array("client_id"=>"", 
						 "client_secret"=>"",
						 "oauth_token"=>"", 
						 "tracking_id"=>"",
						 "ehive_api_error_notification_enabled" => '',
						 "ehive_api_error_message" => $defaultMessage );
		
			update_option('ehive_access_options', $arr);		
		}
		
		/*
		 * On plugin deactivate
		 */
		public function deactivate() {
			delete_option('ehive_access_options');
		}				
	}
	
	include_once('functions.php');
	
	$eHiveAccess = new EHiveAccess();
	
	add_action('activate_eHiveAccess/EHiveAccess.php', array(&$eHiveAccess, 'activate'));
	add_action('deactivate_eHiveAccess/EHiveAccess.php', array(&$eHiveAccess, 'deactivate'));
}
?>