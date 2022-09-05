/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, __experimentalNumberControl as NumberControl } from "@wordpress/components";

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';
import { useState } from '@wordpress/element';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes, setAttributes, isSelected }) {
	const answerCountArray = Array.apply(null, Array(attributes.answerCount));

	const correctAnswerValues = () => {
		const correctAnswers = [];
		for (let i = 0; i < attributes.answerCount; i++) {
			const label = !attributes.answers[i] ? sprintf(/* translators: %s is an integer value, the index in the loop. */ __('Answer %s', 'quiz-blocks'), i + 1) : attributes.answers[i].replace(/<[^>]*>?/gm, '');
			correctAnswers.push(
				{
					label: label,
					value: i
				}
			);
		}
		return correctAnswers;
	};

	return (
		<div {...useBlockProps()}>
			<InspectorControls>
				<PanelBody title={__( 'Question Settings', 'quiz-blocks' )} initialOpen={true}>
					<NumberControl
						label={__( 'How many answers for this question?', 'quiz-blocks')}
						min={2}
						max={25}
						required
						onChange={(answerCount) => {
							if ( answerCount <= 2 ) {
								answerCount = 2;
							}
							if (answerCount >= 25) {
								answerCount = 25;
							}
							setAttributes({ answerCount: parseInt( answerCount ) })
						} }
						value={attributes.answerCount}
					/>
					<br />
					<SelectControl
						label={__('What is the correct answer?', 'quiz-blocks')}
						value={attributes.correctAnswer}
						options={correctAnswerValues()}
						onChange={(correctAnswer) => setAttributes({ correctAnswer: parseInt(correctAnswer) })}
					/>
				</PanelBody>
			</InspectorControls>
			<div>
				<strong>
					<RichText
						tagName="p"
						placeholder={__('Type your question&hellip;', 'quiz-blocks')}
						value={attributes.question}
						onChange={(question) => setAttributes({ question: question })}
						className="quiz-block-question"
					/>
				</strong>
				<div className="quiz-block-answers">
					{answerCountArray.map((emptyValue, i) => {
						return (
							<RichText
								key={i}
								tagName="p"
								placeholder={sprintf(/* translators: %s is an integer value, the index in the loop. */ __('Answer %s', 'quiz-block'), i + 1)}
								value={!!attributes.answers[i] ? attributes.answers[i] : ''}
								onChange={(newAnswer) => {
									const newAnswers = [...attributes.answers];
									newAnswers[i] = newAnswer;
									setAttributes({ answers: newAnswers })
								}}
								className="answer"
								tabIndex={i}
							/>
						)
					})}
				</div>
			</div>
		</div>
	);
}
