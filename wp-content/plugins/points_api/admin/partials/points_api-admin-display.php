<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://author.com
 * @since      1.0.0
 *
 * @package    Points_api
 * @subpackage Points_api/admin/partials
 */

/**
 * Define plugin's Options Page Class.
 *
 * @since      1.0.0
 * @package    Points_api
 * @subpackage Points_api/includes
 * @author     Points API <points_api@example.com>
 */
class PointsAPI_SettingsPage {

	/**
	 * Options array.
	 *
	 * @var array $points_api_options
	 */
	private $points_api_options;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'points_api_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'points_api_page_init' ) );
	}

	/**
	 * Add plugin's page.
	 */
	public function points_api_add_plugin_page() {
		add_menu_page(
			__( 'Points API', 'points_api' ), // page_title.
			__( 'Points API', 'points_api' ), // menu_title.
			'manage_options', // capability.
			'points-api', // menu_slug.
			array( $this, 'points_api_create_admin_page' ), // function.
			'dashicons-admin-generic', // icon_url.
			100 // position.
		);
	}

	/**
	 * Create plugin's page form.
	 */
	public function points_api_create_admin_page() {
		$this->points_api_options = get_option( 'points_api_settings' ); ?>

		<div class="wrap">
			<h2>Points API</h2>
			<p>Points API Settings</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'points_api_option_group' );
				do_settings_sections( 'points-api-admin' );
				submit_button();
				?>
			</form>
		</div>
	<?php }

	/**
	 * Init plugin's page.
	 */
	public function points_api_page_init() {
		register_setting(
			'points_api_option_group', // option_group.
			'points_api_settings', // option_name.
			array( $this, 'points_api_sanitize' ) // sanitize_callback.
		);

		add_settings_section(
			'points_api_setting_section', // id.
			__( 'Settings', 'points_api' ), // title.
			array( $this, 'points_api_section_info' ), // callback.
			'points-api-admin' // page.
		);

		add_settings_field(
			'points_api_enabled', // id.
			__( 'Points API Enabled', 'points_api' ), // title.
			array( $this, 'points_api_enabled_callback' ), // callback.
			'points-api-admin', // page.
			'points_api_setting_section' // section.
		);

		add_settings_field(
			'signup_url', // id.
			__( 'Signup URL', 'points_api' ), // title.
			array( $this, 'signup_url_callback' ), // callback.
			'points-api-admin', // page.
			'points_api_setting_section' // section.
		);

		add_settings_field(
			'login_url', // id.
			__( 'Login URL', 'points_api' ), // title.
			array( $this, 'login_url_callback' ), // callback.
			'points-api-admin', // page.
			'points_api_setting_section' // section.
		);

		add_settings_field(
			'fetch_user_data_url', // id.
			__( 'Fetch User-data URL', 'points_api' ), // title.
			array( $this, 'fetch_user_data_url_callback' ), // callback.
			'points-api-admin', // page.
			'points_api_setting_section' // section.
		);

		add_settings_field(
			'api_timeout', // id.
			__( 'API Timeout', 'points_api' ), // title.
			array( $this, 'api_timeout_callback' ), // callback.
			'points-api-admin', // page.
			'points_api_setting_section' // section.
		);
	}

	/**
	 * Sanitize inputs.
	 *
	 * @param array $input  Input data array.
	 *
	 * @return array
	 */
	public function points_api_sanitize( $input ) {
		$sanitary_values = array();
		if ( isset( $input['points_api_enabled'] ) ) {
			$sanitary_values['points_api_enabled'] = $input['points_api_enabled'];
		}

		if ( isset( $input['signup_url'] ) ) {
			$sanitary_values['signup_url'] = sanitize_text_field( $input['signup_url'] );
		}

		if ( isset( $input['login_url'] ) ) {
			$sanitary_values['login_url'] = sanitize_text_field( $input['login_url'] );
		}

		if ( isset( $input['fetch_user_data_url'] ) ) {
			$sanitary_values['fetch_user_data_url'] = sanitize_text_field( $input['fetch_user_data_url'] );
		}

		if ( isset( $input['api_timeout'] ) ) {
			$sanitary_values['api_timeout'] = absint( intval( $input['api_timeout'] ) );
		}
		return $sanitary_values;
	}

	/**
	 * Settings page info.
	 */
	public function points_api_section_info() {

	}

	/**
	 * Item HTML callback.
	 */
	public function points_api_enabled_callback() {
		$value = ( isset( $this->points_api_options['points_api_enabled'] ) && $this->points_api_options['points_api_enabled'] === 'points_api_enabled' ) ? 'checked' : ''; ?>
		<input type="checkbox" name="points_api_settings[points_api_enabled]"
		       id="points_api_enabled"
		       value="points_api_enabled" <?php echo esc_attr( $value ); ?>>
		<label for="points_api_enabled"><?php esc_html_e( 'Check this to use Points API plugin features', 'points_api' ); ?></label>
	<?php
	}

	/**
	 * Item HTML callback.
	 */
	public function signup_url_callback() {
		$value = isset( $this->points_api_options['signup_url'] )
			? esc_attr( $this->points_api_options['signup_url'] ) : ''; ?>
		<input type="text" name="points_api_settings[signup_url]"
		       id="signup_url" value="<?php echo esc_attr( $value ); ?>" size="80" />
	<?php
	}

	/**
	 * Item HTML callback.
	 */
	public function login_url_callback() {
		$value = isset( $this->points_api_options['login_url'] )
			? esc_attr( $this->points_api_options['login_url'] ) : ''; ?>
		<input type="text" name="points_api_settings[login_url]" id="login_url"
		       value="<?php echo esc_attr( $value ); ?>" size="80" />
	<?php
	}

	/**
	 * Item HTML callback.
	 */
	public function fetch_user_data_url_callback() {
		$value = isset( $this->points_api_options['fetch_user_data_url'] )
			? esc_attr( $this->points_api_options['fetch_user_data_url'] )
			: ''; ?>
		<input type="text" name="points_api_settings[fetch_user_data_url]"
		       id="fetch_user_data_url" value="<?php echo esc_attr( $value ); ?>"
		       size="80" />
	<?php
	}

	/**
	 * Item HTML callback.
	 */
	public function api_timeout_callback() {
		$value = isset( $this->points_api_options['api_timeout'] )
			? esc_attr( $this->points_api_options['api_timeout'] )
			: '5'; ?>
		<input type="number" min="1" step="1" name="points_api_settings[api_timeout]"
		       id="api_timeout" value="<?php echo esc_attr( $value ); ?>" />
		<label for="api_timeout"><?php esc_html_e( ', seconds', 'points_api' ); ?></label>
	<?php
	}
}

if ( is_admin() ) {
	$points_api = new PointsAPI_SettingsPage();
}
