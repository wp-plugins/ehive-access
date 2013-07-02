<?php
/*
	Plugin Name: eHive Access
	Plugin URI: http://developers.ehive.com/wordpress-plugins/
	Description: Base authentication and API access for eHive Wordpress plugins.
	Author: Vernon Systems limited
	Version: 2.2.0
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
	
		const CURRENT_VERSION = 1; // Increment each time an upgrade is required / options added or deleted.
		const EHIVE_ACCESS_OPTIONS = "ehive_access_options";		
		
		function __construct() {
	
			add_action("admin_init", array(&$this, "ehive_access_admin_options_init"));
	
			add_action("admin_menu", array(&$this, "ehive_access_admin_menu"));	
		}

		function ehive_access_admin_options_init(){
			
			$this->ehive_plugin_update();
			
			wp_register_style($handle = 'eHiveAdminCSS', $src = plugins_url('eHiveAdmin.css', '/ehive-access/css/eHiveAdmin.css'));
			wp_enqueue_style('eHiveAdminCSS');
			
			wp_register_script($handle = 'options', $src = plugins_url('options.js', '/ehive-access/js/options.js'), $deps = array('jquery'), $ver = '1.0.0', false);
			wp_enqueue_script( 'options' );
				
			register_setting('ehive_access_options', 'ehive_access_options', array(&$this, 'plugin_options_validate') );
		
			add_settings_section('comment_section', '', array(&$this, 'comment_section_text_fn'), __FILE__);
		
			add_settings_section('oauth_section', 'API Keys', array(&$this, 'oauth_section_text_fn'), __FILE__);
			
			add_settings_section('site_section', 'Site type', array(&$this, 'site_section_text_fn'), __FILE__);
				
			add_settings_section('page_configuration_section', 'Page configuration', array(&$this, 'page_configuration_section_text_fn'), __FILE__);
			
			add_settings_section('ehive_api_section', 'eHive API', array(&$this, 'ehive_api_section_fn'), __FILE__);
		}
		
		function ehive_access_admin_enqueue_styles() {
			wp_enqueue_style('eHiveAdminCSS');
		}
		
		function plugin_options_validate($input) {

			//
			//	Validate OAuth Credentials
			//			
			$input['client_id'] = trim($input['client_id']);
			$input['client_secret'] = trim($input['client_secret']);
			$input['tracking_id'] = trim($input['tracking_id']);
			
			if ($input['client_id'] == '' || $input['client_secret'] == '' || $input['tracking_id'] == '') {
				add_settings_error('ehive_access_options', 'oauth', 'API keys are required, use the API keys configured in your eHive account. Reference this <a href="http://developers.ehive.com/authentication/" target="_blank">guide about API keys</a> at the <a href="http://developers.ehive.com/" target="_blank">developers.ehive.com</a> site.', 'error');				
			} else {
				// FIXME: add an endpoint into the API to validate the API keys.
			}			
			
			
			//
			// Validate memcache settings.
			// 
			if (isset($input['memcache_enabled']) && $input['memcache_enabled']== "on") {
								
				$input['memcache_expiry'] = trim($input['memcache_expiry']);
				if (is_numeric($input['memcache_expiry']) === false) {
					$input['memcache_expiry'] = 300;
				} else {
					$input['memcache_expiry'] = abs($input['memcache_expiry']); 
				}
				
				$input['memcached_servers'] = trim($input['memcached_servers']);

				
				if ( !class_exists("Memcache")) {
					add_settings_error('ehive_access_options', 'memcache-config', 'PHP Memcache needs to be installed.', 'error');
				
				} else {
					
					$hostsPorts = explode( ",", $input['memcached_servers'] );
					
					if ( count($hostsPorts) == 0) {
						add_settings_error('ehive_access_options', 'memcached-config', 'Memcached servers must be configured.', 'error');						
					
					} else {
			
						for ($i=0; $i<count($hostsPorts); $i++ ){
			
							$hostPort = explode(":", $hostsPorts[$i]);
							if ( count($hostPort) == 2 ) {

								$memcache = new Memcache();
								if ($memcache->connect($hostPort[0], $hostPort[1])){
									// Good connection.
								} else {
									add_settings_error('ehive_access_options', 'memcached-config', 'Memcached server connection failed for "'.$hostsPorts[$i].' ".', 'error');
								}
								
							} else {	
								add_settings_error('ehive_access_options', 'memcached-config', 'Memcached servers must be configured correctly.', 'error');								
							}							
						}								
					}
				}
			}			

			
			//
			// Retain the plugin version on save of opotions.
			//
			$input["update_version"] = self::CURRENT_VERSION;
				
			//
			// Retain the oauth_token on save of opotions.
			//
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			if ( array_key_exists('oauth_token', $options)) {
				$input["oauth_token"] = $options['oauth_token'];
			}
							
			add_settings_error('ehive_access_options', 'updated', 'eHive Access settings saved.', 'updated');
								
			return $input;
		}
		
		function comment_section_text_fn() {
			echo "<p><em>An overview of the plugin is available in the help.</em></p>";
		}
		
		function oauth_section_text_fn() {
			add_settings_field('client_id', 'Client id', array(&$this, 'client_id_fn'), __FILE__, 'oauth_section');
			add_settings_field('client_secret', 'Client secret', array(&$this, 'client_secret_fn'), __FILE__, 'oauth_section');
			add_settings_field('tracking_id', 'Tracking id', array(&$this, 'tracking_id_fn'), __FILE__, 'oauth_section');
		}
		
		function site_section_text_fn() {
			add_settings_field('site_type', 'Site type', array(&$this, 'site_type_fn'), __FILE__, 'site_section');
			add_settings_field('account_id', 'eHive account id', array(&$this, 'account_id_fn'), __FILE__, 'site_section');
			add_settings_field('community_id', 'eHive community id', array(&$this, 'community_id_fn'), __FILE__, 'site_section');		
			add_settings_field('private_record_search_enabled', 'Allow searching for private records', array(&$this, 'private_record_search_enabled_fn'), __FILE__, 'site_section');				
		}
		
		function page_configuration_section_text_fn() {
			add_settings_field('account_details_page', 'Account Details Page', array(&$this, 'account_details_page_fn'), __FILE__, 'page_configuration_section');
			add_settings_field('object_details_page', 'Object Details Page', array(&$this, 'object_details_page_fn'), __FILE__, 'page_configuration_section');
			add_settings_field('search_page', 'Search Page', array(&$this, 'search_page_fn'), __FILE__, 'page_configuration_section');
		}
		
		function ehive_api_section_fn() {						
			add_settings_field('memcache_enabled', 'Enable caching for API calls', array(&$this, 'memcache_enabled_fn'), __FILE__, 'ehive_api_section');
			add_settings_field('memcache_expiry', 'Cache item expiry', array(&$this, 'memcache_expiry_fn'), __FILE__, 'ehive_api_section');
			add_settings_field('memcached_servers', 'Memcached servers', array(&$this, 'memcached_servers_fn'), __FILE__, 'ehive_api_section');
			add_settings_field('ehive_api_error_notification_enabled', "Enable error message ", array(&$this, 'ehive_api_error_notification_enabled_fn'), __FILE__, 'ehive_api_section');			
			add_settings_field('ehive_api_error_message', "Error message", array(&$this, 'ehive_api_error_message_fn'), __FILE__, 'ehive_api_section');
		}
		
		//
		//	oAuth options section
		//
		function client_id_fn() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			echo '<input class="regular-text" id="client_id" name="ehive_access_options[client_id]" type="text" value="'.$options['client_id'].'" />';
		}
		
		function client_secret_fn() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			echo '<input class="regular-text" id="client_secret" name="ehive_access_options[client_secret]" type="text" value="'.$options['client_secret'].'" />';
		}
		
		function tracking_id_fn() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			echo '<input class="regular-text" id="tracking_id" name="ehive_access_options[tracking_id]" type="text" value="'.$options['tracking_id'].'" />';
		}
		
		//
		//	Site options section
		//
		function site_type_fn() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			$items = array("Account", "Community", "eHive");
			foreach($items as $item) {
				$checked = ($options['site_type']==$item) ? ' checked="checked" ' : '';
				echo "<label><input ".$checked." value='$item' name='ehive_access_options[site_type]' type='radio' /> $item</label><br />";
			}
		}
		
		function account_id_fn() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			echo '<input class="small-text" id="account_id" name="ehive_access_options[account_id]" type="number" value="'.$options['account_id'].'" />';
		}
		
		function community_id_fn() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			echo '<input class="small-text" id="community_id" name="ehive_access_options[community_id]" type="number" value="'.$options['community_id'].'" />';
		}
		
		function private_record_search_enabled_fn() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			if(isset($options['private_record_search_enabled']) && $options['private_record_search_enabled'] == 'on') {
				$checked = ' checked="checked" ';
			}
			echo "<input {$checked} id='private_record_search_enabled' name='ehive_access_options[private_record_search_enabled]' type='checkbox' />";
			echo '<p>Private records will be included in search results. The object records in the search results will only include public fields, regardless of whether the records are flagged as public or private in eHive. This option is only valid when the site type is Account</p>';
        }
		
		
		//
		//	Page configuration options section
		//
		function account_details_page_fn() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			$pages = get_pages();
			echo "<select id='account_details_page' name='ehive_access_options[account_details_page]'>";
			foreach($pages as $page) {
				$selected = ($options['account_details_page']==$page->ID) ? 'selected="selected"' : '';
				echo "<option value='{$page->ID}' {$selected}>{$page->post_title}</option>";
			}
			echo "</select>";
		}
		
		function object_details_page_fn() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			$pages = get_pages();
			echo "<select id='object_details_page' name='ehive_access_options[object_details_page]'>";
			foreach($pages as $page) {
				$selected = ($options['object_details_page']==$page->ID) ? 'selected="selected"' : '';
				echo "<option value='{$page->ID}' {$selected}>{$page->post_title}</option>";
			}
			echo "</select>";
		}
		
		function  search_page_fn() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			$pages = get_pages();
			echo "<select id='search_page' name='ehive_access_options[search_page]'>";
			foreach($pages as $page) {
				$selected = ($options['search_page']==$page->ID) ? 'selected="selected"' : '';
				echo "<option value='{$page->ID}' {$selected}>{$page->post_title}</option>";
			}
			echo "</select>";
		}
				
		
		//
		//	eHive API options
		//		
		function memcache_enabled_fn() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			if(isset($options['memcache_enabled']) && $options['memcache_enabled'] == 'on') {
				$checked = ' checked="checked" ';
			}
			echo "<input {$checked} id='memcache_enabled' name='ehive_access_options[memcache_enabled]' type='checkbox' />";
			echo '<p>Requires <a href="http://php.net/manual/en/book.memcache.php" target="_blank">PHP Memcache</a> to be installed and access to a <a href="http://memcached.org/">Memcached</a> service.</p>';			
		}

		function memcache_expiry_fn() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			echo '<input class="medium-text" id="memcache_expiry" name="ehive_access_options[memcache_expiry]" type="number" value="'.$options['memcache_expiry'].'" />';
			echo "<p>Expiration time of the item. If it's equal to zero, the item will never expire. You can also use Unix timestamp or a number of seconds starting from current time, but in the latter case the number of seconds may not exceed 2592000 (30 days).</p>";
		}
		
		function memcached_servers_fn() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			echo '<input class="regular-text" id="memcached_servers" name="ehive_access_options[memcached_servers]" type="text" value="'.$options['memcached_servers'].'" />';
			echo '<p>A comma separated list of hosts and ports.<br/><strong>Examples:</strong><br/>"<strong>localhost:11211</strong>" - a single Memcached service.<br/>"<strong>192.168.1.2:11211, 192.168.1.3:11211</strong>" - two Memcached services on two different servers.</p>';				
		}

		function ehive_api_error_notification_enabled_fn() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			if(isset($options['ehive_api_error_notification_enabled']) && $options['ehive_api_error_notification_enabled'] == 'on') {
				$checked = ' checked="checked" ';
			}
			echo "<input ".$checked." id='ehive_api_error_notification_enabled' name='ehive_access_options[ehive_api_error_notification_enabled]' type='checkbox' />";
		}
		
		function ehive_api_error_message_fn() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			echo "<textarea class='regular-text' id='ehive_api_error_message' name='ehive_access_options[ehive_api_error_message]' style='resize:none;' cols='40' rows='5' >{$options['ehive_api_error_message']}</textarea>";
			echo "<p><i>*This message is displayed when access to the eHive API fails.</i></p>";
		}
		
		//
		//	Admin menu setup
		// 
		function ehive_access_admin_menu() {
		
			global $ehive_access_options_page;
		
			$ehive_access_options_page = add_menu_page('eHive Base settings', 'eHive', 'manage_options', 'ehive_access', array(&$this, 'ehive_access_options_page'), plugin_dir_url( __FILE__ ).'images/eHiveMenu.ico' );
			                             
			add_submenu_page('EHive', 'eHive Access', 'Api Access', 'manage_options', 'EHive', array(&$this, 'ehive_access_options_page'));
		
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'ehive_access_plugin_action_links'), 10, 2);				
			
			add_action("admin_print_styles-" . $ehive_access_options_page, array(&$this, "ehive_access_admin_enqueue_styles") );
				
			add_action("load-$ehive_access_options_page",array(&$this, "ehive_access_options_help"));				
		}
		
		//
		//	Admin menu link
		//
		function ehive_access_plugin_action_links($links, $file) {
			$settings_link = '<a href="admin.php?page=ehive_access">' . __('Settings') . '</a>';
			array_unshift($links, $settings_link); // before other links
			return $links;
		}
	
		//
		//	Plugin options help setup
		//
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
	
		//
		//	Options page setup
		//
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

		//
		//	eHive plugin suite utility functions
		//
		public function eHiveApi() {

			//
			//	Store the oauthToken vendered by the API.
			//
			$oauthTokenCallback = function($oauthToken) {
			
				$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			
				$options['oauth_token'] = $oauthToken;
			
				update_option(self::EHIVE_ACCESS_OPTIONS, $options);								
			};
				
									
			require_once  plugin_dir_path(__FILE__).'ehive_api_client-php/EHiveApi.php';
			
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
						
			$oauthToken = '';
			if ( array_key_exists('oauth_token', $options)) {
				$oauthToken = $options['oauth_token'];
			}
			
			$memcachedServers = null;
			$memcacheExpiry = 300;
			if (isset($options['memcache_enabled']) && $options['memcache_enabled']== "on") {
				
				$memcachedServers = array();
				$hostsPorts = explode( ",", $options['memcached_servers'] );
								
				for ($i=0; $i<count($hostsPorts); $i++ ){

					$memcachedServers[] = $hostsPorts[$i];
				}	

				$memcacheExpiry = $options['memcache_expiry'];
			}
						
			$eHiveApi = new EHiveApi($options['client_id'], $options['client_secret'], $options['tracking_id'], $oauthToken,  $oauthTokenCallback, $memcachedServers, $memcacheExpiry);
			return $eHiveApi;
		}
		
		
		public function getSiteType() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			return $options[site_type];				
		}
		
		public function getAccountId() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			return $options[account_id];
		}
		
		public function getCommunityId() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			return $options[community_id];
		}
		
		public function getSearchPrivateRecords() {
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			return $options['private_record_search_enabled'] == 'on' ? true : false;
		}
				
		public function getAccountDetailsPageId(){
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			$pageId = $options['account_details_page'];
			return $pageId;
		}
		
		public function getIsErrorNotificationEnabled(){
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			$eHiveApiErrorNotificationEnabled = $options['ehive_api_error_notification_enabled'];
			return ($eHiveApiErrorNotificationEnabled == 'on' ? true : false);
		}
		
		public function getErrorMessage(){
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			$eHiveApiErrorMessage = $options['ehive_api_error_message'];
			return $eHiveApiErrorMessage;
		}
		
		public function getAccountDetailsPageLink( $accountId ){
			
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			
			$pageId = $options['account_details_page'];
		
			if ($pageId != 0) {
				return get_permalink( $pageId ).$accountId;
			} else {
				return '#';
			}
		}
		
		
		public function getObjectDetailsPageId(){
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);			
			$pageId = $options['object_details_page'];			
			return $pageId;
		}
		
		public function getObjectDetailsPageLink( $objectRecordId ){
			
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);

			$pageId = $options['object_details_page'];
		
			if ($pageId != 0) {
				return get_permalink( $pageId ).$objectRecordId;
			} else {
				return '#';
			}
		}
		
		public function getSearchPageId(){
			$options = get_option(self::EHIVE_ACCESS_OPTIONS);
			$pageId = $options['search_page'];
			return $pageId;
		}
		
		function getSearchPageLink( $args = '' ){

			$options = get_option(self::EHIVE_ACCESS_OPTIONS);

			$pageId = $options['search_page'];
		
			if ($pageId != 0) {		
				$link = get_permalink( $pageId );
				$link = rtrim($link, "/");
		
				return $link.$args;
			} else {
				return '#';
			}
		}
		
		
		//
		//	Setup the plugin options, handle upgrades to the plugin.
		//
		function ehive_plugin_update() {
		
			// Add the default options.
			if ( get_option(self::EHIVE_ACCESS_OPTIONS) === false ) {

				$options = array("update_version"=>self::CURRENT_VERSION,
							 	 "client_id"=>"",
							 	 "client_secret"=>"",
							 	 "oauth_token"=>"",
							 	 "tracking_id"=>"",
							 	 "private_record_search_enabled"=>"",
								 "memcache_expiry"=>300,								 
								 "memcache_enabled"=>"off",
								 "memcached_servers"=>"localhost:11211",								 
							 	 "ehive_api_error_notification_enabled" => '',
							 	 "ehive_api_error_message" => "Some content on this site is currently unavailable please try again later.");

				add_option(self::EHIVE_ACCESS_OPTIONS, $options);
		
			} else {
		
				$options = get_option(self::EHIVE_ACCESS_OPTIONS);
		
				if ( array_key_exists("update_version", $options)) {
					$updateVersion = $options["update_version"];
				} else {
					$updateVersion = 0;
				}
		
				if ( $updateVersion == self::CURRENT_VERSION ) {
				// Nothing to do.
				}  else {
		
					if ( $updateVersion == 0 ) {
				
						$options["memcache_enabled"] = "off";
						$options["memcache_expiry"] = 300;
						$options["memcached_servers"] = "localhost:11211";
				
						$updateVersion = 1;
					}
						
					// End of the update chain, save the options to the database.
					$options["update_version"] = self::CURRENT_VERSION;
					update_option(self::EHIVE_ACCESS_OPTIONS, $options);
				}
			}
		}
		
		//
		//	On plugin activate
		//
		public function activate() {
		}
		
		//
		//	On plugin deactivate
		//
		public function deactivate() {
		}				
	}
	
	include_once('functions.php');
	
	$eHiveAccess = new EHiveAccess();
	
	add_action('activate_ehive-access/EHiveAccess.php', array(&$eHiveAccess, 'activate'));
	add_action('deactivate_ehive-access/EHiveAccess.php', array(&$eHiveAccess, 'deactivate'));
}
?>