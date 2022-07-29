/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

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
	const [question, setQuestion] = useState('');
	const [answer, setAnswer] = useState('');
	const [correctAnswer, setCorrectAnswer] = useState(attributes.correctAnswer);
	const blockProps = useBlockProps();

	const renderAnswers = () => {
		const answers = [];
		for (var i = 0; i < attributes.answerCount; i++) {
			answers.push(
				<RichText
					tagName="p"
					placeholder={sprintf(__('Answer %s', 'quiz-block'), i+1)}
					value={answer}
					onChange={(value) => setAnswer(value)}
					className="answer"
				/>
			);
		}
		return answers;
	};
	
	const correctAnswerValues = () => {
		const correctAnswerValues = [];
		for (var i = 0; i < attributes.answerCount; i++) {
			correctAnswerValues.push(
				{
					label: sprintf(__('Answer %s', 'quiz-blocks'), i + 1),
					value: i
				}
			);
		}
		return correctAnswerValues;
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__( 'Question Settings', 'quiz-blocks' )} initialOpen={true}>
					<NumberControl
						label={__( 'How many answers for this question?', 'quiz-blocks')}
						min={2}
						max={25}
						required
						isShiftStepEnabled={true}
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
					<SelectControl
						label={__('What is the correct answer?', 'quiz-blocks')}
						value={correctAnswer}
						options={correctAnswerValues()}
						onChange={(correctAnswer) => setAttributes({ correctAnswer: parseInt(correctAnswer) })}
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>
			<div>
				<RichText
					{...blockProps}
					tagName="p"
					placeholder={__('Type your question...')}
					value={attributes.question}
					onChange={(question) => setAttributes({ question: question })}
					className="quiz-block-question"
				/>
				<div className="quiz-block-answers">
					{renderAnswers()}
				</div>
			</div>
		</>
	);
}
