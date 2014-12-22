<?php

class AjUserAuthenicationApi{

	/**
	 * Server object
	 *
	 * @var WP_JSON_ResponseHandler
	 */
	protected $server;

	/**
	 * Constructor
	 *
	 * @param WP_JSON_ResponseHandler $server Server object
	 */
	public function __construct( WP_JSON_ResponseHandler $server ) {
		$this->server = $server;
	}

	public function register_routes( $routes = array()) {
		$auth_routes = array(
			'/authenticate' => array(
				array( array( $this, 'authenticate' ), WP_JSON_Server::CREATABLE ),
			),
			'/userprofile' => array(
				array( array( $this, 'user_profile' ), WP_JSON_Server::EDITABLE | WP_JSON_Server::ACCEPT_JSON ),
			),
			// TODO : change the location of this code
			'/attachments' => array(
				array( array( $this, 'upload_attachment' ), WP_JSON_Server::CREATABLE ),
			)
		);
		return array_merge( $routes, $auth_routes );
	}

	/**
	 * [attachment_upload description]
	 * @return [type] [description]
	 */
	public function upload_attachment(){
		if ( ! current_user_can( 'upload_files' ) )
			return new WP_Error('no_permission', __('You dont have enough permission'),
				 array('status' => 200 ));

		$attachment_id = media_handle_upload( 'async-upload', null, array() );

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		$attachment = wp_prepare_attachment_for_js( $attachment_id );

		if ( ! ( $attachment instanceof WP_JSON_ResponseInterface ) ) {
			$response = new WP_JSON_Response( $attachment );
		}
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Authenticates the user based on passed user_login and password
	 * @param  [String] $user_login User login
	 * @param  [String] $user_pass  User password
	 * @return WP_JSON_ResponseInterface the ajax response
	 */
	public function authenticate($user_login, $user_pass, $type = 'basic', $userData = array()){

		if($type === 'basic'){
			$auth_response = wp_authenticate( $user_login, $user_pass );
		}
		else if($type === 'facebook'){
			$access_token = $user_pass;
			$user_email = $user_login;
			$auth_response = $this->authenticate_with_facebook( $user_login, $access_token, $userData );
		}

		if( is_wp_error( $auth_response )){
			$auth_response->add_data(array( 'status' => 200 ));
			return $auth_response;
		}

		$response = aj_get_user_model($auth_response->ID);


		if ( ! ( $response instanceof WP_JSON_ResponseInterface ) ) {
			$response = new WP_JSON_Response( $response );
		}

		$data = $response->get_data();

		//set auth cookies
		wp_set_auth_cookie( $data->ID );
		$response->header( 'HTTP_X_API_KEY', aj_get_user_api_key($data->ID));
		$response->header( 'HTTP_X_SHARED_SECRET', aj_get_user_shared_secret($data->ID));

		$response->header( 'Location', json_url( '/users/' . $data->ID ));
		$response->set_status( 201 );

		return $response;
	}

	public function authenticate_with_facebook($user_login, $access_token, $user_data){

		$user = false;
		$user_id = $this->findUserIdByFBID($user_data['id']);

		if($user_id === false){
			$user_id =	$this->create_user_by_fb($user_data);
		}

		if($user_id === false)
			return new WP_Error('user_not_created', __('User not created'));

		$user = get_userdata( $user_id );

		return $user;

	}

	public function create_user_by_fb($user_data){
		//register the user if not exist

		$user_id = false;
        if ( email_exists($user_data['email']) == false ) {
            $random_password = wp_generate_password( 12, false );
            $user_id = wp_create_user( "FB_".$user_data['id'], $random_password, $user_data['email'] );
            //set user data
	        $userprofiledata = array(
					            'ID' => $user_id,
					            'first_name' => $user_data['first_name'],
					            'last_name' => $user_data['last_name'],
					            'display_name' => $user_data['name']
	            			);

	        wp_update_user( $userprofiledata );
	        update_user_meta( $user_id, 'facebook_uid', $user_data['id'] );
	    }
	    return $user_id;
	}

	public function findUserIdByFBID( $fb_id ) {
		$user_args = array(
			'meta_query' => array(
				array(
					'key' => 'facebook_uid',
					'value' => $fb_id,
				),
			),
			'number' => 1,
			'fields' => array( 'ID' ),
		);
		$user = get_users( $user_args );

		if ( is_array( $user ) && !empty( $user ) ) {
			return $user[0]->ID;
		}

		return false;
	}

	/**
	 * [user_profile description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function user_profile($data){

		$response = aj_update_user_model($data);

		if( is_wp_error( $response )){
			$response->add_data(array( 'status' => 302 ));
			return $response;
		}

		if ( ! ( $response instanceof WP_JSON_ResponseInterface ) ) {
			$response = new WP_JSON_Response( $response );
		}
		$response->set_status( 200 );
		return $response;
	}

}

function add_user_auth_api($server){
	$aj_user_auth_api = new AjUserAuthenicationApi( $server );
	add_filter( 'json_endpoints', array( $aj_user_auth_api, 'register_routes'), 0 );
}
add_action( 'wp_json_server_before_serve', 'add_user_auth_api', 10, 1 );
