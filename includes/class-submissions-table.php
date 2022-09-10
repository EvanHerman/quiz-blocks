<?php
/**
 * Quiz Blocks Submissions Table
 *
 * @package Quiz_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * Quiz_Blocks_Submissions_Table class.
 *
 * Renders the markup for the submissions table in the admin.
 */
class Quiz_Blocks_Submissions_Table {

	/**
	 * Helpers class instance.
	 *
	 * @var class
	 */
	private $helpers;

	/**
	 * Quiz ID.
	 *
	 * @var integer
	 */
	private $quiz_id;

	/**
	 * Submissions array.
	 *
	 * @var array
	 */
	private $submissions;

	/**
	 * Quiz_Blocks_Submissions_Table() constructor.
	 */
	public function __construct() {

		$this->helpers = new Quiz_Blocks_Helpers();

		$this->quiz_id     = filter_input( INPUT_GET, 'quiz', FILTER_VALIDATE_INT );
		$this->submissions = get_post_meta( $this->quiz_id, 'results', true );

	}

	/**
	 * Render the markup for the submissions table.
	 *
	 * @return mixed Markup for the submissions table.
	 */
	public function table() {

		$quiz_name = get_the_title( $this->quiz_id );
		$quiz_name = empty( $quiz_name ) ? __( '(no name)', 'quiz-blocks' ) : $quiz_name;

		$total_submissions   = empty( $this->submissions ) ? 0 : count( $this->submissions );
		$all_percent_correct = wp_list_pluck( $this->submissions, 'percent' );

		$average_percent_correct = empty( $this->submissions ) ? 'N/A' : ( ( array_sum( $all_percent_correct ) / $total_submissions ) . '%' );

		$all_completion_times = wp_list_pluck( $this->submissions, 'time_taken' );

		$average_completion_time = empty( $this->submissions ) ? 0 : ( array_sum( $all_completion_times ) / $total_submissions );
		$average_completion_time = ( 0 === $average_completion_time ) ? __( 'N/A', 'quiz-blocks' ) : $this->helpers->seconds_to_time( $average_completion_time );

		$delete_all_submissions_url = wp_nonce_url(
			add_query_arg(
				array(
					'quiz-blocks-action' => 'trash-submissions',
					'quiz-id'            => $this->quiz_id,
				),
				sprintf(
					admin_url( 'edit.php?post_type=quiz&page=view-submissions&quiz=%s' ),
					$this->quiz_id
				)
			),
			'trash-submissions'
		);

		?>

		<style type="text/css">
		a.button.button-secondary.delete {
			color: #721c24;
			background-color: #fff8f9;
			border-color: #aa8084;
		}

			a.button.button-secondary.delete:hover {
				background-color: #fdf0f2;
			}
		</style>

		<div class="wrap">

			<h1>
				<?php
					printf(
						/* translators: %s is the name of the quiz. */
						esc_html__( '%s Submissions', 'quiz-blocks' ),
						esc_html( $quiz_name )
					);
				?>
			</h1>

			<div id="poststuff">

				<div id="post-body" class="metabox-holder columns-2">

					<!-- main content -->
					<div id="post-body-content">

						<div class="meta-box-sortables ui-sortable">

							<table class="widefat fixed" cellspacing="0">

								<thead>
									<tr>
										<th scope="col" id="title" class="manage-column column-name column-primary sortable desc">
											<a href="<?php echo esc_url( sprintf( admin_url( 'edit.php?post_type=quiz&page=view-submissions&quiz=%s&orderby=name&order=asc' ), esc_attr( $quiz_id ) ) ); ?>">
												<span>Name</span>
												<span class="sorting-indicator"></span>
											</a>
										</th>
										<th scope="col" id="author" class="manage-column column-percent"><?php esc_html_e( 'Percent Correct', 'quiz-blocks' ); ?></th>
										<th scope="col" id="author" class="manage-column column-date"><?php esc_html_e( 'Date', 'quiz-blocks' ); ?></th>
										<th scope="col" id="author" class="manage-column column-time-taken"><?php esc_html_e( 'Time Taken', 'quiz-blocks' ); ?></th>
									</tr>
								</thead>

								<tfoot>
									<tr>
										<th scope="col" id="title" class="manage-column column-name column-primary sortable desc">
											<a href="<?php echo esc_url( sprintf( admin_url( 'edit.php?post_type=quiz&page=view-submissions&quiz=%s&orderby=name&order=asc' ), esc_attr( $quiz_id ) ) ); ?>">
												<span>Name</span>
												<span class="sorting-indicator"></span>
											</a>
										</th>
										<th scope="col" id="author" class="manage-column column-percent"><?php esc_html_e( 'Percent Correct', 'quiz-blocks' ); ?></th>
										<th scope="col" id="author" class="manage-column column-date"><?php esc_html_e( 'Date', 'quiz-blocks' ); ?></th>
										<th scope="col" id="author" class="manage-column column-time-taken"><?php esc_html_e( 'Time Taken', 'quiz-blocks' ); ?></th>
									</tr>
								</tfoot>

								<tbody>
									<?php $this->get_submission_row( $this->submissions, $this->quiz_id ); ?>
								</tbody>

							</table>

						</div>
						<!-- .meta-box-sortables .ui-sortable -->

					</div>
					<!-- post-body-content -->

					<!-- sidebar -->
					<div id="postbox-container-1" class="postbox-container">

						<div class="meta-box-sortables">

							<div class="postbox">

								<h2>
									<span><?php esc_attr_e( 'Quiz Info.', 'quiz-blocks' ); ?></span>
								</h2>

								<div class="inside">
									<ul>
										<li><?php printf( /* translators: %s is the number of quiz submissions. */ esc_html__( 'Total Submissions: %s', 'quiz-blocks' ), esc_html( $total_submissions ) ); ?></li>
										<li><?php printf( /* translators: %s is the average correct percent for all submissions. */ esc_html__( 'Average Percent Correct: %s', 'quiz-blocks' ), esc_html( $average_percent_correct ) ); ?></li>
										<li><?php printf( /* translators: %s is the average time to complete the quiz for all submissions. */ esc_html__( 'Average Completion Time: %s', 'quiz-blocks' ), esc_html( $average_completion_time ) ); ?></li>
									</ul>

									<a class="button button-secondary" href="<?php echo esc_url( sprintf( admin_url( 'post.php?post=%s&action=edit' ), $quiz_id ) ); ?>" ><?php esc_html_e( 'Edit Quiz', 'quiz-blocks' ); ?></a>
									<a class="button button-secondary delete" onclick="return confirm( '<?php printf( /* translators: %s is the users display name. */ esc_attr__( 'Are you sure you want to delete all %s submissions? This cannot be undone.', 'quiz-blocks' ), esc_attr( get_the_title( $this->quiz_id ) ) ); ?>' )" href="<?php echo esc_url( $delete_all_submissions_url ); ?>" ><?php esc_html_e( 'Delete Submissions', 'quiz-blocks' ); ?></a>
								</div>
								<!-- .inside -->

							</div>
							<!-- .postbox -->

						</div>
						<!-- .meta-box-sortables -->

					</div>
					<!-- #postbox-container-1 .postbox-container -->

				</div>
				<!-- #post-body .metabox-holder .columns-2 -->

				<br class="clear">
			</div>
			<!-- #poststuff -->

		</div> <!-- .wrap -->

		<?php

	}

