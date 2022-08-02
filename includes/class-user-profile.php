<?php
/**
 * Quiz Blocks User Profile Section
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

class Quiz_Blocks_User_Profile {

	public function __construct() {

		add_action( 'show_user_profile', array( $this, 'user_quiz_section' ) );

	}

	public function user_quiz_section( WP_User $user ) {

		$quiz_results = get_user_meta( $user->ID, 'quiz_results', true );

		?>
		<h2><?php esc_html_e( 'Quiz Results', 'quiz-blocks' ); ?></h2>
		<table class="form-table">
			<?php $this->show_user_results_table( $quiz_results ); ?>
		</table>
		<?php

	}

	private function show_user_results_table( $quiz_results ) {

		if ( ! $quiz_results ) {

			print(
				'<tr>
					<th>
						<label>No Quizzes Taken</label>
					</th>
					<td>
					</td>
				</tr>'
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
