<?php

// // Activates this plugin in WordPress so it can be tested.
// $GLOBALS['wp_tests_options'] = array(
//     'active_plugins' => array(basename( dirname( dirname( __FILE__ ) ) ) . '/aj-user-management.php'),
// );
global $_tests_dir;
$_tests_dir = getenv('WP_TESTS_DIR');
if ( !$_tests_dir ) $_tests_dir = '/tmp/wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	global $_tests_dir;
	// include helper plugin
	require $_tests_dir . '/tmp/wordpress/wp-content/plugins/json-rest-api/plugin.php';
	require dirname( __FILE__ ) . '/../ajency-wp-core.php';

}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
