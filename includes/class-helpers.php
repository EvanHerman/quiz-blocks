<?php
/**
 * Quiz Blocks Helpers
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

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

		if ( empty( $parts ) ) {
			return $last;
		} else {
			return join( ', ', $parts ) . ' and ' . $last;
		}

	}

}
