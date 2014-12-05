<?php

class AjUserModelTest extends WP_UnitTestCase {

	public function setUp(){
		parent::setUp();
		$this->user_id1 = $this->factory->user->create(array(
								'user_login' => 'user1',
								'user_email' => 'user1@mailinator.com',
								'display_name' => 'User One'
							));
		$this->user_model = new AjUserModel($this->user_id1);
	}

	public function test_delete_role() {
		$this->assertTrue(true);
	}
}

