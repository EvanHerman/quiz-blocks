<?php
/**
 * Tabs navigation.
 *
 * @package Quiz_Blocks
 */

?>
<h2 class="nav-tab-wrapper">
	<a href="#getting-started" class="nav-tab nav-tab-active"><?php esc_html_e( 'Getting Started', 'quiz-blocks' ); ?></a>
	<?php do_action( 'rwmb_about_tabs' ); ?>
	<a href="#extensions" class="nav-tab"><?php esc_html_e( 'Extensions', 'quiz-blocks' ); ?></a>
	<a href="#support" class="nav-tab"><?php esc_html_e( 'Support', 'quiz-blocks' ); ?></a>
</h2>
