(function( $ ) {

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

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(
				quizBlocks.ajaxURL,
				{
					'action': 'validate_answers',
					'quizID': form.attr('data-quizID'),
					'answers': answers
				},
				function ( response ) {
					console.log( response );
					quiz.enableForm(form);

					if ( response.success ) {

						form.before( `<div class="quiz-blocks-alert success">${quizBlocks.successText}</div>` );

						return;

					}

					quiz.enableForm( form );
					form.before(`<div class="quiz-blocks-alert error">${quizBlocks.errorText}</div>`);

				}
			);
		},

	};

	$( document ).ready( quiz.addNames );

	$( 'form#quiz-blocks-quiz label' ).on( 'click', quiz.addPopAnimation );

	$( 'form#quiz-blocks-quiz' ).on( 'submit', quiz.submitQuiz );

} )( jQuery );