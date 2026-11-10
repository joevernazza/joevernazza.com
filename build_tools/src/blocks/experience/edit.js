import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	FormTokenField,
	PanelBody,
	SelectControl,
} from '@wordpress/components';

const PLAIN = [];
const INLINE = [ 'core/bold', 'core/italic' ];

const LEVELS = [
	{ label: __( 'Heading 2', 'joevernazza' ), value: '2' },
	{ label: __( 'Heading 3', 'joevernazza' ), value: '3' },
	{ label: __( 'Heading 4', 'joevernazza' ), value: '4' },
];

export default function Edit( { attributes, setAttributes } ) {
	const { dates, position, company, description, tags, headingLevel } =
		attributes;
	const Heading = `h${ headingLevel }`;
	const blockProps = useBlockProps( {
		className: 'experience two-column',
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Tags', 'joevernazza' ) }>
					<FormTokenField
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Tags', 'joevernazza' ) }
						value={ tags }
						onChange={ ( value ) =>
							setAttributes( {
								tags: value
									.map( ( tag ) =>
										( typeof tag === 'string'
											? tag
											: tag.value
										).trim()
									)
									.filter( Boolean ),
							} )
						}
					/>
					<p>
						{ __(
							'Type a tag and press Enter. Use Backspace or × to remove one.',
							'joevernazza'
						) }
					</p>
				</PanelBody>
				<PanelBody
					title={ __( 'Settings', 'joevernazza' ) }
					initialOpen={ false }
				>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Heading level', 'joevernazza' ) }
						help={ __(
							'Heading 3 under a section’s Heading 2 (e.g. “Experience”).',
							'joevernazza'
						) }
						value={ String( headingLevel ) }
						options={ LEVELS }
						onChange={ ( value ) =>
							setAttributes( { headingLevel: Number( value ) } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<article { ...blockProps }>
				<div className="column experience__meta">
					<RichText
						tagName="p"
						className="experience__dates"
						placeholder={ __( 'Date range…', 'joevernazza' ) }
						value={ dates }
						onChange={ ( value ) =>
							setAttributes( { dates: value } )
						}
						allowedFormats={ PLAIN }
					/>
				</div>
				<div className="column experience__body">
					<Heading className="experience__heading">
						<RichText
							tagName="span"
							className="experience__position"
							placeholder={ __( 'Position…', 'joevernazza' ) }
							value={ position }
							onChange={ ( value ) =>
								setAttributes( { position: value } )
							}
							allowedFormats={ PLAIN }
						/>{ ' ' }
						<span
							className="experience__separator"
							aria-hidden="true"
						>
							·
						</span>{ ' ' }
						<RichText
							tagName="span"
							className="experience__company"
							placeholder={ __( 'Company…', 'joevernazza' ) }
							value={ company }
							onChange={ ( value ) =>
								setAttributes( { company: value } )
							}
							allowedFormats={ PLAIN }
						/>
					</Heading>
					<RichText
						tagName="p"
						className="experience__description"
						placeholder={ __( 'Description…', 'joevernazza' ) }
						value={ description }
						onChange={ ( value ) =>
							setAttributes( { description: value } )
						}
						allowedFormats={ INLINE }
					/>
					{ tags.length > 0 && (
						<ul
							className="experience__tags"
							aria-label={ __( 'Skills', 'joevernazza' ) }
						>
							{ tags.map( ( tag ) => (
								<li key={ tag } className="experience__tag">
									{ tag }
								</li>
							) ) }
						</ul>
					) }
				</div>
			</article>
		</>
	);
}
