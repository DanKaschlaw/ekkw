<?php

/*
Plugin Name: WP Fulltext Search
Description: Implementing a true indexed full-text search over wordpress posts and metas without using any external indexing software.
Version: 1.2.3
Author: Epsiloncool
Author URI: http://e-wm.org
License: GPL3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: wpfts_lang
Domain Path: /languages/
*/

/*  Copyright 2013-2015 Epsiloncool
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  @copyright 2013-2015
 *  @license GPL v3
 *  @version 1.2.3
 *  @package Wordpress Fulltext Search
 *  @author Epsiloncool
 */

define('WPFTS_VERSION', '1.2.3');

require_once dirname(__FILE__).'/includes/wpfts_core.php';
require_once dirname(__FILE__).'/includes/wpfts_output.php';

$wpfts_core = new WPFTS_Core();

register_activation_hook(__FILE__, array(&$wpfts_core, 'activate_plugin'));
register_deactivation_hook(__FILE__, array(&$wpfts_core, 'deactivate_plugin'));

add_action('init', 'wpfts_init');
function wpfts_init() {
	
	global $wpfts_core;

	if ((is_object($wpfts_core)) && (is_callable(array($wpfts_core, 'set_hooks')))) {
	
		$wpfts_core->set_hooks();

		if (is_admin()) {
			add_action('admin_menu', 'wpfts_admin_menu');
			add_filter('plugin_row_meta', 'wpfts_plugin_links', 10, 2);
		
			// Initial rebuild
			if (intval($wpfts_core->get_option('rebuild_time')) < 1) {
				$wpfts_core->rebuild_index();
			}
		
			load_plugin_textdomain( 'wpfts_lang', false, basename(dirname(__FILE__)).'/languages/');
		
			add_action('wp_ajax_wpftsi_submit_settings', array($wpfts_core, 'ajax_submit_settings'));
			add_action('wp_ajax_wpftsi_submit_settings2', array($wpfts_core, 'ajax_submit_settings2'));
			add_action('wp_ajax_wpftsi_rebuild_step', array($wpfts_core, 'ajax_rebuild_step'));
			add_action('wp_ajax_wpftsi_ping', array($wpfts_core, 'ajax_ping'));
			add_action('wp_ajax_wpftsi_submit_testpost', array($wpfts_core, 'ajax_submit_testpost'));
			add_action('wp_ajax_wpftsi_submit_testsearch', array($wpfts_core, 'ajax_submit_testsearch'));
			add_action('wp_ajax_wpftsi_submit_rebuild', array($wpfts_core, 'ajax_submit_rebuild'));
		}
	}
}

function wpfts_custom_js() {
	
	global $wpfts_core;
	
	?><script type="text/javascript">
		var wpfts_pid = "<?php echo $wpfts_core->getPid(); ?>";
		var wpfts_pingtimeout = <?php echo intval($wpfts_core->get_option('ping_period')) * 1000; ?>;
		var wpfts_root_url = "<?php echo $wpfts_core->root_url; ?>";
	</script><?php
}
add_action('admin_head', 'wpfts_custom_js');

add_action('plugins_loaded', 'wpfts_load_plugin_textdomain');
function wpfts_load_plugin_textdomain() {
	load_plugin_textdomain( 'wpfts_lang', false, dirname(plugin_basename(__FILE__)).'/languages/');
}

function wpfts_plugin_links($links, $file) {
	if (basename($file) == basename(__FILE__)) {
		$links[] = '<a href="options-general.php?page=wpfts-options">'.__('Settings', 'wpfts_lang').'</a>';
	}
	return $links;
}

function wpfts_admin_menu() {
	$ime_page = add_options_page('WP FullText Search', 'WP FullText Search', 'manage_options', 'wpfts-options', 'wpfts_option_page');

	add_filter('plugin_action_links', 'wpfts_settings_link', 10, 2);
	add_action('admin_enqueue_scripts', 'wpfts_enqueues');
}

