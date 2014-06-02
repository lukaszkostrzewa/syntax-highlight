<?php
/**
 * Plugin Name: Syntax Highlight
 * Plugin URI: http://wordpress.org/extend/plugins/syntax-highlight/
 * Description: Syntax Highlighting in WordPress Plugins and Themes Editor
 * Version: 1.0
 * Author: Lukasz Kostrzewa
 * Author URI: 
 * License: GPL2
 * Text Domain: syntax-highlight
 * Domain Path: /languages/
 */

/*  Copyright 2014  Lukasz Kostrzewa  (email : lukasz.webmaster@gmail.com)

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

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Only when accesing admin page
if ( !class_exists( 'SyntaxHighlight' ) and is_admin()) :

class SyntaxHighlight {

	/** Singleton *********************************************************/

	/**
	 * Main SyntaxHighlight Instance.
	 *
	 * Insures that only one instance of SyntaxHighlight exists in memory at any
	 * one time. Also prevents needing to define globals all over the place.
	 *
	 * @since SyntaxHighlight (1.0)
	 *
	 * @static object $instance
	 * @uses SyntaxHighlight::setup_globals() Setup the globals needed.
	 * @uses SyntaxHighlight::includes() Include the required files.
	 * @uses SyntaxHighlight::setup_actions() Setup the hooks and actions.
	 * @see syntaxhigh()
	 *
	 * @return SyntaxHighlight The one true SyntaxHighlight.
	 */
	public static function instance() {

		// Store the instance locally to avoid private static replication
		static $instance = null;

		// Only run these methods if they haven't been run previously
		if ( null === $instance ) {
			$instance = new SyntaxHighlight;
			$instance->setup_globals();
			$instance->includes();
			$instance->setup_actions();
		}

		// Always return the instance
		return $instance;
	}

	/**
	 * A dummy constructor to prevent SyntaxHighlight from being loaded more than once.
	 *
	 * @since SyntaxHighlight (1.0)
	 * @see SyntaxHighlight::instance()
	 * @see syntaxhigh()
	 */
	private function __construct() { /* Do nothing here */ }


	/**
	 * Component global variables.
	 *
	 * @since SyntaxHighlight (1.0)
	 * @access private
	 */
	private function setup_globals() {

		/** Versions **************************************************/

		$this->version    	= '1.0';

		/** Paths******************************************************/

		// SyntaxHighlight root directory
		$this->file 		= __FILE__;
		$this->basename 	= plugin_basename( $this->file );
		$this->plugin_dir 	= plugin_dir_path( __FILE__ );
		
		// Language directory
		$this->lang_dir 	= basename( dirname( $this->file ) ) . '/languages';

		$this->js_handle   	= 'sh-js';
	}
	
	/**
	 * Include required files.
	 *
	 * @since SyntaxHighlight (1.0)
	 * @access private
	 */
	private function includes() {

		require( $this->plugin_dir . 'class-sh-settings.php');
	}

	/**
	 * Set up the default hooks and actions.
	 *
	 * @since SyntaxHighlight (1.0)
	 * @access private
	 *
	 * @uses register_activation_hook() To register the activation hook.
	 * @uses register_deactivation_hook() To register the deactivation hook.
	 * @uses add_action() To add various actions.
	 */
	private function setup_actions() {

		// Load localization
		add_action( 'plugins_loaded', 	array( $this, 'plugins_loaded' ) );

		// Add action only if on Editor page
		if ( !$this->is_editor() ) {
			return;
		}

		// Load scripts
		add_action( 'admin_init', 		array( $this, 'admin_init') );
	}

	private function is_editor(){
		if ( !strstr($_SERVER['SCRIPT_NAME'],'plugin-editor.php') && 
			 !strstr($_SERVER['SCRIPT_NAME'],'theme-editor.php' ) ) {
			return false;
		}
		return true;
	}

	public function admin_init() {

		// Load ACE
		wp_enqueue_script( 'sh-ace', 				plugins_url( 'lib/src-min-noconflict/ace.js', 			__FILE__ ) );	
		wp_enqueue_script( 'sh-ace-ext-modelist', 	plugins_url( 'lib/src-min-noconflict/ext-modelist.js',	__FILE__ ) );	

		// Load SyntaxHighlight JavaScript and CSS file
		wp_enqueue_script( $this->js_handle, 		plugins_url( 'syntax-highlight.js', __FILE__ ), array(), syntaxhigh()->version, true );
		wp_enqueue_style( 'sh-css', 				plugins_url( 'syntax-highlight.css', __FILE__ ) );

		// Load settings into SyntaxHighlight JavaScript file
		$sh_settings = get_option( $this->settings->option_name );
		$sh_settings = array_merge( array( 
			'unsaved_changes_txt' => __('Some changes have not been saved.', 'syntax-highlight') 
			), $sh_settings);
		wp_localize_script( $this->js_handle, 'shSettings', $sh_settings);
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @since SyntaxHighlight (1.0)
	 */
	function plugins_loaded() {
  		load_plugin_textdomain( 'syntax-highlight', false, $this->lang_dir ); 
	}
}

/**
 * The main function responsible for returning the one true SyntaxHighlight Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $sh = syntaxhigh(); ?>
 *
 * @return SyntaxHighlight The one true SyntaxHighlight Instance.
 */
function syntaxhigh() {
	return SyntaxHighlight::instance();
}

$GLOBALS['sh'] = syntaxhigh();

endif;
