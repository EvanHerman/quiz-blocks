<?php
/**
 * Quiz Blocks Quiz Post Type Block Editor
 *
 * @package Quiz_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * Quiz_Blocks_Block_Editor class.
 *
 * Force block editor on the 'quiz' post type.
 */
class Quiz_Blocks_Block_Editor {

	/**
	 * Quiz_Blocks_Block_Editor class constructor.
	 */
	public function __construct() {

		add_filter( 'classic_editor_plugin_settings', array( $this, 'force_block_editor' ) );

	}

	/**
	 * Force the block editor on quiz post types.
	 *
	 * @param   [type]  $settings  [$settings description]
	 *
	 * @return  [type]             [return description]
	 */
	public function force_block_editor( $settings ) {

		$post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING );
		$post_id   = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

		$post_type = get_post_type( $post_id );

		if (
			( ! $post_type || 'quiz' !== $post_type ) || // New quiz.
			( 'quiz' !== $post_type ) // Existing quiz.
		) {

			return $settings;

		}

		return array(
			'editor'      => 'block',
			'allow-users' => false,
		);

	}

}

new Quiz_Blocks_Block_Editor();
