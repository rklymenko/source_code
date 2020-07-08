<?php
/**
 * Plugin Name: Additional Gutenberg Blocks
 * Description: Additional Gutenberg Blocks — is a Gutenberg plugin created via create-guten-block.
 * Author: Ruslan Klymenko
 * Author URI: https://cubydev.com/
 * Version: 1.0.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package CGB
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block Initializer.
 */
require_once plugin_dir_path( __FILE__ ) . 'src/init.php';
