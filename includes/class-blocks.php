<?php
/**
 * Quiz Blocks Blocks
 *
 * @package Quiz_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * Quiz_Blocks_Blocks class.
 *
 * Blocks included with Quiz Blocks.
 */
class Quiz_Blocks_Blocks {

	/**
	 * Blocks directory.
	 *
	 * @var string
	 */
	private $blocks_dir;

	/**
	 * Helpers class instance.
	 *
	 * @var class
	 */
	private $helpers;

	/**
	 * Quiz_Blocks_Blocks constructor.
	 */
	public function __construct() {

		$this->blocks_dir = basename( __DIR__ ) . '/../build/';

		$this->helpers = new Quiz_Blocks_Helpers();

		add_filter( 'block_categories_all', array( $this, 'custom_block_category' ), PHP_INT_MAX, 2 );

		add_filter( 'allowed_block_types_all', array( $this, 'quiz_allowed_blocks' ), PHP_INT_MAX, 2 );

		add_action( 'enqueue_block_editor_assets', array( $this, 'disable_blocks' ), PHP_INT_MAX );

		add_action( 'enqueue_block_editor_assets', array( $this, 'quizblocks_blocks' ), PHP_INT_MAX );

		add_action( 'init', array( $this, 'register_serverside_render_blocks' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_quiz_script' ) );

	}

	/**
	 * Register Custom Block Category.
	 *
	 * @param array $block_categories Array of core block categories.
	 *
	 * @return array Filtered list of block categories.
	 */
	public function custom_block_category( $block_categories ) {

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
	 * @param mixed $allowed_block_types Array of allowable blocks for Gutenberg Editor.
	 * @param mixed $editor_context      Gets current post type.
	 *
	 * @return mixed $allowed_blocks Returns the allowed blocks.
	 */
	public function quiz_allowed_blocks( $allowed_block_types, $editor_context ) {

		$quiz_blocks = array(
			'quizblocks/multiple-choice-question',
		);

		unset( $allowed_block_types['quizblocks/multiple-choice-question'] );

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

		wp_enqueue_script( 'quiz-blocks-editor', plugin_dir_url( __FILE__ ) . '../src/js/quiz-blocks-editor.js', array( 'jquery' ), QUIZ_BLOCKS_VERSION, true );

	}

	/**
	 * Enqueue the quiz blocks blocks and assets.
	 */
	public function quizblocks_blocks() {

		global $post;

		if ( ! isset( $post->post_type ) && 'quiz' !== $post->post_type ) {

			return;

		}

		$quiz_asset_file = include plugin_dir_path( dirname( __FILE__ ) ) . $this->blocks_dir . 'quiz/index.asset.php';

		wp_enqueue_script(
			'quiz-blocks-quiz',
			plugin_dir_url( dirname( __FILE__ ) ) . $this->blocks_dir . 'quiz/index.js',
			$quiz_asset_file['dependencies'],
			$quiz_asset_file['version'],
			true
		);

		wp_localize_script(
			'quiz-blocks-quiz',
			'quizBlocksQuiz',
			array(
				'createQuizURL' => admin_url( 'post-new.php?post_type=quiz' ),
			)
		);

		wp_enqueue_style(
			'quiz-blocks-quiz',
			plugin_dir_url( dirname( __FILE__ ) ) . $this->blocks_dir . 'quiz/index.css',
			array(),
			true,
			'all'
		);

		$question_asset_file = include plugin_dir_path( dirname( __FILE__ ) ) . $this->blocks_dir . 'question/index.asset.php';

		wp_enqueue_script(
			'quiz-blocks-question',
			plugin_dir_url( dirname( __FILE__ ) ) . $this->blocks_dir . 'question/index.js',
			$question_asset_file['dependencies'],
			$question_asset_file['version'],
			true
		);

		wp_enqueue_style(
			'quiz-blocks-question',
			plugin_dir_url( dirname( __FILE__ ) ) . $this->blocks_dir . 'question/index.css',
			array(),
			true,
			'all'
		);

	}

	/**
	 * Register the side render blocks.
	 */
	public function register_serverside_render_blocks() {

		wp_register_style(
			'quiz-blocks-styles',
			plugin_dir_url( dirname( __FILE__ ) ) . $this->blocks_dir . 'quiz/style-index.css',
			array(),
			true,
			'all'
		);

		register_block_type(
			plugin_dir_path( dirname( __FILE__ ) ) . 'build/quiz',
			array(
				'title'           => __( 'Quiz', 'quiz-blocks' ),
				'style'           => 'quiz-blocks-styles',
				'render_callback' => function( $atts ) {
					return $this->helpers->render_quiz( $atts );
				},
			)
		);

	}

	/**
	 * Enqueue the quiz block scripts.
	 */
	public function enqueue_quiz_script() {

		if (
			! has_block( 'quizblocks/quiz' ) &&
			! has_shortcode( get_the_content(), 'quiz' )
		) {

			return;

		}

		wp_enqueue_script(
			'jquerymodal',
			plugin_dir_url( dirname( __FILE__ ) ) . 'src/thirdparty/jquery.modal/jquery.modal.min.js',
			array( 'jquery' ),
			JQUERY_MODAL_VERSION,
			true
		);

		wp_enqueue_script(
			'canvas-confetti',
			plugin_dir_url( dirname( __FILE__ ) ) . 'src/thirdparty/canvas-confetti/confetti.browser.min.js',
			array(),
			'1.5.1',
			true
		);

		wp_enqueue_script(
			'quiz-blocks-frontend',
			plugin_dir_url( dirname( __FILE__ ) ) . 'src/js/quiz-blocks-frontend.js',
			array( 'jquerymodal', 'canvas-confetti' ),
			QUIZ_BLOCKS_VERSION,
			true
		);

		wp_localize_script(
			'quiz-blocks-frontend',
			'quizBlocks',
			array(
				'ajaxURL'     => admin_url( 'admin-ajax.php' ),
				'successText' => __( 'Success!', 'quiz-blocks' ),
				'errorText'   => __( 'Error!', 'quiz-blocks' ),
			)
		);

		wp_enqueue_style(
			'jquerymodal',
			plugin_dir_url( dirname( __FILE__ ) ) . 'src/thirdparty/jquery.modal/jquery.modal.min.css',
			array(),
			JQUERY_MODAL_VERSION,
			'all'
		);

	}

}

new Quiz_Blocks_Blocks();
