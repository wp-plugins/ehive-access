<?php
	if ( !defined( 'WP_UNINSTALL_PLUGIN' )) {
		exit;
	}
	
	if ( get_option('ehive_access_options') != false ) {
		delete_option('ehive_access_options');
	}