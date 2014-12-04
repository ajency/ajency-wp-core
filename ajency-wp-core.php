<?php
/**
 * Ajency Wp Core
 *
 * Ajency's wordpress core plugin to keep code DRY
 *
 * @package   ajency-wp-core
 * @author    Team Ajency <team@ajency.in>
 * @license   GPL-2.0+
 * @link      http://ajency.in
 * @copyright 12-4-2014 Ajency.in
 *
 * @wp-plugin
 * Plugin Name: Ajency Wp Core
 * Plugin URI:  http://ajency.in
 * Description: Ajency's wordpress core plugin to keep code DRY
 * Version:     0.1.0
 * Author:      Team Ajency
 * Author URI:  http://ajency.in
 * Text Domain: ajency-wp-core-locale
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if (!defined("WPINC")) {
	die;
}

require_once(plugin_dir_path(__FILE__) . "AjencyWpCore.php");

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook(__FILE__, array("AjencyWpCore", "activate"));
register_deactivation_hook(__FILE__, array("AjencyWpCore", "deactivate"));

AjencyWpCore::get_instance();
