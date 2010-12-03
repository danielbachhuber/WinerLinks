<?php
/*
Plugin Name: Winerlinks
Plugin URI: http://danielbachhuber.com/projects/winerlinks/
Description: Paragraph-level permalinks. See them in use at <a href="http://scripting.com/">http://scripting.com</a>, <a href="http://pressthink.org/">http://pressthink.org</a>, or any <a href="http://nytimes.com/">http://nytimes.com</a> article
Author: Daniel Bachhuber
Version: 0.2.1
Author URI: http://danielbachhuber.com/
*/

define( 'WINERLINKS_FILE_PATH', __FILE__ );
define( 'WINERLINKS_URL', plugins_url(plugin_basename(dirname(__FILE__)) .'/') );
define( 'WINERLINKS_VERSION', '0.2.1' );

if ( !class_exists('winerlinks') ) {

class winerlinks {
	
	var $options_group = 'winerlinks_';
	var $options_group_name = 'winerlinks_options';
	var $settings_page = 'winerlinks_settings';
	var $winerlink_character = '#';
	
	function __construct() {

	}
	
	/**
	 * What we do when WordPress is initialized
	 */ 
	function init() {

		$this->options = get_option( $this->options_group_name );
		
		if ( is_admin() ) {
			add_action( 'admin_menu', array(&$this, 'add_admin_menu_items') );
		} else {
			// Only add Winerlinks if it's enabled
			if ( $this->options['enabled'] ) {
				add_filter( 'the_content', array(&$this, 'filter_the_content') );
				add_filter( 'the_content_feed', array(&$this, 'filter_the_content') );
				
				// Only enqueue the stylesheet if showy-hidey mode is enabled
				if ( $this->options['showhide'] ) {
					wp_enqueue_style( 'winerlinks-css', WINERLINKS_URL . 'css/winerlinks.css', false, WINERLINKS_VERSION );
				}
				
				// We always need the Javascript for highlighting
				wp_enqueue_script( 'winerlinks-js', WINERLINKS_URL . 'js/winerlinks.js', array( 'jquery' ), WINERLINKS_VERSION, true );
				
			}
		}
		
	}
	
	/**
	 * What we do when the admin is initialized
	 */
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
			$options['showhide'] = 0;
			update_option( $this->options_group_name, $options );
		}
	}
	
	/**
	 * Any admin menu items we need
	 */
	function add_admin_menu_items() {
		
		add_submenu_page( 'options-general.php', 'Winerlinks Settings', 'Winerlinks', 'manage_options', 'winerlinks', array( &$this, 'settings_page' ) );			
		
	}
	
	/**
	 * Register all Winerlinks settings
	 */
	function register_settings() {
		
		register_setting( $this->options_group, $this->options_group_name, array( &$this, 'settings_validate' ) );
		
		add_settings_section( 'winerlinks_default', 'Settings', array(&$this, 'settings_section'), $this->settings_page );
		add_settings_field( 'enabled', 'Enable Winerlinks', array(&$this, 'settings_enabled_option'), $this->settings_page, 'winerlinks_default' );
		add_settings_field( 'showhide', 'Magical showy-hidey mode', array(&$this, 'settings_showhide_option'), $this->settings_page, 'winerlinks_default' );
		
	}
	
	/**
	 * The Winerlinks settings page for all of its settings glory
	 */
	function settings_page() {

		?>                                   
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br/></div>

			<h2><?php _e('Winerlinks', 'winerlinks') ?></h2>

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
	 * Setting for whether Winerlinks are enabled or not
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
	 * Setting for magical showy-hidey mode
	 */
	function settings_showhide_option() {
		$options = $this->options;
		echo '<select id="enabled" name="' . $this->options_group_name . '[showhide]">';
		echo '<option value="0">Disabled</option>';
		echo '<option value="1"';
		if ( $options['showhide'] == 1 ) { echo ' selected="selected"'; }
		echo '>Enabled</option>';	
		echo '</select>';
		echo '<p class="description">Enable magical showy-hidey mode to have your Winerlinks appear on hover';
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
		
		// Users can customize the WinerLink character with a filter
		$this->winerlink_character = strip_tags( apply_filters( 'winerlink_character', $this->winerlink_character ) );
		
		if ( ( is_single() && $options['enabled'] == 1 ) || ( is_page() && $options['enabled'] == 2 ) || ( ( is_page() || is_single() ) && $options['enabled'] == 3 ) || ( is_feed() && ( $options['enabled'] == 1 || $options['enabled'] == 3 ) ) ) {
		
			$new_content = '';
			$content_by_paragraph = preg_split( '/<\/p>/is', $the_content );
			foreach ( $content_by_paragraph as $key => $paragraph ) {
				$paragraph = rtrim( $paragraph );
				// Check to make sure it actually has text and that it hasn't already had links added. Else, append the graf
				if ( $paragraph && !strpos( $paragraph, 'winerlinks-enabled' ) ) {
					// Need to wrap our replacements in new p tags so it validates
					$paragraph = preg_replace( '/<p>/is', '', $paragraph );
					// Prepend the graf with an anchor tag
					$new_content .= '<p class="winerlinks-enabled"><a name="p' . $key . '"></a>';
					// Add the link at the end of the graf
					$new_content .= $paragraph . ' <a ref="permalink" title="Permalink to this paragraph" class="winerlink" href="'. get_permalink( $post->ID ) . '#p' . $key . '">' . $this->winerlink_character . '</a></p>';
				} else {
					$new_content .= $paragraph;
				}
			}
			return $new_content;
			
		} else {
			return $the_content;
		}
		
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