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

import { useState } from '@wordpress/element';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
const Edit = ({ attributes, setAttributes }) => {
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
		return <div className="quiz-block-quiz" dangerouslySetInnerHTML={{ __html: unescapeHTML( quizFields.content.rendered ) }}></div>
	}

	return (
		<div {...useBlockProps()}>
			<InspectorControls>
				<PanelBody title={__('Question Settings', 'quiz-blocks')} initialOpen={true}>
					<SelectControl
						label={__('Select which quiz to display.', 'quiz-blocks')}
						value={attributes.quizID}
						options={quizIDs()}
						onChange={(quizID) => setAttributes({ quizID: parseInt(quizID) })}
					/>
					<label data-wp-component="Text">Use Rankings?</label>
						<div class="components-input-control__container">
							<FormToggle
							label={__('Use Rankings?', 'quiz-blocks')}
							checked={attributes.useRankings}
							onChange={(useRankings) => setAttributes({ useRankings: !attributes.useRankings })}
							/>
						</div>
				</PanelBody>
			</InspectorControls>
			{ ! quizzes && <h2>Loading...</h2> }
			{ ( quizzes && ! quizzes.length ) && <h2>No Quizzes created.</h2> }
			{ ( quizzes && quizzes.length) &&
				<div>
					{ attributes.useRankings &&
						<h2>Testing</h2>
					}
					<div className="quiz-block-answers">
						{quizFields()}
						<input class="button_sliding_bg button" type="submit" name="submit" id="submit" value={__( 'Submit', 'quiz-blocks' )} />
					</div>
				</div>
			}
		</div>
	);
}

export default Edit;