/**
 * - Exclude the quiz blocks.
 * - Remove click functionality from the quiz questions.
 *
 * Note: This runs on all posts excpet for the quiz post type.
 */
wp.domReady( function() {
	wp.blocks.unregisterBlockType( 'quizblocks/multiple-choice-question' );

	jQuery( 'body' ).on( 'click', '#quiz-blocks-quiz label', function () {
		return false;
	} );
} );
