<?php
/**
 * Quiz Blocks Welcome Page
 *
 * @package Quiz_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * Quiz_Blocks_Welcome class.
 *
 * Actions for quiz submissions.
 */
class Quiz_Blocks_Welcome {

	/**
	 * Quiz_Blocks_Welcome constructor.
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'register_welcome_guide' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_welcome_assets' ) );

	}

	/**
	 * Register the Welcome Guide page.
	 */
	public function register_welcome_guide() {

		add_submenu_page(
			null,
			__( 'Welcome Guide', 'quiz-blocks' ),
			__( 'Welcome Guide', 'quiz-blocks' ),
			'manage_options',
			'quiz-blocks-welcome-guide',
			array( $this, 'welcome_page' )
		);

	}

	/**
	 * Enqueue the Welcome Guide js/css assets.
	 *
	 * @param string $hook_suffix Admin page suffix.
	 */
	public function enqueue_welcome_assets( $hook_suffix ) {

		if ( 'dashboard_page_quiz-blocks-welcome-guide' !== $hook_suffix ) {

			return;

		}

		wp_enqueue_style( 'quiz-blocks-welcome-guide', plugin_dir_url( __FILE__ ) . 'css/about.css', array(), QUIZ_BLOCKS_VERSION );

		wp_enqueue_script( 'quiz-blocks-welcome-guide', plugin_dir_url( __FILE__ ) . 'js/about.js', array( 'jquery' ), QUIZ_BLOCKS_VERSION, true );

		// Alter the footer text on the Welcome Guide.
		add_filter( 'admin_footer_text', array( $this, 'change_footer_text' ) );

	}

	/**
	 * Alter the footer text on the welcome guide page.
	 *
	 * @return string The filtered footer text.
	 */
	public function change_footer_text() {
		return wp_kses_post(
			sprintf(
				// translators: %1$s - link to review the plugin.
				__( 'Please rate <strong>Quiz Blocks</strong> <a href="%1$s" target="_blank" style="color: goldenrod;">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%1$s" target="_blank">WordPress.org</a> to help us spread the word. Thank you from the Code Parrots team!', 'quiz-blocks' ),
				'https://wordpress.org/support/view/plugin-reviews/quiz-blocks?filter=5#new-post'
			)
		);
	}

	/**
	 * Welcome Page Markup.
	 */
	public function welcome_page() {

		?>
		<div class="wrap">
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="about-wrap">
							<?php
							include __DIR__ . '/sections/welcome.php';
							include __DIR__ . '/sections/tabs.php';
							include __DIR__ . '/sections/getting-started.php';
							include __DIR__ . '/sections/support.php';
							do_action( 'rwmb_about_tabs_content' );
							?>
						</div>
					</div>
					<div id="postbox-container-1" class="postbox-container">
						<?php
						include __DIR__ . '/sections/products.php';
						// include __DIR__ . '/sections/upgrade.php'; phpcs:ignore.
						?>
					</div>
				</div>
			</div>
		</div>
		<?php

	}

}

new Quiz_Blocks_Welcome();
