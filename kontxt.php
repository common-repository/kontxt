<?php
/**
 *
 * @link              https://kontxt.io
 * @package           Kontxt
 *
 * @wordpress-plugin
 * Plugin Name:       Kontxt - Inline Engagement System: Highlights, Comments, Polls, Sharing
 * Plugin URI:        https://kontxt.io/
 * Description:       Kontxt helps publishers increase engagement and build loyal audiences. Adds highlights, comments, polls, @mentions, and deep linking to page parts for efficient viewing, with granular sharing permissions.
 * Version:           1.0.0
 * Author:            Kontxt
 * Author URI:        https://kontxt.io/company
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       kontxt
 */

if (! defined( 'ABSPATH')){ 
	die; 
}

class Kontxt{

	public $plugin;

	function __construct(/*$arg*/){
		// echo $arg;
		$this->plugin = plugin_basename(__FILE__);
	}

	function register(){
		add_action('wp_enqueue_scripts', array($this, 'enqueue'));

		add_action('admin_menu', array($this, 'add_admin_pages'));

		add_filter("plugin_action_links_$this->plugin", array($this, 'settings_link'));

		add_action('admin_init', array($this, 'kontxt_custom_settings'));

		add_action('admin_post_kontxt_settings_form', array($this, 'redirect_to_kontxt_settings_form'));
	}


	public function redirect_to_kontxt_settings_form(){

		if ( ! empty( $_POST ) && check_admin_referer( 'kontxt_settings_update', 'kontxt_form' ) ) {
			$options = get_option('kontxt_include_location', ['type' => null, 'text' => null]);

			$locations = sanitize_text_field($_POST['LOCATIONS']);
			$contains_text = sanitize_text_field($_POST['TEXT_INPUT'][0]);
			$regex_text = sanitize_text_field($_POST['TEXT_INPUT'][1]);
			$text_input = !empty($contains_text) ? $contains_text : $regex_text;

			$valid_locations = array('NONE','POSTS','PAGES','POSTS_AND_PAGES', 'CONTAINS', 'REGEX');
			$is_valid_locations = in_array($locations, $valid_locations) ? true : false;

			if(!$is_valid_locations){
				$locations = 'NONE';
				$text_input = null;
			}

			if(isset($locations)){
				$data = ['type' => $locations, 'text' => trim($text_input)];

			  if(isset($options)){
				update_option('kontxt_include_location', $data);
			  }else{
			  	add_option('kontxt_include_location', $data);
			  }

				add_settings_error( 'general', 'settings_updated', __( 'Settings saved.' ), 'success' );
				set_transient( 'settings_errors', get_settings_errors(), 30 );
			}
		}

		$goback = add_query_arg( 'settings-updated', 'true', wp_get_referer() );
		wp_redirect( $goback );
	}


	public function kontxt_custom_settings(){
		register_setting('kontxt-settings-group', 'kontxt_include_location'); //last arg: callback - for sanitization
	}

	public function settings_link($links){
		$settings_link = '<a href="admin.php?page=kontxt_plugin">Settings</a>';
		array_push($links, $settings_link);
		return $links;
	}

	public function add_admin_pages(){
		//dashicons-store
		//plugins_url('/assets/favicon.ico', __FILE__)
		add_menu_page('Kontxt Plugin', 'Kontxt', 'manage_options', 'kontxt_plugin', array($this, 'admin_index'), 'dashicons-store', 110);
	}

	public function admin_index(){
		require_once plugin_dir_path(__FILE__).'templates/admin.php';
	}

	function activate(){
		$options = get_option('kontxt_include_location', ['type' => null, 'text' => null]);

		if(!isset($options['type'])){
			$data = ['type' => 'NONE', 'text' => null];
			add_option('kontxt_include_location', $data);
		}
	}

	function deactivate(){

	}

	public static function uninstall(){
		delete_option('kontxt_include_location');
	}

	function enqueue(){
		$src = 'https://www.kontxt.io/content.js';

		$options = get_option('kontxt_include_location', ['type' => null, 'text' => null]);

		$location_type = $options['type'];
		$location_text = $options['text'];

		if(isset($location_type) && $location_type !== 'NONE'){
			global $wp;
			$curr_url = esc_url(home_url(add_query_arg(array($_GET), $wp->request)));

			$is_page = is_page();
			$is_post = is_singular('post');

			$is_valid_page = $is_page && ($location_type === 'PAGES' || $location_type === 'POSTS_AND_PAGES');
			$is_valid_post = $is_post && ($location_type === 'POSTS' || $location_type === 'POSTS_AND_PAGES');
			$is_valid_contains = $location_type === 'CONTAINS' && $location_text && strpos($curr_url, $location_text) !== false;
			$is_valid_regex = null;

			try{
				$is_valid_regex  = $location_type === 'REGEX' && $location_text && preg_match($location_text, $curr_url);
			}catch(Exception $e){
				$is_valid = false;
			}
			
			$is_valid = $is_valid_page || $is_valid_post || $is_valid_contains || $is_valid_regex;
			

			if($is_valid){
				wp_enqueue_script('kontxt_script', $src, NULL, NULL, false);
			}

		}
	}
}


if(class_exists('Kontxt')){
	// TODO: Add regex to check current page url to see if should be added
	$kontxt = new Kontxt(/*REGEX*/);
	$kontxt->register();
}


// activation
register_activation_hook(__FILE__, array($kontxt, 'activate'));

// deactivation
register_deactivation_hook(__FILE__, array($kontxt, 'deactivate'));

// uninstall can be made in uninstall.php
// uninstall
register_uninstall_hook(__FILE__, 'Kontxt::uninstall');
