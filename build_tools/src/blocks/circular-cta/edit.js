import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';

// Formatting available in the circle's text. Links are only offered when the
// circle itself isn't a link: a link inside a link is invalid HTML.
const TEXT_FORMATS = [
	'core/bold',
	'core/italic',
	'core/strikethrough',
	'core/subscript',
	'core/superscript',
];

const SIZES = [
	{ label: __( 'Small', 'joevernazza' ), value: 'small' },
	{ label: __( 'Medium', 'joevernazza' ), value: 'medium' },
	{ label: __( 'Large', 'joevernazza' ), value: 'large' },
];

export default function Edit( { attributes, setAttributes } ) {
	const { content, url, opensInNewTab, size } = attributes;
	const blockProps = useBlockProps( {
		className: `circular-cta circular-cta--${ size }`,
	} );
	const formats = url ? TEXT_FORMATS : [ ...TEXT_FORMATS, 'core/link' ];

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Link', 'joevernazza' ) }>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="url"
						label={ __( 'URL', 'joevernazza' ) }
						help={ __(
							'Makes the whole circle a link. Leave empty for a plain circle.',
							'joevernazza'
						) }
						value={ url }
						onChange={ ( value ) =>
							setAttributes( { url: value } )
						}
					/>
					{ url && (
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Open in new tab', 'joevernazza' ) }
							checked={ opensInNewTab }
							onChange={ ( value ) =>
								setAttributes( { opensInNewTab: value } )
							}
						/>
					) }
				</PanelBody>
				<PanelBody title={ __( 'Size', 'joevernazza' ) }>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Circle size', 'joevernazza' ) }
						value={ size }
						options={ SIZES }
						onChange={ ( value ) =>
							setAttributes( { size: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="container">
					<RichText
						tagName="span"
						className="circular-cta__text"
						placeholder={ __( 'Call to action…', 'joevernazza' ) }
						value={ content }
						onChange={ ( value ) =>
							setAttributes( { content: value } )
						}
						allowedFormats={ formats }
					/>
				</div>
			</div>
		</>
	);
}