function wpfts_enqueues() {
	$version = (defined('WP_DEBUG') && WP_DEBUG) ? time() : WPFTS_VERSION;
	$screen = get_current_screen();
	if (!is_null($screen) && $screen->id === 'settings_page_wpfts-options') {

		wp_enqueue_style('wpfts_style', plugins_url('style/wpfts_main.css', __FILE__), array(), $version);
		wp_enqueue_script('wpfts_script', plugins_url('js/wpfts_script.js', __FILE__), array(), $version);
		
		wp_enqueue_style('wp-pointer');

		wp_enqueue_script('postbox');
		wp_enqueue_script('wp-pointer');
	}
}

function wpfts_settings_link($links, $file) {
	$this_plugin = dirname(plugin_basename(dirname(__FILE__))) . '/fulltext-search.php';
	if ($file == $this_plugin) {
		$links[] = '<a href="options-general.php?page=wpfts-options">' . __('Settings', 'wpfts_lang' ) . '</a>';
	}
	return $links;
}

function wpfts_option_page() {

	if (!current_user_can('manage_options'))
		wp_die('Sorry, but you do not have permissions to change settings.');

	/* Make sure post was from this page */
	if (isset($_POST) && (count($_POST) > 0)) {
		check_admin_referer('wpfts-options');
	}

	add_action('add_meta_boxes_wpfts-options', 'add_meta_boxes_cb');
	do_action('add_meta_boxes_wpfts-options');
	
	require dirname(__FILE__).'/includes/templates/admin_options.php';
}

register_activation_hook(__FILE__, 'wpfts_activate_plugin');

function wpfts_activate_plugin() {
	if(!function_exists('register_post_status')) {
		deactivate_plugins(basename(dirname( __FILE__ )).'/'.basename (__FILE__));
		wp_die( __( "This plugin requires WordPress 3.0 or newer. Please update your WordPress installation to activate this plugin.", 'wpfts_lang' ));
	}
}


function add_meta_boxes_cb($post) {
	
	$out = new WPFTS_Output();
	
	// Side
	add_meta_box( 
        'wpfts_status_box',
        __( 'Search Engine Status', 'wpfts_lang' ),
        array(&$out, 'status_box'),
        'wpfts-options',
        'side',
        'core'
    );
	// Side2
	add_meta_box( 
        'wpfts_useful_box',
        __( 'Useful Information', 'wpfts_lang' ),
        array(&$out, 'useful_box'),
        'wpfts-options',
        'side',
        'core'
    );
	// Main
	add_meta_box( 
        'wpfts_control_box',
        __( 'Main Configuration', 'wpfts_lang' ),
        array(&$out, 'control_box'),
        'wpfts-options',
        'normal1',
        'core'
    );
	add_meta_box( 
        'wpfts_relevance_box',
        __( 'Search and Relevance Settings', 'wpfts_lang' ),
        array(&$out, 'relevance_box'),
        'wpfts-options',
        'normal1',
        'core'
    );
	// Relevance
	add_meta_box( 
        'wpfts_indexing_box',
        __( 'Indexing Engine Settings', 'wpfts_lang' ),
        array(&$out, 'indexing_box'),
        'wpfts-options',
        'normal2',
        'core'
    );
	add_meta_box( 
        'wpfts_sandbox_box',
        __( 'Sandbox Area', 'wpfts_lang' ),
        array(&$out, 'sandbox_box'),
        'wpfts-options',
        'normal3',
        'core'
    );	
}

/**
 * Called when any post/page/etc updated or created
 * 
 * We need to reindex the post by this action
 */
function wpfts_save_post_action($post_id){
/*
	if (wp_is_post_revision($post_id)) {
		return;
	}
*/	
	wpfts_post_reindex($post_id);
}
add_action('save_post', 'wpfts_save_post_action', 99);

/**
 * Called when any post/page/etc was deleted
 * 
 * We need to delete the post from the index by this action
 */
function wpfts_deleted_post_action($post_id){
	
	wpfts_post_reindex($post_id);
}
add_action('after_delete_post', 'wpfts_deleted_post_action', 99);

function wpfts_post_reindex($post_id) {
	
	global $wpfts_core;
	
	$res = $wpfts_core->reindex_post($post_id);
	
	if (!$res) {
		trigger_error('Error reindex post ID='.$post_id.': '.$wpfts_core->index_error, E_USER_NOTICE);
		return false;
	}
	
	return true;
}



