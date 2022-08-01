<?php
/**
 * Quiz Blocks Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

class Quiz_Blocks_Blocks {

	private $blocks_dir;

	public function __construct() {

		$this->blocks_dir = basename( __DIR__ ) . '/../build/';

		add_filter( 'block_categories_all',  array( $this, 'custom_block_category' ), PHP_INT_MAX, 2 );

		add_filter( 'allowed_block_types_all', array( $this, 'quiz_allowed_blocks' ), PHP_INT_MAX, 2 );

		add_action( 'enqueue_block_editor_assets', array( $this, 'disable_blocks' ), PHP_INT_MAX );

		add_action( 'enqueue_block_editor_assets', array( $this, 'quizblocks_blocks' ), PHP_INT_MAX );

		add_action( 'init', array( $this, 'register_serverside_render_blocks' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_quiz_script' ) );

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

	public function register_serverside_render_blocks() {

		wp_register_style(
			'quiz-blocks-styles',
			plugin_dir_url( dirname( __FILE__ ) ) . $this->blocks_dir . 'quiz/style-index.css',
			array(),
			true,
			'all'
		);

		register_block_type(
			'quizblocks/quiz',
			array(
				'title'           => __( 'Quiz', 'quiz-blocks' ),
				'style'           => 'quiz-blocks-styles',
				'attributes'      => array(
					'quizID' => array(
						'type'    => 'integer',
						'default' => 0,
					),
					'useRankings' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showTitle'   => array(
						'type'    => 'boolean',
						'default' => true,
					),
				),
				'render_callback' => function( $atts ) {
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

					ob_start();

					print( '<div id="quiz-blocks">' );

					printf(
						'<h2 class="quiz-title">%s</h2>',
						esc_html( $quiz_content->post_title )
					);

					if ( $atts['useRankings'] ) {

						printf(
							'<button class="show-rankings button button_sliding_bg" data-quizid="%1$s">%2$s</button>
							<div class="quiz-%1$s-rankings quiz-blocks-rankings"><img src="%3$s" class="preloader" /></div>',
							esc_attr( $atts['quizID'] ),
							esc_html__( 'View Quiz Rankings', 'quiz-blocks' ),
							plugin_dir_url( dirname( __FILE__ ) ) . 'src/img/preloader.svg'
						);

					}

					?>

					<form id="quiz-blocks-quiz" data-quizid="<?php echo esc_attr( $atts['quizID'] ); ?>">
						<?php echo $quiz; ?>
						<input class="button_sliding_bg button" type="submit" name="submit" id="submit" value="<?php esc_html_e( 'Submit', 'quiz-blocks' ); ?>" />
					</form>

					<button class="show-results button button_sliding_bg" data-quizid="<?php echo esc_attr( $atts['quizID'] ); ?>"><?php esc_html_e( 'View Results', 'quiz-blocks' ); ?></button>

					<?php

					if ( is_user_logged_in() && current_user_can( 'administrator' ) ) {

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
				},
			)
		);

	}

	public function enqueue_quiz_script() {

		if ( ! has_block( 'quizblocks/quiz' ) ) {

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
