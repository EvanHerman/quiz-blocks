<?php
/**
 * Quiz Blocks View Submission Page
 *
 * @package Quiz_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * Quiz_Blocks_View_Submission class.
 */
class Quiz_Blocks_View_Submission {

	/**
	 * Helpers class.
	 *
	 * @var class
	 */
	private $helpers;

	/**
	 * Quiz_Blocks_View_Submission constructor.
	 */
	public function __construct() {

		$this->helpers = new Quiz_Blocks_Helpers();

		add_action( 'admin_init', array( $this, 'prevent_direct_access' ) );

		add_action( 'admin_menu', array( $this, 'register_view_submission_page' ) );

		add_action(
			'admin_head',
			function() {
				remove_submenu_page( 'edit.php?post_type=quiz', 'view-submission' );
			}
		);

	}

	/**
	 * Prevent direct access to the single submission page.
	 */
	public function prevent_direct_access() {

		global $pagenow;

		$page    = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		$quiz_id = filter_input( INPUT_GET, 'quiz', FILTER_SANITIZE_NUMBER_INT );
		$user_id = filter_input( INPUT_GET, 'user', FILTER_SANITIZE_NUMBER_INT );

		if ( ! $page || 'view-submission' !== $page || ! $quiz_id ) {

			return;

		}

		$quiz = get_post( $quiz_id );

		if ( null === $quiz || 'quiz' !== $quiz->post_type || 'auto-draft' === $quiz->post_status ) {

			wp_safe_redirect( admin_url( 'edit.php?post_type=quiz' ) );

			exit;

		}

	}

	/**
	 * Register the single submission view page.
	 */
	public function register_view_submission_page() {

		add_submenu_page(
			'edit.php?post_type=quiz',
			__( 'Submission', 'quiz-blocks' ),
			__( 'Submission', 'quiz-blocks' ),
			'manage_options',
			'view-submission',
			array( $this, 'submission_page' )
		);

	}

