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
import { PanelBody, SelectControl, FormToggle } from "@wordpress/components";
import { useSelect } from '@wordpress/data';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

import preloader from '../img/preloader.svg';

import { useState } from '@wordpress/element';

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

	const blockProps = useBlockProps();

	const quizIDs = () => {
		if (!quizzes) {
			return;
		}

		if (!quizzes.length) {
			return [];
		}

		const ids = [{label:__('No Quiz', 'quiz-blocks'), value: 0}];

		quizzes.map((quiz) => {
			ids.push({ label: quiz.title.raw ?? '', value: quiz.id });
		});
		
		return ids;
	}

	const unescapeHTML = (escapedHTML) => {
		return escapedHTML.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&');
	}

	const quizFields = () => {
		const quizFields = quizzes.find(x => x.id === attributes.quizID );
		if ( ! quizFields ) {
			return;
		}
		return <div dangerouslySetInnerHTML={{ __html: unescapeHTML( quizFields.content.rendered ) }}></div>
	}

	const selectQuizText = isSelected ? __('Choose a quiz to display.', 'quiz-blocks') : __('Select this block, and choose a quiz to display.', 'quiz-blocks');

	return (
		<div {...useBlockProps()}>
			<InspectorControls>
				<PanelBody title={__('Quiz Settings', 'quiz-blocks')} initialOpen={true}>
					<SelectControl
						label={__('Select which quiz to display.', 'quiz-blocks')}
						value={attributes.quizID}
						options={quizIDs()}
						onChange={(quizID) => setAttributes({ quizID: parseInt(quizID) })}
					/>
					<label data-wp-component="Text">{__('Show Rankings?', 'quiz-blocks')}</label>
						<div className="components-input-control__container">
							<FormToggle
							label={__('Show Rankings?', 'quiz-blocks')}
							checked={attributes.useRankings}
							onChange={(useRankings) => setAttributes({ useRankings: !attributes.useRankings })}
							/>
						</div>
				</PanelBody>
			</InspectorControls>
			{! quizzes && <img src={preloader} className="preloader" /> }
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
							options={quizIDs()}
							onChange={(quizID) => setAttributes({ quizID: parseInt(quizID) })}
						/>
					}
				</div>
			}
			{ ( 0 !== attributes.quizID && quizzes && 0 < quizzes.length ) &&
				<div id="quiz-blocks-quiz">
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