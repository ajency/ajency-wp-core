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
 * Function to get the user model
 * @param  integer $user_id [description]
 * @return [type]           [description]
 */
function aj_get_user_model($user_id = 0){
	$user_model = array();

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
	$profile_picture_id = get_user_meta($user_id,' profile_picture_id', true);
	if((int)$profile_picture_id === 0)
		return array();

	$image = wp_get_attachment_metadata( $profile_picture_id );

	return array(
			'id' => $profile_picture_id,
			'sizes' => $image->sizes
	);
}
