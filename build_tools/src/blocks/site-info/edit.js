import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

const PARTS = [
	{ label: __( 'Footer text', 'joevernazza' ), value: 'footer-text' },
	{ label: __( 'Contact details', 'joevernazza' ), value: 'contact' },
	{ label: __( 'Social links', 'joevernazza' ), value: 'social' },
];

export default function Edit( { attributes, setAttributes, name } ) {
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Site Info', 'joevernazza' ) }>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Show', 'joevernazza' ) }
						value={ attributes.part }
						options={ PARTS }
						onChange={ ( part ) => setAttributes( { part } ) }
						help={ __(
							'Values are edited in Settings > Site Settings.',
							'joevernazza'
						) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<ServerSideRender block={ name } attributes={ attributes } />
			</div>
		</>
	);
}
