/**
 * Exclude the quiz blocks.
 *
 * Note: This runs on all posts excpet for the quiz post type.
 */
wp.domReady( function() {
    wp.blocks.unregisterBlockType( 'quizblocks/multiple-choice-question' );
} );