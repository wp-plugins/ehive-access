<?php
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

function ehive_get_var($name, $default = false) {
    global $wp_query;
    return isset($wp_query->query_vars) && isset($wp_query->query_vars[$name]) ? $wp_query->query_vars[$name] : (isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default);
}

function ehive_set_var($name, $value) {
	global $wp_query;
	if (isset($wp_query->query_vars)) {
		$wp_query->query_vars[$name] = $value;
	} else {
		$_REQUEST[$name] = $value;
	}
}

function ehive_current_url() {
	return ($_SERVER["HTTPS"] == "on" ? 'https' : 'http') . "://" . $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
}

function ehive_link2($name) {
	switch ($name) {
		case 'Search':
			global $eHiveSearch;
			return get_permalink($eHiveSearch->options['page_id']);

		case 'ObjectDetails':
			global $eHiveObjectDetails;
			return get_permalink($eHiveObjectDetails->options['page_id']);

		default:
			return '#';
	}
}
/*
function url_encode($string) {
	return urlencode(utf8_encode($string));
}
*/
?>