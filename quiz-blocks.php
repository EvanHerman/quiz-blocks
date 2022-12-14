<?php
/**
 * Plugin Name:       Quiz Blocks
 * Description:       A WordPress plugin to easily create beautiful quizzes for your website. Log user scores, time to completion, show a leaderboard and much more!
 * Requires at least: 5.9
 * Requires PHP:      7.0
 * Version:           1.0.0
 * Author:            Code Parrots
 * Author URI:        https://www.codeparrots.com
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
define( 'QUIZ_BLOCKS_JQUERY_MODAL_VERSION', '0.9.1' );

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

		add_action( 'activated_plugin', array( $this, 'activation_redirect' ) );

		require_once plugin_dir_path( __FILE__ ) . 'includes/class-helpers.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-blocks.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-block-editor.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-cpt.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-submission-handler.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-rankings.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-results.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-user-profile.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-submissions-table.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-view-submissions.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-view-submission.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-quiz-shortcode.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/welcome-guide/class-welcome-guide.php';

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

	/**
	 * Redirect the user to our welcome guide on activation.
	 */
	public function activation_redirect() {

		wp_safe_redirect( add_query_arg( 'page', 'quiz-blocks-welcome-guide', admin_url() ), 301, 'Quiz Blocks' );

		exit;

	}

}

new Quiz_Blocks();
