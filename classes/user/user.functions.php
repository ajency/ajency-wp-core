<?php

/**
 * Function to generate the JSON rest API vars
 * Creates global variable APIURL and WP_JSON_API_NONCE
 * Added the nonce to global jQuery ajax setup so it is
 * sent with every request
 * @return [type] [description]
 */
function get_wp_json_rest_api_vars(){
	ob_start(); ?>
	var APIURL = '<?php echo esc_url_raw( get_json_url()) ?>';
	var WP_JSON_API_NONCE = '<?php echo  wp_create_nonce( "wp_json" )  ?>';
	jQuery.ajaxSetup({headers : { 'X-WP-Nonce': WP_JSON_API_NONCE}});
	<?php
	$html = ob_get_clean();
	return $html;
}

/**
 * Returns the API key for the user
 * @param  [type] $user_id [description]
 * @return [type]          [description]
 */
function aj_get_user_api_key($user_id){
	return get_user_meta( $user_id, 'json_api_key' , true);
}

/**
 * Returns the shared secret for the user
 * @param  [type] $user_id [description]
 * @return [type]          [description]
 */
function aj_get_user_shared_secret($user_id){
	return get_user_meta( $user_id, 'json_shared_secret' , true);
}

/**
 * Function to get the user model
 * @param  integer $user_id [description]
 * @return [type]           [description]
 */
function aj_get_user_model($user_id = 0){
	$user_model = array();

	if($user_id === 0){
		return $user_model;
	}

	$user_data = get_userdata( $user_id );

	$user_model = $user_data->data;

	unset($user_model->user_pass);
	unset($user_model->user_activation_key);
	unset($user_model->user_url);

	$user_model->caps = (object)array_merge((array)$user_data->allcaps, (array)$user_data->caps);

	$user_model->profile_picture = aj_get_user_profile_picture($user_id);

	$user_model->ID = (int) $user_data->ID;

	return apply_filters( 'aj_user_model', $user_model );
}

/**
 * Function to return the profile picture info for a user
 * @param  integer $user_id [description]
 * @return [type]           [description]
 */
function aj_get_user_profile_picture($user_id = 0){

	$profile_picture_id = get_user_meta($user_id,'profile_picture_id', true);
	if((int)$profile_picture_id === 0){
		$mock_image = array(
				'id' => 0,
				'sizes' => array(
					"thumbnail" => array(
			                "height" => 150,
			                "width" => 150,
			                "url" => "http://placehold.it/200x200",
			                "orientation" => "landscape"
			            )
			         )
				);
		return apply_filters('aj_mock_user_profile_picture', $mock_image ,$user_id );
	}
	else{

		if(get_user_meta( $user_id, 'facebook_uid', true ) !== ''){

		}

		$image = wp_prepare_attachment_for_js( $profile_picture_id );
		$profile_picture = array(
						'id' => $profile_picture_id,
						'sizes' => $image['sizes']
					);

	}

	return apply_filters('aj_user_profile_picture', $profile_picture ,$user_id );
}

/**
 * [aj_update_user_model description]
 * @param  [type] $data [description]
 * @return [type]       [description]
 */
function aj_update_user_model($data){

	if(!isset($data['ID']))
		return new WP_Error('user_id_missing', __('User Id not passed'), array('status' => 302));

	$user_id = $data['ID'];

	$defaults = array(
					'display_name' => '',
					'profile_picture' => array( 'id' => 0 ),
					'user_email' => ''
				);

	$args = wp_parse_args( $data, $defaults );
	extract($args);

	wp_update_user(array('ID' => $user_id,'display_name' => $display_name));

	//update profile picture
	$profile_picture_id = $profile_picture['id'];
	update_user_meta( $user_id, 'profile_picture_id', $profile_picture_id );

	/**
	 * Ability for theme dev to add aditinal functionality after user model save
	 */
	do_action('aj_update_user_model', $data);

	$user_model = aj_get_user_model($user_id);

	return $user_model;
}

function check_facebook_profile_picture($picture){

	if(!is_user_logged_in())
		return $picture;

	$fb_user_id = get_user_meta( $user_id, 'facebook_uid', true );

	if( $fb_user_id == '')
		return $picture;

	return $picture;

}
//add_filter('aj_mock_user_profile_picture', 'check_facebook_profile_picture', 10, 1);
