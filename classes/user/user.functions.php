<?php

/**
 * Function to generate the JSON rest API vars
 * Creates global variable APIURL and WP_JSON_API_NONCE
 * Added the nonce to global jQuery ajax setup so it is
 * sent with every request
 * @return [type] [description]
 */
/* TODO: Move this function to proper file */
function aj_get_global_js_vars(){
	ob_start(); ?>
	var userData = <?php echo json_encode(aj_get_user_model(get_current_user_id()));?>;
	var notLoggedInCaps = <?php echo json_encode(aj_get_not_logged_in_caps()[0]);?>;
    var allSystemCaps = <?php echo json_encode(aj_get_all_caps()); ?>;
	var APIURL = '<?php echo esc_url_raw( get_json_url()) ?>';
	var _SITEURL = '<?php echo esc_url_raw( site_url()) ?>';
	<?php
	$html = ob_get_clean();
	return $html;
}

/**
 * TODO: Move this function to proper file
 */
function aj_get_facebook_js(){
	if(!defined('FBAPPID'))
		wp_die('Please define "FBAPPID" in wp-config.php');

	ob_start(); ?>
	var FBAPPID  = '<?php echo FBAPPID ?>';
    if(typeof FBAPPID !== 'undefined')
        facebookConnectPlugin.browserInit(App, FBAPPID, 'v2.2');
    <?php
	$html = ob_get_clean();
	return $html;
}

function aj_capNamesCB ( $cap ){
	$cap = str_replace('_', ' ', $cap);
	//$cap = ucfirst($cap);

	return $cap;
}

function aj_get_all_caps(){
	$max_level = 10;
	$roles = aj_get_roles(true);
	$caps = array();

	foreach ( array_keys($roles) as $role ) {
		$role_caps = get_role($role);
		// user reported PHP 5.3.3 error without array cast
		$caps = array_merge( $caps, (array) $role_caps->capabilities );  
	}

	$keys = array_keys($caps);
	$names = array_map('aj_capNamesCB', $keys);
	$capabilities = array_combine($keys, $names);
	asort($capabilities);

	return array_keys($capabilities);
}



/**
 * Returns all valid roles.
 * The returned list can be translated or not.
 *
 * @uses apply_filters() Calls the 'alkivia_roles_translate' hook on translated roles array.
 * @since 0.5
 *
 * @param boolean $translate If the returned roles have to be translated or not.
 * @return array All defined roles. If translated, the key is the role name and value is the translated role.
 */
function aj_get_roles( $translate = false ) {
	global $wp_roles;
	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}

	$roles = $wp_roles->get_names();
	if ( $translate ) {
		foreach ($roles as $k => $r) {
			$roles[$k] = _x($r, 'User role');
		}
		asort($roles);
		return apply_filters('aj_roles_translate', $roles);
	} else {
		$roles = array_keys($roles);
		asort($roles);
		return $roles;
	}
}

/**
 * get capabilities for not logged in role
 * @return [type] [description]
 */
function aj_get_not_logged_in_caps(){

	$not_logged_in_role = get_role('not_logged_in');
	$capabilities = array($not_logged_in_role->capabilities);
	return apply_filters('aj_not_logged_in_caps', $capabilities );

}

function aj_generate_key($length) {
	$random= "";
	srand((double)microtime()*1000000);

	$data = "AbcDE123IJKLMN67QRSTUVWXYZ";
	$data .= "aBCdefghijklmn123opq45rs67tuv89wxyz";
	$data .= "0FGH45OP89";

	for($i = 0; $i < $length; $i++) {
		$random .= substr($data, (rand()%(strlen($data))), 1);
	}

	return $random;
}



/**
 * Returns the API key for the user
 * @param  [type] $user_id [description]
 * @return [type]          [description]
 */
function aj_get_user_api_key($user_id){
	$key = get_user_meta( $user_id, 'json_api_key' , true);
	if($key === ''){
		$key = aj_generate_key(15);
		update_user_meta($user_id, 'json_api_key', $key);
	}
	return $key;
}

/**
 * Returns the shared secret for the user
 * @param  [type] $user_id [description]
 * @return [type]          [description]
 */
function aj_get_user_shared_secret($user_id){
	$key = get_user_meta( $user_id, 'json_shared_secret' , true);
	if($key === ''){
		$key = aj_generate_key(15);
		update_user_meta($user_id, 'json_shared_secret', $key);
	}
	return $key;
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
