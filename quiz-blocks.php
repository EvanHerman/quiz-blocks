<?php
/**
 * Plugin Name:       Quiz Blocks
 * Description:       Quiz blocks for WordPress.
 * Requires at least: 5.9
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Evan Herman
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       quiz-blocks
 *
 * @package           create-block
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

define( 'QUIZ_BLOCKS_VERSION', '1.0.0' );
define( 'JQUERY_MODAL_VERSION', '0.9.1' );

/**
 * Quiz Blocks main class.
 */
class Quiz_Blocks {

	/**
	 * Quiz_Blocks class constructor
	 *
	 * @since 0.1
	 */
	public function __construct() {

		require_once plugin_dir_path( __FILE__ ) . 'includes/class-helpers.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-blocks.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-cpt.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-submission-handler.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-rankings.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-results.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-user-profile.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-view-submissions.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-view-submission.php';

	}

}

new Quiz_Blocks();
