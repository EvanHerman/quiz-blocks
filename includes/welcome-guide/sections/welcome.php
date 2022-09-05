<?php
/**
 * Welcome section.
 *
 * @package Quiz_Blocks
 */

?>

<h1>
	<?php
	$plugin_data = get_plugin_data( dirname( __FILE__ ) . '../../../../quiz-blocks.php', false, false );

	// Translators: %s - Plugin name.
	echo esc_html( sprintf( __( 'Welcome to %s', 'quiz-blocks' ), $plugin_data['Name'] ) );
	?>
</h1>
<div class="about-text"><?php esc_html_e( 'Quiz Blocks is a WordPress plugin that makes it very easy to create highly customized quizzes for your users take. Submissions can be logged to track who took your quiz and how well they did. Follow the instruction below to get started!', 'quiz-blocks' ); ?></div>
<a target="_blank" class="wp-badge" href="https://wordpress.org/plugin/quiz-blocks/"><?php echo esc_html( $plugin_data['Name'] ); ?></a>
<p class="about-buttons">
	<a target="_blank" class="button" href="https://wordpress.org/support/plugin/quiz-blocks/"><?php esc_html_e( 'Support', 'quiz-blocks' ); ?></a>
</p>
