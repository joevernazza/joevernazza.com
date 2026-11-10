import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	RichText,
	URLInput,
	useBlockProps,
} from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';

const FORMATS = [ 'core/bold', 'core/italic' ];

// The theme's arrow icon, from inc/blocks.php ('' when the theme has none).
const ICON_URL = window.joevernazzaButtonLink?.iconUrl ?? '';

export default function Edit( { attributes, setAttributes } ) {
	const { text, url, opensInNewTab } = attributes;
	const blockProps = useBlockProps( { className: 'button' } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Link', 'joevernazza' ) }>
					<URLInput
						__nextHasNoMarginBottom
						label={ __( 'URL', 'joevernazza' ) }
						value={ url }
						onChange={ ( value ) =>
							setAttributes( { url: value } )
						}
					/>
					<p>
						{ __(
							'Type a URL, or search for a page by name. Without a URL the button isn’t shown.',
							'joevernazza'
						) }
					</p>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Open in new tab', 'joevernazza' ) }
						checked={ opensInNewTab }
						onChange={ ( value ) =>
							setAttributes( { opensInNewTab: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ /* A <span class="button__link"> stands in for the <a>, so clicking edits the text instead of navigating. */ }
				<span className="button__link">
					<RichText
						tagName="span"
						placeholder={ __( 'Button text…', 'joevernazza' ) }
						value={ text }
						onChange={ ( value ) =>
							setAttributes( { text: value } )
						}
						allowedFormats={ FORMATS }
					/>
					{ ICON_URL && (
						<>
							{ ' ' }
							<img
								className="button__icon"
								src={ ICON_URL }
								alt=""
								width="10"
								height="10"
							/>
						</>
					) }
				</span>
			</div>
		</>
	);
}
