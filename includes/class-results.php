<?php
/**
 * Quiz Blocks Results
 *
 * @package Quiz_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * Quiz_Blocks_Results class.
 */
class Quiz_Blocks_Results {

	/**
	 * Quiz_Blocks_Results constructor.
	 */
	public function __construct() {

		add_action( 'wp_ajax_get_existing_result', array( $this, 'get_existing_result' ), PHP_INT_MAX );

	}

	/**
	 * Retreive the result for logged in user.
	 */
	public function get_existing_result() {

		if ( ! isset( $_GET['quizID'] ) ) { // phpcs:ignore

			wp_send_json_error( __( 'Missing Quiz ID.', 'quiz-blocks' ), 400 );

		}

		$quiz_id = filter_input( INPUT_GET, 'quizID', FILTER_VALIDATE_INT );

		// Get the quiz post meta.
		$quiz_results = get_post_meta( $quiz_id, 'results', true );

		if ( ! $quiz_results ) {

			wp_send_json_error( __( 'No quiz results found.', 'quiz-blocks' ), 500 );

		}

		$user_id = get_current_user_id();

		if ( 0 === $user_id ) {

			wp_send_json_error( __( 'Invalid user id.', 'quiz-blocks' ), 500 );

		}

		$user_result_key = array_search( $user_id, array_column( $quiz_results, 'user_id' ), true );

		if ( false === $user_result_key ) {

			wp_send_json_error( __( 'User submission not found.', 'quiz-blocks' ), 500 );

		}

		wp_send_json_success(
			array(
				'quizID'  => $quiz_id,
				'results' => $quiz_results[ $user_result_key ],
			)
		);

	}

}

new Quiz_Blocks_Results();