	/**
	 * Retreive the markup for a submission table row.
	 *
	 * @param array $submissions The submissions array.
	 * @param int   $quiz_id     The quiz ID.
	 *
	 * @return mixed Markup for the tr.
	 */
	public function get_submission_row( $submissions, $quiz_id ) {

		if ( ! $submissions ) {

			?>

			<tr>
				<td class="column-primary">
					<strong><?php esc_html_e( 'No Quiz Submissions.', 'quiz-blocks' ); ?></strong>
				</td>
			</tr>

			<?php

			return;

		}

		foreach ( $submissions as $submission ) {

			$user = get_userdata( $submission['user_id'] );

			$delete_submission_url = wp_nonce_url(
				add_query_arg(
					array(
						'quiz-blocks-action' => 'trash-submission',
						'quiz-id'            => $quiz_id,
						'user-id'            => $submission['user_id'],
					),
					sprintf(
						admin_url( 'edit.php?post_type=quiz&page=view-submissions&quiz=%s' ),
						$quiz_id
					)
				),
				'trash-submission'
			);

			$view_submission_url = add_query_arg(
				array(
					'quiz' => $quiz_id,
					'user' => $submission['user_id'],
				),
				admin_url( 'edit.php?post_type=quiz&page=view-submission' )
			);

			?>

			<tr class="iedit author-self hentry">
				<td class="name column-name has-row-actions column-primary" data-colname="Name">
					<strong>
						<?php echo esc_html( $user->display_name ); ?>
					</strong>
					<div class="row-actions">
						<span class="view">
							<a href="<?php echo esc_url( $view_submission_url ); ?>" class="view" aria-label="<?php printf( /* translators: %s is the users display name. */ esc_attr__( 'View submission for %s', 'quiz-blocks' ), esc_attr( $user->display_name ) ); ?>">
								<?php esc_html_e( 'View Submission', 'quiz-blocks' ); ?>
							</a>
						</span>
						|
						<span class="trash">
							<a href="<?php echo esc_url( $delete_submission_url ); ?>" class="submitdelete" onclick="return confirm( '<?php printf( /* translators: %s is the users display name. */ esc_attr__( 'Are you sure you want to delete the quiz submission for %s? This cannot be undone.', 'quiz-blocks' ), esc_attr( $user->display_name ) ); ?>' )" aria-label="<?php printf( /* translators: %s is the users display name. */ esc_attr__( 'Delete submission for %s', 'quiz-blocks' ), esc_attr( $user->display_name ) ); ?>">
								<?php esc_html_e( 'Delete Submission', 'quiz-blocks' ); ?>
							</a>
						</span>
					</div>
				</td>

				<td class="percent column-percent" data-colname="Percent">
					<?php echo esc_html( $submission['percent'] ); ?>%
				</td>

				<?php
					$date_string = sprintf(
						/* translators: %1$s is the date the quiz was submitted. %2$s is the time the quiz was submitted. */
						__( '%1$s at %2$s', 'quiz-blocks' ),
						date_i18n( get_option( 'date_format' ), strtotime( $submission['date'] ) ),
						date_i18n( get_option( 'time_format' ), strtotime( $submission['date'] ) )
					);
				?>

				<td class="date column-date" data-colname="Date">
					<?php echo esc_html( $date_string ); ?>
				</td>

				<td class="date column-time-taken" data-colname="Time Taken">
					<?php echo esc_html( $this->helpers->seconds_to_time( $submission['time_taken'] ) ); ?>
				</td>
			</tr>

			<?php

		}

	}

}
