<?php
/*
Plugin Name: WinerLinks
Plugin URI: http://www.danielbachhuber.com/projects/winerlinks/
Description: Paragraph-level permalinks
Author: Daniel Bachhuber
Version: 0.1
Author URI: http://www.danielbachhuber.com/
*/

define('WINERLINKS_FILE_PATH', __FILE__);

if ( !class_exists('winerlinks') ) {

class winerlinks {
	
	var $options_group = 'winerlinks_';
	var $options_group_name = 'winerlinks_options';
	var $settings_page = 'winerlinks_settings';	
	
	function __construct() {

	}
	
	function init() {

		$this->options = get_option( $this->options_group_name );
		
		if ( is_admin() ) {
			add_action( 'admin_menu', array(&$this, 'add_admin_menu_items') );
		} else {
			if ( $this->options['enabled'] ) {
				add_filter( 'the_content', array(&$this, 'filter_the_content') );
			}
		}
		
	}
	
	function admin_init() {
		
		$this->register_settings();
		
	}
	
	/**
	 * Default settings for when the plugin is activated for the first time
	 */ 
	function activate_plugin() {
		$options = $this->options;
		if ( $options['activated_once'] != 'on' ) {
			$options['activated_once'] = 'on';
			$options['enabled'] = 0;
			update_option( $this->options_group_name, $options );
		}
	}
	
	/**
	 * Any admin menu items we need
	 */
	function add_admin_menu_items() {
		
		add_submenu_page( 'options-general.php', 'WinerLinks Settings', 'WinerLinks', 'manage_options', 'winerlinks', array( &$this, 'settings_page' ) );			
		
	}
	
	/**
	 * Register all WinerLinks settings
	 */
	function register_settings() {
		
		register_setting( $this->options_group, $this->options_group_name, array( &$this, 'settings_validate' ) );
		
		add_settings_section( 'winerlinks_default', 'Settings', array(&$this, 'settings_section'), $this->settings_page );
		add_settings_field( 'enabled', 'Enable WinerLinks', array(&$this, 'settings_enabled_option'), $this->settings_page, 'winerlinks_default' );	
		
	}
	
	function settings_page() {

		?>                                   
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br/></div>

			<h2><?php _e('WinerLinks', 'winerlinks') ?></h2>

			<form action="options.php" method="post">

				<?php settings_fields( $this->options_group ); ?>
				<?php do_settings_sections( $this->settings_page ); ?>

				<p class="submit"><input name="submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" /></p>

			</form>
		</div>

	<?php
		
	}
	
	/**
	 * Empty method because we need a callback apparently
	 */
	function settings_section() {
		
	}
	
	/**
	 * Setting for whether WinerLinks are enabled or not
	 */
	function settings_enabled_option() {
		$options = $this->options;
		echo '<select id="enabled" name="' . $this->options_group_name . '[enabled]">';
		echo '<option value="0">Disabled</option>';
		echo '<option value="1"';
		if ( $options['enabled'] == 1 ) { echo ' selected="selected"'; }
		echo '>Enable on posts</option>';
		echo '<option value="2"';
		if ( $options['enabled'] == 2 ) { echo ' selected="selected"'; }
		echo '>Enable on pages</option>';
		echo '<option value="3"';
		if ( $options['enabled'] == 3 ) { echo ' selected="selected"'; }
		echo '>Enable on both</option>';		
		echo '</select>';
	}
	
	/**
	 * Validation and sanitization on the settings field
	 */
	function settings_validate( $input ) {
		
		// Sanitize input for display_configuration
		return $input;
		
	}
	
	/**
	 * Add your paragraph-level permalinks to a post or page
	 * @return string $the_content The modified content
	 */
	function filter_the_content( $the_content ) {
		global $post;
		$options = $this->options;
		
		return $the_content;
		
	}
	
} // END: class winerlinks

global $winerlinks;
$winerlinks = new winerlinks();

// Core hooks to initialize the plugin
add_action( 'init', array( &$winerlinks, 'init' ) );
add_action( 'admin_init', array( &$winerlinks, 'admin_init' ) );

// Hook to perform action when plugin activated
register_activation_hook( WINERLINKS_FILE_PATH, array(&$winerlinks, 'activate_plugin') );

}

?>