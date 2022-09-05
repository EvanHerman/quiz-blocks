/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#save
 *
 * @return {WPElement} Element to render.
 */
export default function save({ attributes }) {
	const answerCountArray = Array.apply(null, Array(attributes.answerCount));

	return (
		<div className="question">
			<p><strong>{ attributes.question }</strong></p>
			<div className="answers">
				{answerCountArray.map((emptyValue, index) => {
					return (
						<div className="answer" key={index}>
							<input type="radio" id={`answer-${index}`} value={index} required />
							<label htmlFor={`answer-${index}`}>{attributes.answers[index]}</label>
						</div>
					)
				})}
			</div>
		</div>
	);
}
