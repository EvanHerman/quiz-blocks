<?php
/**
 * Quiz Blocks Helpers
 *
 * @package Quiz_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * Quiz_Blocks_Helpers helpers class.
 *
 * Helper functions used throughout Quiz Blocks.
 */
class Quiz_Blocks_Helpers {

	/**
	 * Convert a millisecond value into a human readable time format.
	 *
	 * @param int $input_milliseconds Milliseconds value.
	 *
	 * @return string Human readable format for the total time.
	 */
	public function seconds_to_time( $input_milliseconds ) {

		// Convert milliseconds to seconds.
		$duration = $input_milliseconds / 1000;

		$periods = array(
			'day'    => 86400,
			'hour'   => 3600,
			'minute' => 60,
			'second' => 1,
		);

		$parts = array();

		foreach ( $periods as $name => $dur ) {
			$div = (int) floor( $duration / $dur );

			if ( 0 === $div ) {
				continue;
			} else {
				if ( 1 === $div ) {
					$parts[] = $div . ' ' . $name;
				} else {
					$parts[] = $div . ' ' . $name . 's';
				}
			}
			$duration %= $dur;
		}

		$last = array_pop( $parts );

		if ( empty( $parts ) && empty( $last ) ) {
			return '1 second';
		}

		if ( empty( $parts ) ) {
			return $last;
		} else {
			return join( ', ', $parts ) . ' and ' . $last;
		}

	}

	/**
	 * Retrieve quiz attributes from blocks for a given quiz ID.
	 *
	 * @param integer $quiz_id The quiz ID to pull attributes for.
	 * @param array   $blocks  Post content block array.
	 *
	 * @return array The attributes for the specified quiz, else empty.
	 */
	public function get_block_attributes( $quiz_id, $blocks ) {

		if ( ! $quiz_id || empty( $blocks ) ) {
			return array();
		}

		foreach ( $blocks as $block ) {
			if ( ! isset( $block['attrs']['quizID'] ) || $quiz_id !== $block['attrs']['quizID'] ) {
				continue;
			}
			return $block['attrs'];
		}

		return array();

	}

	/**
	 * Render the quiz markup from an array of attributes.
	 *
	 * @param array $atts Attributes array.
	 *
	 * @return mixed Markup for the quiz.
	 */
	public function render_quiz( $atts ) {

		if ( 0 === $atts['quizID'] ) {

			return;

		}

		$quiz_content = get_post( $atts['quizID'] );

		if ( 'publish' !== $quiz_content->post_status ) {

			return;

		}

		// Strip HTML comments from the content.
		$quiz = ! is_null( $quiz_content ) ? html_entity_decode( preg_replace( '/<!--(.|\s)*?-->/', '', $quiz_content->post_content ) ) : false;

		if ( ! $quiz ) {

			return;

		}

		$is_logged_in = is_user_logged_in();

		$classes = array();

		ob_start();

		print( '<div id="quiz-blocks">' );

		printf(
			'<h2 class="quiz-title">%s</h2>',
			esc_html( $quiz_content->post_title )
		);

		if ( $atts['requireLogin'] && $is_logged_in && $atts['useRankings'] ) {

			printf(
				'<button class="show-rankings button button_sliding_bg" data-quizid="%1$s">%2$s</button>
				<div class="quiz-%1$s-rankings quiz-blocks-rankings"><img src="%3$s" class="preloader" /></div>',
				esc_attr( $atts['quizID'] ),
				esc_html__( 'View Quiz Rankings', 'quiz-blocks' ),
				esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'src/img/preloader.svg' )
			);

		}

		if ( $atts['requireLogin'] && ! $is_logged_in ) {
			$not_logged_in_text = apply_filters(
				'quiz_blocks_not_logged_in_text',
				__( 'Please log in to access this quiz.', 'quiz-blocks' )
			);

			$classes[] = 'not-logged-in';

			printf(
				'<div class="login-notice">
					<h4>%1$s</h4>
					<a href="%2$s" class="button_sliding_bg button login">%3$s</a>
					%4$s
				</div>',
				esc_html( $not_logged_in_text ),
				esc_url( wp_login_url() ),
				esc_html__( 'Login', 'quiz-blocks' ),
				get_option( 'users_can_register' ) ? sprintf(
					'<a href="%1$s" class="button_sliding_bg button login">%2$s</a>',
					esc_url( wp_registration_url() ),
					esc_html__( 'Register', 'quiz-blocks' )
				) : ''
			);

			// Obfuscate the questions and answers.
			$quiz = $this->obfuscate_questions( $quiz );
		}

