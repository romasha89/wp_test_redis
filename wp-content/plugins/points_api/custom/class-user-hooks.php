<?php

class UserHooks {

	public function callback_register_form() {
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

	public function callback_registration_errors( $errors, $sanitized_user_login, $user_email ) {
		global $points_api;

		if ( empty( $_POST['first_name'] ) || ! empty( $_POST['first_name'] ) && trim( $_POST['first_name'] ) == '' ) {
			$errors->add( 'first_name_error', __( '<strong>ERROR</strong>: You must include a first name.', 'points_api' ) );
		}
		if ( empty( $_POST['last_name'] ) || ! empty( $_POST['last_name'] ) && trim( $_POST['last_name'] ) == '' ) {
			$errors->add( 'last_name_error', __( '<strong>ERROR</strong>: You must include a last name.', 'points_api' ) );
		}
		if ( empty( $errors->get_error_code() ) ) {

			$data = array( 'email' => $_POST['user_email'] );
			$data_json = json_encode( $data );

			$response = wp_remote_request($points_api->get_settings_option('login_url'), [
				'method' => 'POST',
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'timeout' => 2,
				'body' => $data_json,
			]);

			if ( wp_remote_retrieve_response_code( $response ) === 200 ) {
				$errors->add( 'user_email_error', __( '<strong>ERROR</strong>: Email already taken! Please, use another one.', 'points_api' ) );
			}
		}

		return $errors;
	}

	public function callback_user_register( $user_id ) {
		global $points_api;

		if ( isset( $_POST['user_email'] ) ) {
			$data_json = json_encode( [
				'id' => $user_id,
				'email' => $_POST['user_email'],
				'userName' => $_POST['user_login'],
				'firstName' => $_POST['first_name'],
				'lastName' => $_POST['last_name'],
			] );

			$response = wp_remote_request($points_api->get_settings_option('signup_url'), [
				'method' => 'POST',
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'timeout' => 2,
				'body' => $data_json,
			]);

			if ( wp_remote_retrieve_response_code( $response ) == 201 ) {
				$response_body = json_decode( wp_remote_retrieve_body( $response ) );
				update_user_meta( $user_id, 'uid', $response_body->id );
			}
		}
	}

	public function callback_authenticate_user( $user, $user_password ) {
		global $points_api;

		if ( ! empty( $user ) && ! empty( $user_password )  ) {
			if ( $user && wp_check_password( $user_password, $user->data->user_pass, $user->ID ) ) {
				$uid = get_user_meta( $user->ID, 'uid', true ) ?: $user->ID;

				$url = trim($points_api->get_settings_option('fetch_user_data_url'), '/');
				$response = wp_remote_request("{$url}/{$uid}", [
					'method' => 'GET',
					'timeout' => 2,
				]);

				if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
					$response_body = json_decode( wp_remote_retrieve_body( $response ) );
				}

				if( ! $user || empty( $response_body ) || ( ! empty ( $response_body->id ) && $response_body->id !== $user->ID ) ) {
					// Create an error to return to user.
					$errors = new WP_Error();
					$errors->add('title_error', __("<strong>ERROR</strong>: User does not exists!", 'points_api'));

					return $errors;
				}
			}
		}

		return $user;
	}

}