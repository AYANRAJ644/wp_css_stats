<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://learn.skillcrush.com
 * @since      1.0.0
 *
 * @package    Css_Stats
 * @subpackage Css_Stats/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Css_Stats
 * @subpackage Css_Stats/admin
 * @author     Skillcrush Development <dev@skillcrush.com>
 */
class Css_Stats_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Css_Stats_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Css_Stats_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, CSS_STATS_PLUGIN_DIR . 'client/dist/admin.bundle.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Css_Stats_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Css_Stats_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name . '-common', CSS_STATS_PLUGIN_DIR . 'client/dist/commons.js', array(), $this->version, true );
		wp_enqueue_script( $this->plugin_name, CSS_STATS_PLUGIN_DIR . 'client/dist/admin.bundle.js', array(), $this->version, true );

		wp_localize_script( $this->plugin_name, 'css_stats', $this->get_vars());
				
	}

	/**
		* Adds the TXN Report item to the admin menu.
		*
		* @since 1.0.0
		*/
	public function add_admin_menu(){
			return add_menu_page(
					'CSS Stats',
					'CSS Stats',
					'manage_options',
					'css-stats',
					array(&$this, 'display_css_stats'),
					'dashicons-media-code',
					'50.4' // position order
			);
	}

	/**
		* Adds the TXN Report item to the admin menu.
		*
		* @since 1.1.0
		*/
	public function init_admin_menu(){
			$admin_hook = $this->add_admin_menu();
			add_action('admin_print_styles-' . $admin_hook, array($this, 'enqueue_styles'));
			add_action('admin_print_scripts-' . $admin_hook, array($this, 'enqueue_scripts'));
	}

	/**
		* Requires the partial file to render the download txn report page content.
		*
		* @since 1.0.0
		*/
	public function display_css_stats()
	{
			// this is the admin page markup
			require_once plugin_dir_path( __FILE__ ). 'partials/css-stats-admin-display.php';
	}

	private function glob_recursive($pattern, $flags = 0) {
		$files = glob($pattern, $flags);
		foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
			$files = array_merge($files, $this->glob_recursive($dir.'/'.basename($pattern), $flags));
		}
		return $files;
	}

	private function replace_directory_with_uri($files, $data = array()) {
		foreach ($files as $file) {
			array_push($data, str_replace(get_stylesheet_directory(), get_stylesheet_directory_uri(), $file));
		}
		return $data;
	}

	public function get_files() {
		if ( !wp_verify_nonce( $_POST['nonce'], "css_stats_nonce")) {
			exit("No naughty business please");
		}
		$filepath = $_POST['filepath'];
		update_option('css_stats_filepath', $filepath);
		$data = array();

		$files = $this->glob_recursive(get_stylesheet_directory() . '/' . $filepath);
		$data = $this->replace_directory_with_uri($files);
		wp_send_json_success(['files' => $data]);
	  die();
	}

	private function get_vars($filepath = false) {
		if (!$filepath) { $filepath = get_option('css_stats_filepath'); }
    $css_stats = array();
		$css_stats['data']['files'] = array();
    $files = $this->glob_recursive(get_stylesheet_directory() . '/' . $filepath);
		$css_stats['data']['files'] = $this->replace_directory_with_uri($files);
    $css_stats['data']['filepath'] = $filepath;
    $css_stats['ajaxurl'] = admin_url( 'admin-ajax.php' );
    $css_stats['nonce'] = wp_create_nonce('css_stats_nonce');
    $css_stats['action'] = 'get_files';
    return $css_stats;
	}
}
