( function( $ ) {

	const submissions = {

		show: function( event ) {
			event.preventDefault();
			$( '.quiz-blocks-submissions' ).html( '<h2>Testing :D</h2>' );
			$(`.quiz-blocks-submissions`).modal({
				fadeDuration: 150
			});
		},

	};

	$( document ).on( 'click', 'a.view-submissions', submissions.show );

} )( jQuery );