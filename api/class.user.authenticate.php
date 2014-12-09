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
			)
		);
		return array_merge( $routes, $auth_routes );
	}

	/**
	 * Authenticates the user based on passed user_login and password
	 * @param  [String] $user_login User login
	 * @param  [String] $user_pass  User password
	 * @return WP_JSON_ResponseInterface the ajax response
	 */
	public function authenticate($user_login, $user_pass){
		$auth_response = wp_authenticate( $user_login, $user_pass );
		if( is_wp_error( $auth_response )){
			$auth_response->add_data(array( 'status' => 200 ));
			return $auth_response;
		}

		$response = aj_get_user_model($auth_response->ID);

		if ( ! ( $response instanceof WP_JSON_ResponseInterface ) ) {
			$response = new WP_JSON_Response( $response );
		}

		$data = $response->get_data();

		$response->header( 'Location', json_url( '/users/' . $data->ID ));
		$response->set_status( 201 );

		return $response;
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
