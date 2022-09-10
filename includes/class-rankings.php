<?php
/**
 * Quiz Blocks Rankings
 *
 * @package Quiz_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * Quiz_Blocks_Rankings class.
 *
 * Handles ranking functionality.
 */
class Quiz_Blocks_Rankings {

	/**
	 * Quiz_Blocks_Rankings constructor.
	 */
	public function __construct() {

		add_action( 'wp_ajax_get_rankings', array( $this, 'get_rankings' ), PHP_INT_MAX );

	}

	/**
	 * Retreive the rankings for a quiz.
	 */
	public function get_rankings() {

		if ( ! isset( $_GET['quizID'] ) ) { // phpcs:ignore

			wp_send_json_error( 'Missing Quiz ID.', 400 );

		}

		$quiz_id = filter_input( INPUT_GET, 'quizID', FILTER_VALIDATE_INT );

		// Get the quiz post meta.
		$rankings = get_post_meta( $quiz_id, 'results', true );

		if ( ! $rankings ) {

			$rankings = array();

		}

		if ( false !== $rankings ) {

			foreach ( $rankings as $index => $ranked_user_data ) {

				$user_data = get_userdata( $ranked_user_data['user_id'] );

				$rankings[ $index ]['display_name'] = $user_data->display_name;
				$rankings[ $index ]['date']         = date_i18n( get_option( 'date_format' ), $ranked_user_data['date'] );

			}
		}

		// Sort rankings by percent correct.
		$keys = array_column( $rankings, 'percent' );
		array_multisort( $keys, SORT_DESC, $rankings );

		wp_send_json_success(
			array(
				'quizID'   => $quiz_id,
				'rankings' => $this->rankings_markup( $rankings ),
			)
		);

	}

	/**
	 * Generate the rankings markup.
	 *
	 * @param array $rankings Rankings array.
	 */
	private function rankings_markup( $rankings ) {

		ob_start();

		if ( empty( $rankings ) ) {

			?>

			<h4 style="text-align: center;"><?php esc_html_e( 'No one has submitted this quiz yet.', 'quiz-blocks' ); ?></h4>
			<h5 style="text-align: center;"><?php esc_html_e( 'You could be the first!', 'quiz-blocks' ); ?></h5>

			<?php

			return ob_get_clean();

		}

		?>

		<table class="styled-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'quiz-blocks' ); ?></th>
					<th><?php esc_html_e( 'Percent Correct', 'quiz-blocks' ); ?></th>
					<th><?php esc_html_e( 'Number Correct', 'quiz-blocks' ); ?></th>
					<th><?php esc_html_e( 'Date', 'quiz-blocks' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $rankings as $index => $user ) {
					printf(
						'<tr>
							<td>%1$s%2$s</td>
							<td>%3$s</td>
							<td>%4$s</td>
							<td>%5$s</td>
						</tr>',
						wp_kses_post( $this->get_medal( $index ) ),
						esc_html( $user['display_name'] ),
						esc_html( $user['percent'] ) . '%',
						esc_html( $user['counts']['correct'] ),
						esc_html( $user['date'] )
					);
				}
				?>
			</tbody>
		</table>

		<?php

		return ob_get_clean();

	}

	/**
	 * Retreive the medal image for the rankings.
	 *
	 * @param integer $place The place of the person to retreive a medal for.
	 *
	 * @return mixed <img> tag for the medal.
	 */
	private function get_medal( $place ) {

		$icon = false;
		$alt  = '';

		switch ( $place ) {
			default:
				break;

			case 0:
				$icon = plugin_dir_url( dirname( __FILE__ ) ) . 'src/img/1st-place-medal.png';
				$alt  = __( 'First Place', 'quiz-blocks' );
				break;

			case 1:
				$icon = plugin_dir_url( dirname( __FILE__ ) ) . 'src/img/2nd-place-medal.png';
				$alt  = __( 'Second Place', 'quiz-blocks' );
				break;

			case 2:
				$icon = plugin_dir_url( dirname( __FILE__ ) ) . 'src/img/3rd-place-medal.png';
				$alt  = __( 'Third Place', 'quiz-blocks' );
				break;
		}

		return ! $icon ? '' : '<img class="medal" src="' . $icon . '" alt="' . $alt . '" />';

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

		$correct_count   = isset( $results['counts']['correct'] ) ? $results['counts']['correct'] : 0;
		$percent_corrent = ( $correct_count / count( $results['results'] ) ) * 100;

		$results['user_id'] = get_current_user_id();
		$results['percent'] = $percent_corrent;
		$results['date']    = strtotime( 'now' );

		$existing_results[] = $results;

		update_post_meta( $quiz_id, 'results', $existing_results );

	}

}

new Quiz_Blocks_Rankings();
