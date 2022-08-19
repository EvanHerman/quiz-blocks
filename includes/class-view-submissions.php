<?php
/**
 * Quiz Blocks View Submissions Page
 *
 * @package Quiz_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * Quiz_Blocks_View_Submissions class.
 *
 * Actions for quiz submissions.
 */
class Quiz_Blocks_View_Submissions {

	/**
	 * Quiz_Blocks_View_Submissions constructor.
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'prevent_direct_access' ) );

		add_action( 'admin_menu', array( $this, 'register_view_submissions_page' ) );

		add_action(
			'admin_head',
			function() {
				remove_submenu_page( 'edit.php?post_type=quiz', 'view-submissions' );
			}
		);

		add_action( 'admin_init', array( $this, 'clear_quiz_submission' ) );

		add_action( 'admin_notices', array( $this, 'clear_submission_admin_notices' ) );

		add_filter( 'removable_query_args', array( $this, 'removable_query_args' ) );

	}

	/**
	 * Prevent direct access to the view-submissions admin page.
	 */
	public function prevent_direct_access() {

		global $pagenow;

		$page    = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		$quiz_id = filter_input( INPUT_GET, 'quiz', FILTER_SANITIZE_NUMBER_INT );

		if ( ! $page || 'view-submissions' !== $page ) {

			return;

		}

		$quiz = get_post( $quiz_id );

		if ( null === $quiz || 'quiz' !== $quiz->post_type || 'auto-draft' === $quiz->post_status ) {

			wp_safe_redirect( admin_url( 'edit.php?post_type=quiz' ) );

			exit;

		}

	}

	/**
	 * Register the view submissions admin page.
	 */
	public function register_view_submissions_page() {

		add_submenu_page(
			'edit.php?post_type=quiz',
			__( 'Submissions', 'quiz-blocks' ),
			__( 'Submissions', 'quiz-blocks' ),
			'manage_options',
			'view-submissions',
			array( $this, 'submissions_page' )
		);

	}

	/**
	 * Render the submissions page markup.
	 *
	 * @return mixed Markup for the view submissions page.
	 */
	public function submissions_page() {

		$submissions_table = new Quiz_Blocks_Submissions_Table();

		$submissions_table->table();

	}

	/**
	 * Clear a submission for a quiz.
	 */
	public function clear_quiz_submission() {

		if ( ! isset( $_GET['quiz-blocks-action'] ) || ! isset( $_GET['user-id'] ) || ! isset( $_GET['quiz-id'] ) || ! isset( $_GET['_wpnonce'] ) ) { // phpcs:ignore

			return;

		}

		$action  = filter_input( INPUT_GET, 'quiz-blocks-action', FILTER_SANITIZE_STRING );
		$user_id = filter_input( INPUT_GET, 'user-id', FILTER_VALIDATE_INT );
		$quiz_id = filter_input( INPUT_GET, 'quiz-id', FILTER_VALIDATE_INT );
		$nonce   = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $nonce, 'trash-submission' ) ) {

			return;

		}

		$quiz_results = get_post_meta( $quiz_id, 'results', true );

		$user_submission_key = array_search( $user_id, array_column( $quiz_results, 'user_id' ), true );

		if ( false === $user_submission_key ) {

			wp_safe_redirect(
				add_query_arg(
					array(
						'submission-deleted' => false,
						'user-id'            => $user_id,
					),
					sprintf(
						admin_url( 'edit.php?post_type=quiz&page=view-submissions&quiz=%s' ),
						$quiz_id
					)
				),
			);

			exit;

		}

		unset( $quiz_results[ $user_submission_key ] );

		$update_quiz_results = update_post_meta( $quiz_id, 'results', $quiz_results );

		wp_safe_redirect(
			add_query_arg(
				array(
					'submission-deleted' => $update_quiz_results,
					'user-id'            => $user_id,
				),
				sprintf(
					admin_url( 'edit.php?post_type=quiz&page=view-submissions&quiz=%s' ),
					$quiz_id
				)
			),
		);

		exit;

	}

	/**
	 * Clear all submissions for a quiz.
	 */
	public function clear_all_quiz_submissions() {

		if ( ! isset( $_GET['quiz-blocks-action'] ) || ! isset( $_GET['quiz-id'] ) || ! isset( $_GET['_wpnonce'] ) ) {

			return;

		}

		$action  = filter_input( INPUT_GET, 'quiz-blocks-action', FILTER_SANITIZE_STRING );
		$quiz_id = filter_input( INPUT_GET, 'quiz-id', FILTER_VALIDATE_INT );
		$nonce   = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $nonce, 'trash-submissions' ) ) {

			return;

		}

		$quiz_results = get_post_meta( $quiz_id, 'results', true );

		$update_quiz_results = update_post_meta( $quiz_id, 'results', array() );

		wp_safe_redirect(
			add_query_arg(
				array(
					'submissions-deleted' => $update_quiz_results,
					'quiz-id'             => $quiz_id,
				),
				sprintf(
					admin_url( 'edit.php?post_type=quiz&page=view-submissions&quiz=%s' ),
					$quiz_id
				)
			),
		);

		exit;

	}

	/**
	 * Admin notice when a submission is deleted.
	 *
	 * @return mixed Markup for the admin notice.
	 */
	public function clear_submission_admin_notices() {

		global $pagenow;

		$page               = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		$submission_deleted = filter_input( INPUT_GET, 'submission-deleted', FILTER_VALIDATE_BOOLEAN );
		$user_id            = filter_input( INPUT_GET, 'user-id', FILTER_SANITIZE_NUMBER_INT );

		if ( ! $page || 'view-submissions' !== $page || ! isset( $_GET['submission-deleted'] ) || ! $user_id ) { // phpcs:ignore

			return;

		}

		$class = sprintf(
			'notice notice-%s',
			$submission_deleted ? 'success' : 'error'
		);

		$user = get_userdata( $user_id );

		$message = $submission_deleted ? sprintf( /* translators: %s is the user display name. */ __( 'Submission deleted for %s.', 'quiz-blocks' ), '<strong> ' . $user->display_name . '</strong>' ) : __( 'Submission not deleted. An error occurred.', 'quiz-blocks' );

		printf(
			'<div class="%1$s">
				<p>%2$s</p>
			</div>',
			esc_attr( $class ),
			wp_kses_post( $message )
		);

	}

	/**
	 * Remove our quiz blocks query args from the admin URL.
	 *
	 * @param array $removable_query_args Default core removable query args.
	 *
	 * @return array Filtered removable query args.
	 */
	public function removable_query_args( $removable_query_args ) {

		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

		if ( ! $page || ! isset( $_GET['submission-deleted'] ) ) { // phpcs:ignore

			return $removable_query_args;

		}

		$removable_query_args[] = 'submission-deleted';
		$removable_query_args[] = 'user-id';

		return $removable_query_args;

	}

}

new Quiz_Blocks_View_Submissions();
