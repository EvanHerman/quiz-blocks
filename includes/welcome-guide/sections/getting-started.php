<?php
/**
 * Getting started section.
 *
 * @package Quiz_Blocks
 */

?>

<div id="getting-started" class="gt-tab-pane gt-is-active">
	<div class="two">
		<div class="col">
			<h3><?php esc_html_e( 'Getting Started With Quiz Blocks', 'quiz-blocks' ); ?></h3>

			<?php

			printf(
				'<p>%s</p>',
				sprintf(
					/* translators: %s is an anchor tag linking to the an admin page to create a quiz. */
					esc_html__(
						"To get started, you'll first want to create a quiz to display on your site. You can do this by navigating to %s in the left hand menu.",
						'quiz-blocks'
					),
					sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url( admin_url( 'post-new.php?post_type=quiz' ) ),
						esc_html__( 'Quizzes > Add New Quiz', 'quiz-blocks' )
					)
				)
			);

			?>

			<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../images/create-quiz.png' ); ?>" alt="<?php esc_attr_e( 'Quiz Blocks - Create a New Quiz', 'quiz-blocks' ); ?>">
			<br />
			<p>
				<a class="button" target="_blank" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=quiz' ) ); ?>"><?php esc_html_e( 'Create a Quiz', 'quiz-blocks' ); ?></a>
			</p>
		</div>

		<div class="col">
			<h3><?php esc_html_e( 'Add the Quiz to a Page', 'quiz-blocks' ); ?></h3>
			<p><?php esc_html_e( "After the quiz is created, you can easily add it to any page or post on your site. Just add the 'Quiz' block to your post/page, and select the quiz you want to show. You can also adjust the quiz settings in the right hand sidebar.", 'quiz-blocks' ); ?><p>

			<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../images/add-quiz-block.gif' ); ?>" alt="<?php esc_attr_e( 'Add Quiz Block to Page', 'quiz-blocks' ); ?>">
		</div>
	</div>
</div>
