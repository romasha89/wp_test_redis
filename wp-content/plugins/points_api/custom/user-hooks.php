<?php
add_filter( 'wp_login', 'points_api_authenticate', 10, 3 );
function points_api_authenticate( $user_login, $user ) {

	if ( ! empty( $username ) && ! empty( $password )  ) {
		//Get user object
		if ( is_email( $username ) ) {
			$user = get_user_by('email', $username );
		} else {
			$user = get_user_by('login', $username );
		}

		//Get stored value
		$uid = get_user_meta($user->ID, 'uid', true);

		$data = array( 'id' => $uid );
		$data_json = json_encode( $data );

		$response = wp_remote_request('http://yeb4tqzyxwmksaa7x.stoplight-proxy.io/api/points/v1/login', [
			'method' => 'POST',
			'headers' => [
				'Content-Type' => 'application/json',
			],
			'timeout' => 30,
			'body' => $data_json,
		]);

		if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			$body = json_decode( wp_remote_retrieve_body( $response ) );
		}

		if( ! $user || empty( $body ) ) {
			//User note found, or no value entered or doesn't match stored value - don't proceed.
			remove_action('authenticate', 'wp_authenticate_username_password', 20);
			remove_action('authenticate', 'wp_authenticate_email_password', 20);

			//Create an error to return to user
			$user = new WP_Error( 'denied', __("<strong>ERROR</strong>: User not Found!") );
		}
	}

	//Make sure you return null
	return null;
}

////2. Add validation. In this case, we make sure first_name is required.
//add_filter( 'registration_errors', 'points_api_registration_errors', 10, 3 );
//function points_api_registration_validate( $errors, $sanitized_user_login, $user_email ) {
//
//	if ( empty( $_POST['first_name'] ) || ! empty( $_POST['first_name'] ) && trim( $_POST['first_name'] ) == '' ) {
//		$errors->add( 'first_name_error', __( '<strong>ERROR</strong>: You must include a first name.', 'mydomain' ) );
//	}
//
//	return $errors;
//}

add_action( 'user_register', 'points_api_registration_save', 10, 1 );
function points_api_registration_save( $user_id ) {
	if ( isset( $_POST['user_email'] ) ) {
		update_user_meta( $user_id, 'uid', '1-1' );
	}
}

$fake = TRUE;