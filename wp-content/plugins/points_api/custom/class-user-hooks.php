<?php

/**
 * Define custom User Hooks Class.
 *
 * @link       http://author.com
 * @since      1.0.0
 *
 * @package    Points_api
 * @subpackage Points_api/includes
 */

/**
 * Define custom User Hooks Class.
 *
 * @since      1.0.0
 * @package    Points_api
 * @subpackage Points_api/includes
 * @author     Points API <points_api@example.com>
 */
class UserHooks {

	public static function callback_register_form() {
		$first_name = ( ! empty( $_POST['first_name'] ) ) ? trim( $_POST['first_name'] ) : '';
		$last_name = ( ! empty( $_POST['last_name'] ) ) ? trim( $_POST['last_name'] ) : ''; ?>
		<p>
			<label for="first_name"><?php _e( 'First Name', 'points_api' ) ?><br />
				<input type="text" name="first_name" id="first_name" class="input" value="<?php echo esc_attr( wp_unslash( $first_name ) ); ?>" size="25" />
			</label>
		</p>
		<p>
			<label for="first_name"><?php _e( 'Last Name', 'points_api' ) ?><br />
				<input type="text" name="last_name" id="last_name" class="input" value="<?php echo esc_attr( wp_unslash( $last_name ) ); ?>" size="25" />
			</label>
		</p>
		<?php
	}

	public static function callback_registration_errors( $errors, $sanitized_user_login, $user_email ) {
		global $points_api;

		if ( empty( $_POST['first_name'] ) || ! empty( $_POST['first_name'] ) && trim( $_POST['first_name'] ) == '' ) {
			$errors->add( 'first_name_error', __( '<strong>ERROR</strong>: You must include a first name.', 'points_api' ) );
		}
		if ( empty( $_POST['last_name'] ) || ! empty( $_POST['last_name'] ) && trim( $_POST['last_name'] ) == '' ) {
			$errors->add( 'last_name_error', __( '<strong>ERROR</strong>: You must include a last name.', 'points_api' ) );
		}
		if ( ! $errors->get_error_code() ) {

			$data = array( 'email' => $_POST['user_email'] );
			$data_json = json_encode( $data );

			$response = self::sendRequest( $points_api->get_settings_option('login_url'), 'POST', array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body' => $data_json,
			) );
			if ( self::getResponseCode( $response ) === 200 ) {
				$errors->add( 'user_email_error', __( '<strong>ERROR</strong>: Email already taken! Please, use another one.', 'points_api' ) );
			}
		}

		return $errors;
	}

	public static function callback_user_register( $user_id ) {
		global $points_api;

		if ( isset( $_POST['user_email'] ) ) {
			$data_json = json_encode( array(
				'id' => $user_id,
				'email' => $_POST['user_email'],
				'userName' => $_POST['user_login'],
				'firstName' => $_POST['first_name'],
				'lastName' => $_POST['last_name'],
			) );

			$response = self::sendRequest( $points_api->get_settings_option( 'signup_url' ), 'POST', array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body' => $data_json,
			) );
			if ( self::getResponseCode( $response ) == 201 ) {
				$response_body = json_decode( self::getResponseBody( $response ) );
				update_user_meta( $user_id, 'uid', $response_body->id );
			}
		}
	}

	public static function callback_authenticate_user( $user, $user_password ) {
		global $points_api;

		if ( ! empty( $user ) && self::isApiActiveFor( $user ) ) {
			$errors = new WP_Error();
			if ( ! empty( $user_password ) ) {
				if ( $user && wp_check_password( $user_password, $user->data->user_pass, $user->ID ) ) {
					$uid = get_user_meta( $user->ID, 'uid', true ) ?: $user->ID;
					$url = trim($points_api->get_settings_option('fetch_user_data_url'), '/');
					$response = self::sendRequest( "{$url}/{$uid}", 'GET' );
					if ( is_wp_error( $response ) ) {

						return $response;
					} else if ( self::getResponseCode( $response ) == 200 ) {
						$response_body = json_decode( self::getResponseBody( $response ) );
						if( ( ! empty ( $response_body->id ) && $response_body->id == $user->ID ) ) {

							// Return User object to allow authentication.
							return $user;
						} else {
							// Create an error to return to user.
							$errors->add( 'title_error', __( "<strong>ERROR</strong>: User does not exists!", 'points_api' ) );

							return $errors;
						}
					}
				} else {
					return $user;
				}
			}

			$errors->add( 'api_error', __( "<strong>ERROR</strong>: Authentication failed!", 'points_api' ) );
			return $errors;
		} else {
			return $user;
		}
	}

	/**
	 * Send HTTP Request.
	 *
	 * @param    string              $url            Request URL string.
	 * @param    string              $method         Request method [GET, POST, etc.].
	 * @param    array               $args           Extra arguments array.
	 *
	 * @return array|WP_Error
	 */
	private static function sendRequest( $url, $method, $args = [] ) {
		if ( filter_var( $url, FILTER_VALIDATE_URL ) === false ) {
			return new WP_Error( 'api_error', __( "<strong>ERROR</strong>: Gerenal failure! Please, contact support.", 'points_api' ) );
		}

		return wp_remote_request( $url, array_merge( array( 'method' => $method, 'timeout' => self::getTimeout() ), $args ) );
	}

	/**
	 * Get response Code.
	 *
	 * @param $response
	 *
	 * @return int|string
	 */
	public static function getResponseCode( $response ) {
		return wp_remote_retrieve_response_code( $response );
	}

	/**
	 * Get response Body.
	 *
	 * @param $response
	 *
	 * @return string
	 */
	public static function getResponseBody( $response ) {
		return wp_remote_retrieve_body( $response );
	}

	/**
	 * Get API Timeout from settings.
	 *
	 * @return int
	 */
	private static function getTimeout() {
		global $points_api;

		return $points_api->get_settings_option('api_timeout') ?: 5;
	}

	/**
	 * Validate, if current user should be verified using API.
	 *
	 * @param WP_User|bool $user
	 *
	 * @return bool
	 */
	private static function isApiActiveFor( $user = false ) {
		if ( ! $user ) $user = _wp_get_current_user();
		$disallowed_roles = array( 'editor', 'administrator', 'author' );

		return ! array_intersect( $disallowed_roles, $user->roles );

	}

	/**
	 * Get ClassName.
	 *
	 * @return object
	 */
	public static function getClass() {
		return get_class();
	}
}
