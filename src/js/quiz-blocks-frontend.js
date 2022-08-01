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

			quiz.disableForm( form );

			jQuery.post(
				quizBlocks.ajaxURL,
				{
					'action': 'validate_answers',
					'quizID': form.attr('data-quizID'),
					'answers': answers
				},
				function ( response ) {
					console.log( response.data.response );

					if ( response.success ) {

						quiz.markCorrectAnswers( form, response.data.response.results );

						form.before( `<div class="quiz-blocks-alert success">${quizBlocks.successText}</div>` );

						return;

					}

					form.before(`<div class="quiz-blocks-alert error">${quizBlocks.errorText}</div>`);

				}
			);
		},

		markCorrectAnswers: function( form, results ) {

			for (var i = 0; i < results.length; i++) {
				let nthChild = i+1;
	
				console.log('.question:nth-child(' + nthChild + ') .answers');

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
					console.log(response.data);

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
			confetti.start();
			return false;
		},

	};

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

	$( document ).ready( quiz.addNames );

	$( 'form#quiz-blocks-quiz label' ).on( 'click', quiz.addPopAnimation );

	$( 'form#quiz-blocks-quiz' ).on( 'submit', quiz.submitQuiz );

	$( 'button.show-rankings' ).on( 'click', rankings.show );

} )( jQuery );