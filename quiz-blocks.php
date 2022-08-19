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

		register_activation_hook( __FILE__, array( $this, 'activation_hook' ) );

		require_once plugin_dir_path( __FILE__ ) . 'includes/class-helpers.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-blocks.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-cpt.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-submission-handler.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-rankings.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-results.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-user-profile.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-submissions-table.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-view-submissions.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-view-submission.php';

	}

	/**
	 * Activation hook.
	 */
	public function activation_hook() {

		$show_shortcode_column = is_plugin_active( 'classic-editor/classic-editor.php' );

		$user_id = get_current_user_id();

		$quiz_columns_hidden = get_user_meta( $user_id, 'manageedit-quizcolumnshidden', true );

		// Don't hide the shortcode column if the Classic Editor plugin is active.
		if ( $show_shortcode_column ) {

			$index = array_search( 'shortcode', $quiz_columns_hidden, true );

			if ( false !== $index ) {

				unset( $quiz_columns_hidden[ $index ] );

				update_user_meta( get_current_user_id(), 'manageedit-quizcolumnshidden', array_values( $quiz_columns_hidden ) );

			}

			return;

		}

		$hidden_columns = array_merge(
			$quiz_columns_hidden,
			array( 'shortcode' )
		);

		update_user_meta( get_current_user_id(), 'manageedit-quizcolumnshidden', $hidden_columns );

	}

}

new Quiz_Blocks();
