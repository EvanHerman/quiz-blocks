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

		notLoggedIn: function() {
			$( '#quiz-blocks-quiz.not-logged-in' ).find( 'input' ).attr( 'disabled', 'disabled' );
		},

		multipleSubmissionsDisabled: function() {
			$( '#quiz-blocks-quiz.multiple-submissions-disabled' ).find( 'input' ).attr( 'disabled', 'disabled' );
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

			if (
				form.hasClass( 'not-logged-in' ) ||
				form.hasClass('multiple-submissions-disabled')
			) {
				return false; 
			}

			timer.stop();

			quiz.disableForm( form );

			jQuery.post(
				quizBlocks.ajaxURL,
				{
					'action': 'validate_answers',
					'quizID': quizID,
					'answers': answers,
					'timeTaken': msTimeTaken
				},
				function ( response ) {
					if ( response.success ) {

						const quizResponse = response.data.response;

						quiz.markCorrectAnswers(form, quizResponse );

						results.show( quizID, quizResponse );

						form.before( `<div class="quiz-blocks-alert success">${quizBlocks.successText}</div>` );

						form.find( '.show-results' ).show();

						return;

					}

					console.error( response );

					form.before(`<div class="quiz-blocks-alert error">${quizBlocks.errorText}</div>`);

				}
			);
		},

		markCorrectAnswers: function( form, quizResults ) {

			// Do not show the user the correct answers.
			if ( ! quizResults.show_answers ) {
				return;
			}

			const results = quizResults.results;

			for (var i = 0; i < results.length; i++) {
				let nthChild = i+1;
				form.find( '.question:nth-child(' + nthChild + ') .answers' ).addClass( results[i] );
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
					'action': 'get_existing_ranking',
					'quizID': quizID
				},
				function (response) {
					if (response.success) {

						$( `.quiz-${quizID}-rankings` ).html( response.data.rankings );

						return;

					}

					console.error( response );
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
			// Do not show the user the results modal.
			if ( ! response.show_results ) {
				return;
			}

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

		showExisting: function( event ) {
			event.preventDefault();
			const button = $( event.target );
			const quizID = button.data( 'quizid' );

			jQuery.get(
				quizBlocks.ajaxURL,
				{
					'action': 'get_existing_result',
					'quizID': quizID
				},
				function (response) {
					if (response.success) {

						$(`.quiz-${quizID}-results`).find('.percent-correct').text(response.data.results.percent);
						$(`.quiz-${quizID}-results`).find('.number-correct').text(response.data.results.counts.correct ?? 0);

						$(`.quiz-${quizID}-results`).modal({
							fadeDuration: 150
						});

						confetti.start();

						return;

					}

					console.error(response);
				}
			);

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

	let start = 0;
	let end = 0;
	let msTimeTaken = 0;

	var timer = {

		start: function() {

			if ( 0 !== start ) {
				return;
			}

			start = new Date().getTime();

		},

		stop: function() {

			end = new Date().getTime();

			msTimeTaken = end - start;

		},

	};

	// Add names to the form on render.
	$( document ).ready( quiz.addNames );

	// Disable the form when user is not logged in.
	$( document ).ready( quiz.notLoggedIn );

	// Disable/Style the form when multiple submissions are disabled.
	$( document ).ready( quiz.multipleSubmissionsDisabled );

	// Click on an answer.
	$( 'form#quiz-blocks-quiz label' ).on( 'click', quiz.addPopAnimation );

	// Start the quiz timer.
	$( 'form#quiz-blocks-quiz label' ).one( 'click', timer.start );

	// Submit a quiz.
	$( 'form#quiz-blocks-quiz' ).on( 'submit', quiz.submitQuiz );

	// Show the rankings modal.
	$( 'button.show-rankings' ).on( 'click', rankings.show );

	// Show the rankings modal.
	$( 'a.show-existing-results' ).on( 'click', results.showExisting );

	// Show the results modal.
	$( 'button.show-results' ).on( 'click', function( event ) {
		const quizID = $( event.target ).data( 'quizid' );
		$(`.quiz-${quizID}-results`).modal({
			fadeDuration: 150
		});
		confetti.start();
	} );

} )( jQuery );