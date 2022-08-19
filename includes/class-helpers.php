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

}
