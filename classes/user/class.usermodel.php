<?php


class AjUserModel{

	private $id;

	public function __contstruct($user_id = 0){
		if((int)$user_id === 0)
			return;

		$user_data = get_userdata( $user_id );
		if ($user_data === false)
			return;

		$this->id = $user_id;
	}

	public function get_data(){
		$user_data = get_userdata($this->id );
		$this->data = array(
						'ID' => $this->id,
						'user_login' => $user_data->data->user_login,
						'user_email' => $user_data->data->user_email,
						'display_name' => $user_data->data->display_name,
						'profile_picture_id' => get_user_meta( $this->id, profile_picture_id, true )
					);

		return $this->data;
	}

}
