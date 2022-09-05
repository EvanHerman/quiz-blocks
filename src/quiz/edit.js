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
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl } from "@wordpress/components";
import { useSelect } from '@wordpress/data';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

import preloader from '../img/preloader.svg';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
const Edit = ({ attributes, setAttributes, isSelected }) => {
	const quizzes = useSelect((select) => {
		return select('core').getEntityRecords('postType', 'quiz');
	}, []);

	const quizIDs = () => {
		if (!quizzes) {
			return;
		}

		if (!quizzes.length) {
			return [];
		}

		const ids = [{label:__('No Quiz', 'quiz-blocks'), value: 0}];

		quizzes.forEach((quiz) => {
			ids.push({ label: quiz.title.raw ?? '', value: quiz.id });
		});
		
		return ids;
	}

	const unescapeHTML = (escapedHTML) => {
		return escapedHTML.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&');
	}

	const quizFields = () => {
		const fields = quizzes.find(x => x.id === attributes.quizID );
		if ( ! fields ) {
			return;
		}
		return <div dangerouslySetInnerHTML={{ __html: unescapeHTML( fields.content.rendered ) }}></div>
	}

	const selectQuizText = isSelected ? __('Choose a quiz to display.', 'quiz-blocks') : __('Select this block, and choose a quiz to display.', 'quiz-blocks');

	const quizIDOptions = quizIDs();
	let quizTitle = '';

	if ( quizIDOptions && quizIDOptions.length ) {
		const quizIDOptionsIndex = quizIDOptions.map(object => object.value).indexOf(attributes.quizID);
		quizTitle = quizIDOptions[quizIDOptionsIndex].label;
	}

	return (
		<div {...useBlockProps()}>
			<InspectorControls>
				<PanelBody title={__('Display Settings', 'quiz-blocks')} initialOpen={true}>
					<SelectControl
						label={__('Select which quiz to display.', 'quiz-blocks')}
						value={attributes.quizID}
						options={quizIDOptions}
						onChange={(quizID) => setAttributes({ quizID: parseInt(quizID) })}
					/>
					<ToggleControl
						label={attributes.showTitle ? __('Quiz Title Enabled', 'quiz-blocks') : __('Quiz Title Disabled', 'quiz-blocks')}
						checked={attributes.showTitle}
						onChange={() => setAttributes({ showTitle: !attributes.showTitle })}
					/>
				</PanelBody>
				<PanelBody title={__('Quiz Settings', 'quiz-blocks')} initialOpen={true}>
					<ToggleControl
						label={attributes.requireLogin ? __('Require Login', 'quiz-blocks') : __('Do Not Require Login', 'quiz-blocks')}
						checked={attributes.requireLogin}
						onChange={() => {
							setAttributes({ requireLogin: !attributes.requireLogin });
							if ( !! attributes.requireLogin ) {
								setAttributes({
									useRankings: false,
									multipleSubmissions: false,
								});
							}
						}}
						help={attributes.requireLogin ? __('Users must be logged in to submit this quiz.', 'quiz-blocks') : __('Non-logged in users can submit this quiz. Note: Rankings and multiple submissions are disabled.', 'quiz-blocks')}
					/>
					<ToggleControl
						label={attributes.useRankings ? __('Rankings Enabled', 'quiz-blocks') : __('Rankings Disabled', 'quiz-blocks')}
						checked={attributes.requireLogin && attributes.useRankings}
						disabled={!attributes.requireLogin}
						onChange={() => setAttributes({ useRankings: !attributes.useRankings })}
						help={attributes.useRankings ? __('The rankings are enabled. It is required that users are logged in for rankings to work.', 'quiz-blocks') : __('Rankings are disabled.', 'quiz-blocks')}
					/>
					<ToggleControl
						label={attributes.multipleSubmissions ? __('Multiple Submissions Enabled', 'quiz-blocks') : __('Multiple Submissions Disabled', 'quiz-blocks')}
						checked={attributes.requireLogin && attributes.multipleSubmissions}
						disabled={!attributes.requireLogin}
						onChange={() => setAttributes({ multipleSubmissions: !attributes.multipleSubmissions })}
						help={attributes.multipleSubmissions ? __('Users can submit the quiz multiple times, but only the latest submission will be saved.', 'quiz-blocks') : __('Users can only submit this quiz one time.', 'quiz-blocks')}
					/>
					<ToggleControl
						label={attributes.showResults ? __('Show Results Enabled', 'quiz-blocks') : __('Show Results Disabled', 'quiz-blocks')}
						checked={attributes.showResults}
						onChange={() => setAttributes({ showResults: !attributes.showResults })}
						help={attributes.showResults ? __('Results will be shown to the user after the quiz is submitted. Users will see how many questions they got right, and the percent correct.', 'quiz-blocks') : __('Results will not be shown to the user after the quiz is submitted.', 'quiz-blocks')}
					/>
					<ToggleControl
						label={attributes.showAnswers ? __('Show Answers Enabled', 'quiz-blocks') : __('Show Answers Disabled', 'quiz-blocks')}
						checked={attributes.showAnswers}
						onChange={() => setAttributes({ showAnswers: !attributes.showAnswers })}
						help={attributes.showAnswers ? __('After the quiz is submitted, the correct answers will be shown to the user.', 'quiz-blocks') : __('After the quiz is submitted, the answers will not be shown to the user.', 'quiz-blocks')}
					/>
				</PanelBody>
			</InspectorControls>
			{! quizzes && <img src={preloader} alt={ __('Preloader', 'quiz-blocks') } className="preloader" /> }
			{ ( quizzes && ! quizzes.length ) &&
				<div id="quiz-blocks-quiz" className="create-quiz">
					<strong>{__( 'No Quizzes Found', 'quiz-block' )}</strong>
					<p>Please <a href={quizBlocksQuiz.createQuizURL}>create a quiz</a>.</p>
				</div>
			}
			{ ( 0 === attributes.quizID ) &&
				<div id="quiz-blocks-quiz" className="select-quiz">
					<strong>{__('Select a Quiz', 'quiz-block')}</strong>
					<p>{selectQuizText}</p>
					{ isSelected &&
						<SelectControl
							value={attributes.quizID}
							options={quizIDOptions}
							onChange={(quizID) => setAttributes({ quizID: parseInt(quizID) })}
						/>
					}
				</div>
			}
			{ ( 0 !== attributes.quizID && quizzes && 0 < quizzes.length ) &&
				<div id="quiz-blocks-quiz">
					{attributes.showTitle &&
						<h2 className="quiz-title">{quizTitle}</h2>
					}
					{ attributes.useRankings &&
						<button className="show-rankings button button_sliding_bg">{__( 'View Quiz Rankings', 'quiz-blocks' )}</button>
					}
					{quizFields()}
					<input className="button_sliding_bg button" type="submit" name="submit" id="submit" value={__('Submit', 'quiz-blocks')} />
				</div>
			}
		</div>
	);
}

export default Edit