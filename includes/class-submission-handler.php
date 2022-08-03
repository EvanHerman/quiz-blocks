<?php
/**
 * Quiz Blocks Submission Handler
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

class Quiz_Blocks_Submission_Handler {

	private $helpers;

	public function __construct() {

		$this->helpers = new Quiz_Blocks_Helpers();

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

		$post_id = url_to_postid( wp_get_referer() );

		$block_attributes = array();

		$quiz_id      = filter_input( INPUT_POST, 'quizID', FILTER_VALIDATE_INT );
		$user_answers = array_values( wp_parse_args( filter_input( INPUT_POST, 'answers' ) ) );
		$time_taken   = filter_input( INPUT_POST, 'timeTaken', FILTER_VALIDATE_INT );

		if ( 0 !== $post_id ) {

			$post   = get_post( $post_id );
			$blocks = parse_blocks( $post->post_content );

			$block_attributes = $this->helpers->get_block_attributes( $quiz_id, $blocks );

		}

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

		$response['user_answers'] = $user_answers;

		$response['snapshot'] = $this->get_test_snapshot( $quiz_id );

		$response['time_taken'] = $time_taken;

		if ( $block_attributes['useRankings'] ) {

			$user_id = get_current_user_id();

			$this->store_test_results( $user_id, $quiz_id, $response, $block_attributes );
			$this->store_user_meta( $user_id, $quiz_id, $response,  $block_attributes );

		}

		// Not stored as user data.
		$response['show_answers'] = isset( $block_attributes['showAnswers'] ) ? $block_attributes['showAnswers'] : true;
		$response['show_results'] = isset( $block_attributes['showResults'] ) ? $block_attributes['showResults'] : true;

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
			$blocks = array_values(
				array_filter(
					parse_blocks( $post->post_content ),
					function( $value ) {
						return ! empty( $value['attrs'] );
					}
				)
			);

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
	 * Get a "snapshot" for a given quiz.
	 *
	 * Note: A snapshot is just an array of questions and possible answers,
	 *       incase a quiz changes AFTER a user submits it, you can still
	 *       see what the questions and answers were at the time of submission.
	 *
	 * @param int $quiz_id The quiz ID to retreive questions for.
	 *
	 * @return array Question/Answers array for a given quiz.
	 */
	private function get_test_snapshot( $quiz_id ) {

		$questions = array();
		$post      = get_post( $quiz_id );

		if ( has_blocks( $post->post_content ) ) {

			// Filter out empty blocks on the page.
			$blocks = array_values(
				array_filter(
					parse_blocks( $post->post_content ),
					function( $value ) {
						return ! empty( $value['attrs'] );
					}
				)
			);

			$block_attributes = wp_list_pluck( $blocks, 'attrs' );
			$questions        = wp_list_pluck( $block_attributes, 'question' );
			$answers          = wp_list_pluck( $block_attributes, 'answers' );

			$questions = array_map( 'wp_strip_all_tags', $questions );

			$snapshot = array();

			foreach ( $questions as $index => $question ) {

				$snapshot[] = array(
					'question' => $question,
					'answers'  => $answers[ $index ],
				);

			}

			return $snapshot;

		}

		return $questions;

	}

	/**
	 * Store the results for the test.
	 *
	 * @param int   $quiz_id The quiz ID to retreive answers for.
	 * @param array $results The quiz results.
	 */
	private function store_test_results( $user_id, $quiz_id, $results, $block_attributes ) {

		$existing_results = get_post_meta( $quiz_id, 'results', true );

		if ( ! $existing_results ) {

			$existing_results = array();

		}

		$correct_count   = isset( $results['counts']['correct'] ) ? $results['counts']['correct'] : 0;
		$incorrect_count = isset( $results['counts']['incorrect'] ) ? $results['counts']['incorrect'] : 0;
		$percent_correct = ( $correct_count / count( $results['results'] ) ) * 100;

		$results['user_id']             = $user_id;
		$results['percent']             = $percent_correct;
		$results['counts']['correct']   = $correct_count;
		$results['counts']['incorrect'] = $incorrect_count;
		$results['date']                = strtotime( 'now' );

		// Determine if user already submitted results to this test.
		$existing_user_key = array_search( $user_id, array_column( $existing_results, 'user_id' ), true );

		// Update a user had previously submitted the quiz.
		if ( false !== $existing_user_key ) {

			// Prevent multiple submissions, when disabled.
			if ( ! $block_attributes['multipleSubmissions'] ) {
				return;
			}

			$existing_results[ $existing_user_key ] = $results;

			update_post_meta( $quiz_id, 'results', $existing_results );

			return;
		}

		$existing_results[] = $results;

		update_post_meta( $quiz_id, 'results', $existing_results );

	}

	/**
	 * Store the results for the test in the user_meta.
	 *
	 * @param int   $quiz_id The quiz ID to retreive answers for.
	 * @param array $results The quiz results.
	 */
	private function store_user_meta( $user_id, $quiz_id, $results, $block_attributes ) {

		$existing_results = get_user_meta( $user_id, 'quiz_results', true );

		if ( ! $existing_results ) {

			$existing_results = array();

		}

		// Determine if user already submitted results to this quiz.
		$existing_quiz_key = array_search( $quiz_id, array_column( $existing_results, 'quiz_id' ), true );

		$correct_count   = isset( $results['counts']['correct'] ) ? $results['counts']['correct'] : 0;
		$incorrect_count = isset( $results['counts']['incorrect'] ) ? $results['counts']['incorrect'] : 0;
		$percent_correct = ( $correct_count / count( $results['results'] ) ) * 100;

		// Update a user had previously submitted the quiz.
		if ( false !== $existing_quiz_key ) {

			// Prevent multiple submissions, when disabled.
			if ( ! $block_attributes['multipleSubmissions'] ) {
				return;
			}

			$existing_results[ $existing_quiz_key ]['percent']             = $percent_correct;
			$existing_results[ $existing_quiz_key ]['counts']['correct']   = $correct_count;
			$existing_results[ $existing_quiz_key ]['counts']['incorrect'] = $incorrect_count;

			update_user_meta( $user_id, 'quiz_results', $existing_results );

			return;
		}

		$results['quiz_id']             = $quiz_id;
		$results['percent']             = $percent_correct;
		$results['date']                = strtotime( 'now' );
		$results['counts']['correct']   = $correct_count;
		$results['counts']['incorrect'] = $incorrect_count;

		$existing_results[] = $results;

		update_option( 'etest_results', $results );
		update_option( 'etest_existing_results', $existing_results );

		update_user_meta( $user_id, 'quiz_results', $existing_results );

	}

}

new Quiz_Blocks_Submission_Handler();