	/**
	 * Single submission view admin page.
	 * Enqueue styles.
	 *
	 * @return mixed Markup for the submission page.
	 */
	public function submission_page() {

		wp_enqueue_style(
			'quiz-blocks-styles',
			plugin_dir_url( dirname( __FILE__ ) ) . 'build/quiz/style-index.css',
			array(),
			true,
			'all'
		);

		$custom_css = '#quiz-blocks {
			max-width: 60%;
			margin: 0 auto;
		}
		#poststuff h2.quiz-title {
			font-size: 1.5rem;
			margin: 0 0 1em 0;
		}
		.postbox.submission {
			padding: 1em 0 3em 0;
		}';

		wp_add_inline_style( 'quiz-blocks-styles', $custom_css );

		$quiz_id = filter_input( INPUT_GET, 'quiz', FILTER_VALIDATE_INT );
		$user_id = filter_input( INPUT_GET, 'user', FILTER_VALIDATE_INT );

		if ( ! $quiz_id || ! $user_id ) {

			printf(
				'<h2>%s</h2>',
				esc_html__( 'An error occurred. Please go back and try again.', 'quiz-blocks' )
			);

			return;

		}

		$quiz_results = get_post_meta( $quiz_id, 'results', true );

		$user_submission_key = array_search( $user_id, array_column( $quiz_results, 'user_id' ), true );

		if ( false === $user_submission_key ) {

			printf(
				'<h2>%s</h2>',
				esc_html__( 'User submission not found for this quiz. Please go back and try again.', 'quiz-blocks' )
			);

			return;

		}

		$quiz_name = get_the_title( $quiz_id );

		?>

		<div class="wrap">

			<div id="poststuff">

				<div id="post-body" class="metabox-holder columns-2">

					<!-- main content -->
					<div id="post-body-content">

						<div class="meta-box-sortables ui-sortable postbox submission">

							<?php $this->render_quiz_snapshot( $quiz_name, $quiz_results[ $user_submission_key ] ); ?>

						</div>
						<!-- .meta-box-sortables .ui-sortable -->

					</div>
					<!-- post-body-content -->

					<?php $this->submission_page_sidebar( $quiz_results[ $user_submission_key ] ); ?>

				</div>
				<!-- #post-body .metabox-holder .columns-2 -->

				<br class="clear">
			</div>
			<!-- #poststuff -->

		</div> <!-- .wrap -->

		<?php

	}

	/**
	 * Render the quiz snapshot.
	 *
	 * @param string $quiz_name             The name of the quiz that is being submitted.
	 * @param array  $user_submission_data  The user submitted data.
	 *
	 * @return mixed Markup for the quiz snapshot.
	 */
	private function render_quiz_snapshot( $quiz_name, $user_submission_data ) {

		?>

		<div id="quiz-blocks">

			<h2 class="quiz-title"><?php echo esc_html( $quiz_name ); ?></h2>

			<form id="quiz-blocks-quiz">

				<?php

				foreach ( $user_submission_data['snapshot'] as $question_index => $question_block ) {

					$question = $question_block['question'];
					$answers  = $question_block['answers'];

					$correct_class = ( 'correct' === $user_submission_data['results'][ $question_index ] ) ? 'correct' : 'incorrect';

					?>

						<div class="question">
							<p>
								<strong>
									<em><?php echo esc_html( $question ); ?></em>
								</strong>
							</p>
							<div class="answers <?php echo esc_attr( $correct_class ); ?>">
							<?php

							foreach ( $answers as $index => $answer ) {

								printf(
									'<div class="answer">
												<input type="radio" required disabled="disabled" %2$s>
												<label>%3$s</label>
											</div>',
									esc_attr( $index ),
									checked( $user_submission_data['user_answers'][ $question_index ], $index, false ),
									wp_kses_post( $answer )
								);

							}

							?>
							</div>
						</div>

						<?php

				}

				?>

			</form>

		</div>

		<?php

	}

	/**
	 * Single submission page sidebar.
	 *
	 * @param array $user_submission_data The user submission data array.
	 *
	 * @return mixed Markup for the submission page sidebar.
	 */
	private function submission_page_sidebar( $user_submission_data ) {

		$user_id   = $user_submission_data['user_id'];
		$user_data = get_userdata( $user_id );

		$quiz_id = filter_input( INPUT_GET, 'quiz', FILTER_SANITIZE_NUMBER_INT );

		$percent_correct = $user_submission_data['percent'];

		$time_taken = $this->helpers->seconds_to_time( $user_submission_data['time_taken'] );

		$date_string = sprintf(
			/* translators: %1$s is the date the quiz was submitted. %2$s is the time the quiz was submitted. */
			__( '%1$s at %2$s', 'quiz-blocks' ),
			date_i18n( get_option( 'date_format' ), strtotime( $user_submission_data['date'] ) ),
			date_i18n( get_option( 'time_format' ), strtotime( $user_submission_data['date'] ) )
		);

		$delete_submission_url = wp_nonce_url(
			add_query_arg(
				array(
					'quiz-blocks-action' => 'trash-submission',
					'quiz-id'            => $quiz_id,
					'user-id'            => $user_id,
				),
				sprintf(
					admin_url( 'edit.php?post_type=quiz&page=view-submissions&quiz=%s' ),
					$quiz_id
				)
			),
			'trash-submission'
		);

		?>

		<style type="text/css">
		a.button.button-secondary.delete {
			color: #721c24;
			background-color: #f8d7da;
			border-color: #aa8084;
		}

			a.button.button-secondary.delete:hover {
				background-color: #f5cace;
			}
		</style>

		<!-- sidebar -->
		<div id="postbox-container-1" class="postbox-container">

			<div class="meta-box-sortables">

				<div class="postbox">

					<h2><?php esc_html_e( 'Submission Info.', 'quiz-blocks' ); ?></h2>

					<div class="inside">

						<ul>
							<li><?php printf( /* translators: %s is the users display name. */ esc_html__( 'User: %s', 'quiz-blocks' ), esc_html( $user_data->display_name ) ); ?></li>
							<li><?php printf( /* translators: %s is the users email address. */ esc_html__( 'User Email: %s', 'quiz-blocks' ), sprintf( '<a href="mailto: %1$s">%1$s</a>', wp_kses_post( $user_data->user_email ) ) ); ?></li>
							<li><?php printf( /* translators: %s is the percent correct for the quiz. */ esc_html__( 'Percent Correct: %s', 'quiz-blocks' ), esc_html( $percent_correct ) ); ?>%</li>
							<li><?php printf( /* translators: %s is the date the quiz was submitted. */ esc_html__( 'Submitted: %s', 'quiz-blocks' ), esc_html( $date_string ) ); ?></li>
							<li><?php printf( /* translators: %s is the amount of time taken. */ esc_html__( 'Time Taken: %s', 'quiz-blocks' ), esc_html( $time_taken ) ); ?></li>
						</ul>

						<a href="<?php echo esc_url( $delete_submission_url ); ?>" onclick="return confirm( '<?php esc_attr_e( 'Are you sure you want to delete this quiz submission? This cannot be undone.', 'quiz-blocks' ); ?>' )" class="button button-secondary delete"><?php esc_html_e( 'Delete Submission', 'quiz-blocks' ); ?></a>

					</div>
					<!-- .inside -->

				</div>
				<!-- .postbox -->

			</div>
			<!-- .meta-box-sortables -->

		</div>
		<!-- #postbox-container-1 .postbox-container -->

		<?php

	}

	/**
	 * Clear a submission for a quiz.
	 */
	public function clear_quiz_submission() {

		if ( ! isset( $_GET['quiz-blocks-action'] ) || ! isset( $_GET['user-id'] ) || ! isset( $_GET['quiz-id'] ) || ! isset( $_GET['_wpnonce'] ) ) {

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

		$update_quiz_results = update_post_meta( $quiz_id, 'results', array_values( $quiz_results ) );

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

}

new Quiz_Blocks_View_Submission();
