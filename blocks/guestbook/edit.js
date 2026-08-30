import { __ } from "@wordpress/i18n";
import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import { PanelBody, SelectControl, TextControl } from "@wordpress/components";

const HEADING_LEVELS = [2, 3, 4, 5, 6];

export default function Edit({ attributes, setAttributes }) {
	const { title, headingLevel = 2 } = attributes;
	const displayedTitle =
		title === undefined ? __("Guestbook", "inskrift") : title;
	const HeadingTag = `h${headingLevel}`;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__("Guestbook settings", "inskrift")}>
					<TextControl
						label={__("Title", "inskrift")}
						value={displayedTitle}
						onChange={(value) => setAttributes({ title: value })}
					/>
					<SelectControl
						label={__("Heading level", "inskrift")}
						value={headingLevel}
						options={HEADING_LEVELS.map((level) => ({
							label: `H${level}`,
							value: level,
						}))}
						onChange={(value) => setAttributes({ headingLevel: Number(value) })}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				{displayedTitle !== "" && (
					<HeadingTag className="inskrift-guestbook__title">
						{displayedTitle}
					</HeadingTag>
				)}
				<p className="inskrift-guestbook__placeholder">
					{__(
						"Guestbook entries and the submission form will appear here.",
						"inskrift",
					)}
				</p>
			</div>
		</>
	);
}
