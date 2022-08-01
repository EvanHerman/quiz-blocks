(function( $ ) {

	/**
	 * Quiz Specific Functionality
	 *
	 * @var {object}
	 */
	var quiz = {

		addNames: function() {
			$( 'form#quiz-blocks-quiz .question' ).each( function( questionIndex ) {
				var questionName = `question-${questionIndex}`;
				$( this ).find( '.answers .answer' ).each( function( answerIndex ) {
					var answerName = `${questionName}-answer-${answerIndex}`;
					$( this ).find( 'input[type="radio"]' ).attr( 'id', answerName ).attr( 'name', `question-${questionIndex}` );
					$( this ).find( 'label' ).attr( 'for', answerName );
				} );
			} );
		},

		addPopAnimation: function( event ) {
			const button = $( event.target );
			if (button.prev().prop('disabled')) {
				return;
			}
			button.attr( 'data-animation', 'pop' );
			setTimeout(() => {
				button.removeAttr( 'data-animation' );
			}, 200);
		},

		disableForm: function( form ) {
			form.find( 'input' ).attr( 'disabled', true );
		},

		enableForm: function( form ) {
			form.find('input').removeAttr('disabled');
		},

		submitQuiz: function( event ) {
			event.preventDefault();
			const form = $( event.target );
			const answers = form.serialize();
			const quizID = form.data( 'quizid' );

			quiz.disableForm( form );

			jQuery.post(
				quizBlocks.ajaxURL,
				{
					'action': 'validate_answers',
					'quizID': quizID,
					'answers': answers
				},
				function ( response ) {
					if ( response.success ) {

						const quizResults = response.data.response.results;
						const quizResponse = response.data.response;

						quiz.markCorrectAnswers( form, quizResults );
						results.show( quizID, quizResponse );

						form.before( `<div class="quiz-blocks-alert success">${quizBlocks.successText}</div>` );

						form.find( '.show-results' ).show();

						return;

					}

					form.before(`<div class="quiz-blocks-alert error">${quizBlocks.errorText}</div>`);

				}
			);
		},

		markCorrectAnswers: function( form, results ) {

			for (var i = 0; i < results.length; i++) {
				let nthChild = i+1;
				form.find('.question:nth-child(' + nthChild + ') .answers').addClass( results[i] );
			}

		},

	};

	/**
	 * Rankings Modal
	 *
	 * @var {object}
	 */
	var rankings = {

		show: function( event ) {
			const button = $( event.target );
			const quizID = button.data( 'quizid' );

			jQuery.get(
				quizBlocks.ajaxURL,
				{
					'action': 'get_rankings',
					'quizID': quizID
				},
				function (response) {
					if (response.success) {

						$( `.quiz-${quizID}-rankings` ).html( response.data.rankings );

						return;

					}

					console.error( ':(' );
				}
			);

			$( `.quiz-${quizID}-rankings` ).modal({
				fadeDuration: 150
			});
			return false;
		},

	};

	/**
	 * Quiz Results.
	 *
	 * @var {object}
	 */
	var results = {

		show: function( quizID, response ) {
			const numberCorrect = response.counts.correct ?? 0;
			const percentCorrect = ( ( numberCorrect / response.results.length ) * 100 ) + '%';

			$( `.quiz-${quizID}-results` ).find( '.percent-correct' ).text( percentCorrect );
			$( `.quiz-${quizID}-results` ).find( '.number-correct' ).text( response.counts.correct ?? 0 );

			$(`.quiz-${quizID}-results`).modal({
				fadeDuration: 150
			});

			$( `button[data-quizid="${quizID}"].show-results` ).show();

			confetti.start();
		},

	};

	/**
	 * Confetti
	 *
	 * @var {object}
	 */
	var confetti = {

		start: function() {

			var end = Date.now() + (2 * 1000);

			(function frame() {
				window.confetti({
					particleCount: 5,
					angle: 60,
					spread: 100,
					origin: { x: 0 },
					disableForReducedMotion: true
				});

				window.confetti({
					particleCount: 5,
					angle: 120,
					spread: 100,
					origin: { x: 1 },
					disableForReducedMotion: true
				});

				if (Date.now() < end) {
					requestAnimationFrame(frame);
				}
			}());

		},

	};

	// Add names to the form on render.
	$( document ).ready( quiz.addNames );

	// Click on an answer.
	$( 'form#quiz-blocks-quiz label' ).on( 'click', quiz.addPopAnimation );

	// Submit a quiz.
	$( 'form#quiz-blocks-quiz' ).on( 'submit', quiz.submitQuiz );

	// Show the rankings modal.
	$( 'button.show-rankings' ).on( 'click', rankings.show );

	// Show the results modal.
	$( 'button.show-results' ).on( 'click', function( event ) {
		const quizID = $( event.target ).data( 'quizid' );
		$(`.quiz-${quizID}-results`).modal({
			fadeDuration: 150
		});
		confetti.start();
	} );

} )( jQuery );