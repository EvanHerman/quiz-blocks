<?php
/**
 * Quiz Blocks Submission Handler
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

class Quiz_Blocks_Submission_Handler {

	public function __construct() {

		add_action( 'wp_ajax_validate_answers', array( $this, 'validate_answers' ), PHP_INT_MAX );

	}

	/**
	 * Validate the user submitted answers.
	 */
	public function validate_answers() {

		if ( ! is_user_logged_in() ) {

			wp_send_json_error( 'User is not logged in', 401 );

		}

		if ( ! isset( $_POST['quizID'] ) ) {

			wp_send_json_error( 'Missing Quiz ID.', 400 );

		}

		if ( ! isset( $_POST['answers'] ) ) {

			wp_send_json_error( 'Missing Answers.', 400 );

		}

		$quiz_id      = filter_input( INPUT_POST, 'quizID', FILTER_VALIDATE_INT );
		$user_answers = array_values( wp_parse_args( filter_input( INPUT_POST, 'answers' ) ) );

		$correct_answers = $this->get_test_answers( $quiz_id );

		$response = array();

		foreach ( $user_answers as $question_number => $answer ) {

			if ( (int) $answer !== (int) $correct_answers[ $question_number ] ) {

				$response['results'][ $question_number ] = 'incorrect';

				continue;

			}

			$response['results'][ $question_number ] = 'correct';

		}

		$response['counts'] = array_count_values( $response['results'] );

		$this->store_test_results( $quiz_id, $response );

		wp_send_json_success(
			array(
				'quizID'   => $quiz_id,
				'response' => $response,
			)
		);

	}

	/**
	 * Get the answers for a test.
	 *
	 * @param int $quiz_id The quiz ID to retreive answers for.
	 *
	 * @return array Answers for the specified quiz ID.
	 */
	private function get_test_answers( $quiz_id ) {

		$answers = array();
		$post    = get_post( $quiz_id );

		if ( has_blocks( $post->post_content ) ) {

			// Filter out empty blocks on the page.
			$blocks = array_values( array_filter(
				parse_blocks( $post->post_content ),
				function( $value ) {
					return ! empty( $value['attrs'] );
				}
			) );

			$block_attributes = wp_list_pluck( $blocks, 'attrs' );
			$answers          = wp_list_pluck( $block_attributes, 'correctAnswer' );

			foreach ( $answers as $index => $answer ) {
				if ( ! empty( $answer ) ) {
					continue;
				}
				$answers[ $index ] = 0;
			}
		}

		return $answers;

	}

	/**
	 * Store the results for the test.
	 *
	 * @param int   $quiz_id The quiz ID to retreive answers for.
	 * @param array $results The quiz results.
	 */
	private function store_test_results( $quiz_id, $results ) {

		$existing_results = get_post_meta( $quiz_id, 'results', true );

		if ( ! $existing_results ) {

			$existing_results = array();

		}

		$user_id = get_current_user_id();

		// Determine if user already submitted results to this test.
		$existing_user_key = array_search( $user_id, array_column( $existing_results, 'user_id' ), true );

		$correct_count   = isset( $results['counts']['correct'] ) ? $results['counts']['correct'] : 0;
		$incorrect_count = isset( $results['counts']['incorrect'] ) ? $results['counts']['incorrect'] : 0;
		$percent_correct = ( $correct_count / count( $results['results'] ) ) * 100;

		// Update a user had previously submitted the quiz.
		if ( false !== $existing_user_key ) {
			$existing_results[ $existing_user_key ]['percent']             = $percent_correct;
			$existing_results[ $existing_user_key ]['counts']['correct']   = $correct_count;
			$existing_results[ $existing_user_key ]['counts']['incorrect'] = $incorrect_count;

			update_post_meta( $quiz_id, 'results', $existing_results );

			return;
		}

		$results['user_id'] = $user_id;
		$results['percent'] = $percent_correct;
		$results['date']    = strtotime( 'now' );

		$existing_results[] = $results;

		update_post_meta( $quiz_id, 'results', $existing_results );

	}

}

new Quiz_Blocks_Submission_Handler();
