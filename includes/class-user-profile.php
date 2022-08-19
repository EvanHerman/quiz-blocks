<?php
/**
 * Quiz Blocks User Profile Section
 *
 * @package Quiz_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * Quiz_Blocks_User_Profile class.
 */
class Quiz_Blocks_User_Profile {

	/**
	 * Quiz_Blocks_User_Profile constructor.
	 */
	public function __construct() {

		add_action( 'show_user_profile', array( $this, 'user_quiz_section' ) );

	}

	/**
	 * Render the quiz results table on the user profile.
	 *
	 * @param WP_User $user WordPress user object.
	 *
	 * @return mixed Markup for the user profile quiz section.
	 */
	public function user_quiz_section( WP_User $user ) {

		$quiz_results = get_user_meta( $user->ID, 'quiz_results', true );

		?>
		<h2><?php esc_html_e( 'Quiz Results', 'quiz-blocks' ); ?></h2>
		<table class="form-table">
			<?php $this->show_user_results_table( $quiz_results ); ?>
		</table>
		<?php

	}

	/**
	 * [show_user_results_table description]
	 *
	 * @param array $quiz_results The quiz results array.
	 *
	 * @return mixed Markup for the results table.
	 */
	private function show_user_results_table( $quiz_results ) {

		if ( ! $quiz_results ) {

			printf(
				'<tr>
					<th>
						<label>%s</label>
					</th>
					<td>
					</td>
				</tr>',
				esc_html__( 'No Quizzes Taken', 'quiz-blocks' )
			);

			return;

		}

		foreach ( $quiz_results as $results ) {
			$quiz_name = get_the_title( $results['quiz_id'] );
			printf(
				'<tr>
					<th>
						<label>%1$s</label>
					</th>
					<td>
						%2$s
					</td>
				</tr>',
				esc_html( $quiz_name ),
				esc_html( $results['percent'] . '%' )
			);
		}

	}

}

new Quiz_Blocks_User_Profile();
