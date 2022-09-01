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
	 * @param array $settings Classic editor settings array.
	 *
	 * @return array Filtered settings array when on quiz post type, else original settings.
	 */
	public function force_block_editor( $settings ) {

		$post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING );

		if ( 'quiz' === $post_type ) {

			return array(
				'editor'      => 'block',
				'allow-users' => false,
			);

		}

		$post_id   = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
		$post_type = get_post_type( $post_id );

		return ( 'quiz' !== $post_type ) ? $settings : array(
			'editor'      => 'block',
			'allow-users' => false,
		);

	}

}

new Quiz_Blocks_Block_Editor();