		if ( $atts['requireLogin'] && ! $atts['multipleSubmissions'] && $is_logged_in && $this->has_user_taken_quiz( $atts['quizID'] ) ) {
			$classes[] = 'multiple-submissions-disabled';
			printf(
				'<div class="multiple-submissions-disabled-notice">
					<h4>%1$s</h4>
					<a href="%1$s" class="show-existing-results button_sliding_bg button" data-quizid="%2$s">%3$s</a>
				</div>',
				esc_html__( 'You have already submitted this quiz.', 'quiz-blocks' ),
				esc_attr( $atts['quizID'] ),
				esc_html__( 'View Results', 'quiz-blocks' )
			);

			// Obfuscate the questions and answers.
			$quiz = $this->obfuscate_questions( $quiz );
		}

		?>

		<form id="quiz-blocks-quiz" data-quizid="<?php echo esc_attr( $atts['quizID'] ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php echo $quiz; // phpcs:ignore ?>
			<input class="button_sliding_bg button" type="submit" name="submit" id="submit" value="<?php esc_html_e( 'Submit', 'quiz-blocks' ); ?>" />
		</form>

		<button class="show-results button button_sliding_bg" data-quizid="<?php echo esc_attr( $atts['quizID'] ); ?>"><?php esc_html_e( 'View Results', 'quiz-blocks' ); ?></button>

		<?php

		if ( $is_logged_in && current_user_can( 'administrator' ) ) {

				printf(
					'<a href="%1$s" style="text-decoration: underline; color: #21759b;">%2$s</a>',
					esc_url( admin_url( sprintf( 'post.php?post=%s&action=edit', $atts['quizID'] ) ) ),
					esc_html__( 'Edit This Quiz', 'quiz-blocks' )
				);

		}

		print( '</div>' );

		printf(
			'<div class="quiz-%1$s-results quiz-blocks-results">
				<h2>%2$s</h2>
				<p>%3$s</p>
				<p>%4$s</p>
			</div>',
			esc_attr( $atts['quizID'] ),
			esc_html__( 'Congratulations', 'quiz-blocks' ),
			sprintf(
				/* translators: %s is the percent correct <span> container. */
				esc_html__( 'Percent Correct: %s', 'quiz-blocks' ),
				'<span class="percent-correct"></span>'
			),
			sprintf(
				/* translators: %s is the number correct <span> container. */
				esc_html__( 'Number Correct: %s', 'quiz-blocks' ),
				'<span class="number-correct"></span>'
			)
		);

		return ob_get_clean();

	}

	/**
	 * Obfuscate the HTML markup for the quiz block.
	 *
	 * @param mixed $quiz_markup The markup for the quiz block.
	 *
	 * @return mixed Quiz block markup with obfuscated question/answer text.
	 */
	public function obfuscate_questions( $quiz_markup ) {

		$text      = 'Sociosqu consectetuer. Placerat nisl, hendrerit. Morbi lobortis vitae non mattis pellentesque hendrerit ultrices ante neque dui. Torquent inceptos. Penatibus est eu libero non enim class auctor purus a netus curae; purus feugiat ultricies. Adipiscing nec cubilia metus convallis, nunc. Ridiculus placerat praesent a. Taciti litora sociis congue eu ullamcorper egestas ac adipiscing orci. Cras integer porttitor et convallis. Enim nisi nulla luctus Bibendum Gravida ut nonummy montes, nonummy bibendum pharetra malesuada. Pretium luctus suspendisse. Malesuada scelerisque nec pretium class hendrerit hendrerit nisi iaculis. Netus enim auctor. Tellus aliquam magna feugiat aenean vestibulum sapien pharetra laoreet ac volutpat venenatis curabitur sapien.';
		$word_list = explode( ' ', $text );

		// obfuscate question text.
		shuffle( $word_list );
		$quiz_markup = preg_replace( '/(<strong.*?>).*?(<\/strong>)/', '$1' . implode( ' ', array_slice( $word_list, 0, 3 ) ) . '$2', $quiz_markup );

		// obfuscate answer text.
		shuffle( $word_list );
		$quiz_markup = preg_replace( '/(<label.*?>).*?(<\/label>)/', '$1' . implode( ' ', array_slice( $word_list, 0, 3 ) ) . '$2', $quiz_markup );

		return $quiz_markup;

	}

	/**
	 * Determine if a user has taken a quiz already.
	 *
	 * @param integer $quiz_id The quiz ID to check.
	 *
	 * @return boolean True when the user has taken the quiz, else false.
	 */
	public function has_user_taken_quiz( $quiz_id ) {

		$existing_results = get_user_meta( get_current_user_id(), 'quiz_results', true );

		if ( ! $existing_results ) {

			return false;

		}

		// Determine if user already submitted results to this quiz.
		$existing_quiz_key = array_search( $quiz_id, array_column( $existing_results, 'quiz_id' ), true );

		return false !== $existing_quiz_key;

	}

}
