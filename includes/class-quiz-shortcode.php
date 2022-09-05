<?php
/**
 * Quiz Blocks Quiz Shortcode
 *
 * @package Quiz_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * Quiz_Blocks_Quiz_Shortcode class.
 *
 * Render a quiz on a page or post using the shortcode.
 */
class Quiz_Blocks_Quiz_Shortcode {

	/**
	 * Helpers class instance.
	 *
	 * @var class
	 */
	private $helpers;

	/**
	 * Quiz Blocks Quiz Shortcode Class Constructor
	 */
	public function __construct() {

		$this->helpers = new Quiz_Blocks_Helpers();

		add_shortcode( 'quiz', array( $this, 'quiz_shortcode' ) );

	}

	/**
	 * Render the Quiz
	 *
	 * @param array $atts Shortcode attributes array.
	 *
	 * @return mixed Markup for the quiz block.
	 */
	public function quiz_shortcode( $atts ) {

		$atts = shortcode_atts(
			array(
				'id'                  => 0,
				'requireLogin'        => true,
				'useRankings'         => true,
				'multipleSubmissions' => true,
				'showTitle'           => true,
				'showAnswers'         => true,
				'showResults'         => true,
			),
			$atts
		);

		if ( ! $atts['id'] ) {

			return;

		}

		wp_enqueue_style(
			'quiz-blocks-styles',
			plugin_dir_url( dirname( __FILE__ ) ) . basename( __DIR__ ) . '/../../build/quiz/style-index.css',
			array(),
			true,
			'all'
		);

		$atts['quizID'] = $atts['id'];

		return $this->helpers->render_quiz( $atts );

	}

}

new Quiz_Blocks_Quiz_Shortcode();
