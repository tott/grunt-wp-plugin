<?php
/**
 * Plugin Name: {%= title %}
 * Plugin URI:  {%= homepage %}
 * Description: {%= description %}
 * Version:     0.1.0
 * Author:      {%= author_name %}
 * Author URI:  {%= author_url %}
 * License:     GPLv2+
 * Text Domain: {%= prefix %}
 * Domain Path: /languages
 */

/**
 * Copyright (c) {%= grunt.template.today('yyyy') %} {%= author_name %} (email : {%= author_email %})
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

class {%= class_name %} {

	private static $__instance = NULL;

	private $settings = array();
	private $default_settings = array();
	private $settings_texts = array();

	private $plugin_prefix = '{%= prefix %}';
	private $plugin_name = '{%= title %}';
	private $settings_page_name = null;
	private $dashed_name = '{%= name %}';
	private $underscored_name = '{%= js_safe_name %}';
	private $js_version = '{%= grunt.template.today('yymmddhhMMss') %}';
	private $css_version = '{%= grunt.template.today('yymmddhhMMss') %}';

	public function __construct() {
		add_action( 'admin_init', array( &$this, 'register_setting' ) );
		add_action( 'admin_menu', array( &$this, 'register_settings_page' ) );

		/**
		 * Default settings that will be used for the setup. You can alter these value with a simple filter such as this
		 * add_filter( 'pluginprefix_default_settings', 'mypluginprefix_settings' );
		 * function mypluginprefix_settings( $settings ) {
		 * 		$settings['enable'] = false;
		 * 		return $settings;
		 * }
		 */
		$this->default_settings = (array) apply_filters( $this->plugin_prefix . '_default_settings', array(
			'enable'				=> 1,
		) );

		/**
		 * Define fields that will be used on the options page
		 * the array key is the field_name the array then describes the label, description and type of the field. possible values for field types are 'text' and 'yesno' for a text field or input fields or 'echo' for a simple output
		 * a filter similar to the default settings (ie pluginprefix_settings_texts) can be used to alter this values
		 */
		$this->settings_texts = (array) apply_filters( $this->plugin_prefix . '_settings_texts', array(
			'enable' => array(
				'label' => sprintf( __( 'Enable %s', $this->plugin_prefix ), $this->plugin_name ),
				'desc' => sprintf( __( 'Enable %s', $this->plugin_prefix ), $this->plugin_name ),
				'type' => 'yesno'
			),
		) );

		$user_settings = get_option( $this->plugin_prefix . '_settings' );
		if ( false === $user_settings )
			$user_settings = array();

		// after getting default settings make sure to parse the arguments together with the user settings
		$this->settings = wp_parse_args( $user_settings, $this->default_settings );
	}

	public static function init() {
		self::instance()->settings_page_name = sprintf( __( '%s Settings', self::instance()->plugin_prefix ), self::instance()->plugin_name );

		if ( 1 == self::instance()->settings['enable'] ) {
			add_action( 'init', self::instance()->init_hook_enabled() );
		}
		self::instance()->init_hook_always();
	}

	/*
	 * Use this singleton to address methods
	 */
	public static function instance() {
		if ( self::$__instance == NULL )
			self::$__instance = new {%= class_name %};
		return self::$__instance;
	}

	/**
	 * Run these functions when the plugin is enabled
	 */
	public function init_hook_enabled() {

	}

	/**
	 * Run these functions all the time
	 */
	public function init_hook_always() {
		/**
		 * If a css file for this plugin exists in ./css/wp-cron-control.css make sure it's included
		 */
		if ( file_exists( dirname( __FILE__ ) . "/css/" . $this->underscored_name . ".css" ) )
			wp_enqueue_style( $this->dashed_name, plugins_url( "css/" . $this->underscored_name . ".css", __FILE__ ), $deps = array(), $this->css_version );
		/**
		 * If a js file for this plugin exists in ./js/wp-cron-control.css make sure it's included
		 */
		if ( file_exists( dirname( __FILE__ ) . "/js/" . $this->underscored_name . ".js" ) )
			wp_enqueue_script( $this->dashed_name, plugins_url( "js/" . $this->underscored_name . ".js", __FILE__ ), array(), $this->js_version, true );

		/**
		 * Locale setup
		 */
		$locale = apply_filters( 'plugin_locale', get_locale(), $this->plugin_prefix );
		load_textdomain( $this->plugin_prefix, WP_LANG_DIR . '/' . $this->plugin_prefix . '/' . $this->plugin_prefix . '-' . $locale . '.mo' );
		load_plugin_textdomain( $this->plugin_prefix, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

	public function register_settings_page() {
		add_options_page( $this->settings_page_name, $this->plugin_name, 'manage_options', $this->dashed_name, array( &$this, 'settings_page' ) );
	}

	public function register_setting() {
		register_setting( $this->plugin_prefix . '_settings', $this->plugin_prefix . '_settings', array( &$this, 'validate_settings') );
	}

	public function validate_settings( $settings ) {
		// reset to defaults
		if ( !empty( $_POST[ $this->dashed_name . '-defaults'] ) ) {
			$settings = $this->default_settings;
			$_REQUEST['_wp_http_referer'] = add_query_arg( 'defaults', 'true', $_REQUEST['_wp_http_referer'] );

		// or do some custom validations
		} else {

		}
		return $settings;
	}

	public function settings_page() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not permission to access this page', $this->plugin_prefix ) );
		}
		?>
		<div class="wrap">
		<?php if ( function_exists('screen_icon') ) screen_icon(); ?>
			<h2><?php echo $this->settings_page_name; ?></h2>

			<form method="post" action="options.php">

			<?php settings_fields( $this->plugin_prefix . '_settings' ); ?>

			<table class="form-table">
				<?php foreach( $this->settings as $setting => $value): ?>
				<tr valign="top">
					<th scope="row">
						<label for="<?php echo $this->dashed_name . '-' . $setting; ?>">
						<?php if ( isset( $this->settings_texts[$setting]['label'] ) ) {
							echo $this->settings_texts[$setting]['label'];
						} else {
							echo $setting;
						} ?>
						</label>
					</th>
					<td>
						<?php
						/**
						 * Implement various handlers for the different types of fields. This could be easily extended to allow for drop-down boxes, textareas and more
						 */
						?>
						<?php switch( $this->settings_texts[$setting]['type'] ):
							case 'yesno': ?>
								<select name="<?php echo $this->plugin_prefix; ?>_settings[<?php echo $setting; ?>]" id="<?php echo $this->dashed_name . '-' . $setting; ?>" class="postform">
									<?php
										$yesno = array( 0 => __( 'No', $this->plugin_prefix ), 1 => __( 'Yes', $this->plugin_prefix ) );
										foreach ( $yesno as $val => $txt ) {
											echo '<option value="' . esc_attr( $val ) . '"' . selected( $value, $val, false ) . '>' . esc_html( $txt ) . "&nbsp;</option>\n";
										}
									?>
								</select><br />
							<?php break;
							case 'text': ?>
								<div><input type="text" name="<?php echo $this->plugin_prefix; ?>_settings[<?php echo $setting; ?>]" id="<?php echo $this->dashed_name . '-' . $setting; ?>" class="postform" value="<?php echo esc_attr( $value ); ?>" /></div>
							<?php break;
							case 'echo': ?>
								<div><span id="<?php echo $this->dashed_name . '-' . $setting; ?>" class="postform"><?php echo esc_attr( $value ); ?></span></div>
							<?php break;
							default: ?>
								<?php echo $this->settings_texts[$setting]['type']; ?>
							<?php break;
						endswitch; ?>
						<?php if ( !empty( $this->settings_texts[$setting]['desc'] ) ) { echo $this->settings_texts[$setting]['desc']; } ?>
					</td>
				</tr>
				<?php endforeach; ?>
				<?php if ( 1 == $this->settings['enable'] ): ?>
					<tr>
						<td colspan="3">
							<p>The script has been enabled</p>
						</td>
					</tr>
				<?php endif; ?>
			</table>

			<p class="submit">
		<?php
				if ( function_exists( 'submit_button' ) ) {
					submit_button( null, 'primary', $this->dashed_name . '-submit', false );
					echo ' ';
					submit_button( __( 'Reset to Defaults', $this->plugin_prefix ), '', $this->dashed_name . '-defaults', false );
				} else {
					echo '<input type="submit" name="' . $this->dashed_name . '-submit" class="button-primary" value="' . __( 'Save Changes', $this->plugin_prefix ) . '" />' . "\n";
					echo '<input type="submit" name="' . $this->dashed_name . '-defaults" id="' . $this->dashed_name . '-defaults" class="button-primary" value="' . __( 'Reset to Defaults', $this->plugin_prefix ) . '" />' . "\n";
				}
		?>
			</p>

			</form>
		</div>

		<?php
	}
}

// if we loaded wp-config then ABSPATH is defined and we know the script was not called directly to issue a cli call
if ( defined('ABSPATH') ) {
	{%= class_name %}::init();
} else {
	// otherwise parse the arguments and call the cron.
	if ( !empty( $argv ) && $argv[0] == basename( __FILE__ ) || $argv[0] == __FILE__ ) {
		if ( isset( $argv[1] ) ) {
			echo "You could do something here";
		} else {
			echo "Usage: php " . __FILE__ . " <param1>\n";
			echo "Example: php " . __FILE__ . " superduperparameter\n";
			exit;
		}
	}
}

