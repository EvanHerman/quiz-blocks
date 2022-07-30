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

class Quiz_Blocks {

	/**
	 * Quiz_Blocks class constructor
	 *
	 * @since 0.1
	 */
	public function __construct() {

		require_once plugin_dir_path( __FILE__ ) . 'includes/class-blocks.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-cpt.php';
		// require_once plugin_dir_path( __FILE__ ) . 'src/quiz/quiz.php';
		// require_once plugin_dir_path( __FILE__ ) . 'includes/class-validate-answers.php';
		// require_once plugin_dir_path( __FILE__ ) . 'includes/class-rankings.php';

		// add_action( 'wp_ajax_get_quiz_data', array( $this, 'get_quiz_data' ), PHP_INT_MAX );

	}

	/**
	 * Retrieve all page IDs where quizes are enabled.
	 *
	 * @return array Page IDs where quizzes have been enabled on.
	 */
	public function get_quiz_selections() {

		$quiz_ids = get_posts(
			array(
				'post_type'      => 'quiz',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		$pageids = array();

		foreach ( $quiz_ids as $id ) {

			$pageids[] = array(
				'label' => html_entity_decode( get_the_title( $id ) ),
				'value' => $id,
			);

		}

		return array_filter( $pageids );

	}

	/**
	 * Retreive quiz data.
	 *
	 * @param int $quiz_id The quizID.
	 *
	 * @return array Array of quiz data.
	 */
	public function get_quiz_data( $quiz_id = 0 ) {

		return array(
			'name'      => get_the_title( $quiz_id ),
			'questions' => get_post_meta( $quiz_id, 'quiz_block_questions', true ),
		);

	}

}

new Quiz_Blocks();
