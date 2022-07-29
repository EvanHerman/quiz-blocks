<?php
/**
 * Quiz Blocks Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

class Quiz_Blocks_Blocks {

	public function __construct() {

		add_filter( 'block_categories_all',  array( $this, 'custom_block_category' ), PHP_INT_MAX, 2 );

		add_filter( 'allowed_block_types_all', array( $this, 'quiz_allowed_blocks' ), PHP_INT_MAX, 2 );

		add_action( 'enqueue_block_editor_assets', array( $this, 'disable_blocks' ), PHP_INT_MAX );

		add_action( 'enqueue_block_editor_assets', array( $this, 'quizblocks_blocks' ), PHP_INT_MAX );

	}

	public function custom_block_category( $block_categories, $editor_context ) {

		array_push(
			$block_categories,
			array(
				'slug'  => 'quiz-blocks',
				'title' => __( 'Quiz Blocks', 'quiz-blocks' ),
				'icon'  => 'forms',
			)
		);

		return $block_categories;

	}

	/**
	 * Limit the blocks allowed in the block editor for quiz post types.
	 *
	 * @param mixed $allowed_blocks Array of allowable blocks for Gutenberg Editor.
	 * @param mixed $post Gets current post type.
	 *
	 * @return mixed $allowed_blocks Returns the allowed blocks.
	 *
	 */
	public function quiz_allowed_blocks( $allowed_block_types, $editor_context ) {

		$quiz_blocks = array(
			'quizblocks/multiple-choice-question',
		);

		unset( $allowed_blocks['quizblocks/multiple-choice-question'] );

		return 'quiz' !== $editor_context->post->post_type ? $allowed_block_types : $quiz_blocks;

	}

	/**
	 * Disable the quiz blocks blocks on non quiz post types.
	 */
	public function disable_blocks() {

		global $post;

		if ( isset( $post->post_type ) && 'quiz' === $post->post_type ) {

			return;

		}

		wp_enqueue_script( 'wpquiz', plugin_dir_url( __FILE__ ) . '../src/js/remove-question-blocks.js', array(), QUIZ_BLOCKS_VERSION, true );

	}

	public function quizblocks_blocks() {

		global $post;

		if ( ! isset( $post->post_type ) && 'quiz' !== $post->post_type ) {

			return;

		}

		$blocks_dir = basename( __DIR__ ) . '/../build/';

		$question_asset_file = include plugin_dir_path( dirname( __FILE__ ) ) . $blocks_dir . 'question/index.asset.php';

		wp_enqueue_script(
			'quizblocks-question',
			plugin_dir_url( dirname( __FILE__ ) ) . $blocks_dir . 'question/index.js',
			$question_asset_file['dependencies'],
			$question_asset_file['version']
		);

		wp_enqueue_style(
			'quizblocks-question',
			plugin_dir_url( dirname( __FILE__ ) ) . $blocks_dir . 'question/index.css',
			array(),
			true,
			'all'
		);

	}

}

new Quiz_Blocks_Blocks();
