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

	public function register_routes( $routes ) {
		$media_routes = array(
			'/authenticate' => array(
				array( array( $this, 'authenticate' ), WP_JSON_Server::CREATABLE ),
			)
		);
		return array_merge( $routes, $media_routes );
	}

	public function authenticate($user_login, $user_pass){
		$auth_response = wp_authenticate( $user_login, $user_pass );
		if( is_wp_error( $auth_response )){
			$auth_response->add_data(array( 'status' => 200 ));
			return $auth_response;
		}

		$user_data = aj_get_user_model($auth_response->ID);

		return $user_data;
	}

}

function add_user_auth_api($server){
	$aj_user_auth_api = new AjUserAuthenicationApi( $server );
	add_filter( 'json_endpoints', array( $aj_user_auth_api, 'register_routes'), 0 );
}
add_action( 'wp_json_server_before_serve', 'add_user_auth_api', 10, 1 );
