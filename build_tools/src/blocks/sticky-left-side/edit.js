import { __, sprintf } from '@wordpress/i18n';
import {
	InnerBlocks,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	TextareaControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';

const HEADING_FORMATS = [ 'core/bold', 'core/italic' ];

// The menu slot holds exactly one Menu block, which can't be moved or
// removed; its items are edited by selecting it.
const MENU_TEMPLATE = [ [ 'joevernazza/menu', { label: 'Main' } ] ];

/**
 * Preview URL for pasted SVG code. Shown in an <img>, so nothing in the code
 * can run in the editor; the front end prints it inline, sanitized.
 *
 * @param {string} svg SVG markup.
 * @return {string} data: URL, or '' when there's no <svg>.
 */
const svgPreviewSrc = ( svg ) =>
	svg && svg.includes( '<svg' )
		? `data:image/svg+xml;charset=utf-8,${ encodeURIComponent( svg.trim() ) }`
		: '';

/**
 * One social link's settings: icon, tooltip, URL, move and remove.
 *
 * @param {Object}   props
 * @param {Object}   props.social   { iconId, svg, tooltip, url }.
 * @param {string}   props.iconSrc  Icon preview URL.
 * @param {number}   props.index    Position in the list.
 * @param {number}   props.count    Number of links.
 * @param {Function} props.onChange Called with the changed fields.
 * @param {Function} props.onMove   Called with -1 or 1.
 * @param {Function} props.onRemove Removes the link.
 */
function SocialItem( {
	social,
	iconSrc,
	index,
	count,
	onChange,
	onMove,
	onRemove,
} ) {
	const name =
		social.tooltip ||
		sprintf(
			/* translators: %d: position in the list. */
			__( 'Social link %d', 'joevernazza' ),
			index + 1
		);

	return (
		<fieldset className="sticky-left-side-panel__item">
			<legend className="sticky-left-side-panel__legend">{ name }</legend>
			<div className="sticky-left-side-panel__icon">
				{ ( svgPreviewSrc( social.svg ) || iconSrc ) && (
					<img
						src={ svgPreviewSrc( social.svg ) || iconSrc }
						alt=""
						width="32"
					/>
				) }
				<MediaUploadCheck>
					<MediaUpload
						allowedTypes={ [ 'image' ] }
						value={ social.iconId }
						onSelect={ ( media ) =>
							onChange( { iconId: media.id } )
						}
						render={ ( { open } ) => (
							<Button variant="secondary" onClick={ open }>
								{ social.iconId
									? __( 'Replace icon', 'joevernazza' )
									: __( 'Choose icon', 'joevernazza' ) }
							</Button>
						) }
					/>
				</MediaUploadCheck>
			</div>
			<TextareaControl
				__nextHasNoMarginBottom
				className="sticky-left-side-panel__svg"
				label={ __( 'Or paste SVG code', 'joevernazza' ) }
				help={ __(
					'Paste the whole <svg>…</svg>. It’s used instead of the image and printed inline, so CSS can color it (use fill="currentColor" to follow the text color). Scripts and event attributes are removed.',
					'joevernazza'
				) }
				value={ social.svg ?? '' }
				onChange={ ( svg ) => onChange( { svg } ) }
				rows={ 4 }
			/>
			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Icon tooltip', 'joevernazza' ) }
				help={ __(
					'Also the link’s name for screen readers, e.g. “LinkedIn”. Without one, the site’s domain is used.',
					'joevernazza'
				) }
				value={ social.tooltip ?? '' }
				onChange={ ( tooltip ) => onChange( { tooltip } ) }
			/>
			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				type="url"
				label={ __( 'URL', 'joevernazza' ) }
				value={ social.url ?? '' }
				onChange={ ( url ) => onChange( { url } ) }
			/>
			<div className="sticky-left-side-panel__actions">
				<Button
					size="small"
					icon="arrow-up-alt2"
					label={ sprintf(
						/* translators: %s: social link name. */
						__( 'Move %s earlier', 'joevernazza' ),
						name
					) }
					disabled={ index === 0 }
					accessibleWhenDisabled
					onClick={ () => onMove( -1 ) }
				/>
				<Button
					size="small"
					icon="arrow-down-alt2"
					label={ sprintf(
						/* translators: %s: social link name. */
						__( 'Move %s later', 'joevernazza' ),
						name
					) }
					disabled={ index === count - 1 }
					accessibleWhenDisabled
					onClick={ () => onMove( 1 ) }
				/>
				<Button
					size="small"
					variant="link"
					isDestructive
					onClick={ onRemove }
				>
					{ __( 'Remove', 'joevernazza' ) }
				</Button>
			</div>
		</fieldset>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const { headline, title, text, socials, socialsNewTab, backText, backUrl } =
		attributes;
	const blockProps = useBlockProps( { className: 'sticky-left-side' } );

	const iconSrcs = useSelect(
		( select ) =>
			socials.map( ( { iconId } ) => {
				const media = iconId
					? select( coreStore ).getMedia( iconId )
					: null;
				return (
					media?.media_details?.sizes?.thumbnail?.source_url ??
					media?.source_url
				);
			} ),
		[ socials ]
	);

	const setSocials = ( next ) => setAttributes( { socials: next } );
	const updateSocial = ( index, fields ) =>
		setSocials(
			socials.map( ( social, i ) =>
				i === index ? { ...social, ...fields } : social
			)
		);
	const moveSocial = ( index, offset ) => {
		const next = [ ...socials ];
		const [ social ] = next.splice( index, 1 );
		next.splice( index + offset, 0, social );
		setSocials( next );
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Social icons', 'joevernazza' ) }>
					{ socials.map( ( social, index ) => (
						<SocialItem
							key={ index }
							social={ social }
							iconSrc={ iconSrcs[ index ] }
							index={ index }
							count={ socials.length }
							onChange={ ( fields ) =>
								updateSocial( index, fields )
							}
							onMove={ ( offset ) => moveSocial( index, offset ) }
							onRemove={ () =>
								setSocials(
									socials.filter( ( _, i ) => i !== index )
								)
							}
						/>
					) ) }
					<Button
						variant="secondary"
						onClick={ () =>
							setSocials( [
								...socials,
								{ iconId: 0, svg: '', tooltip: '', url: '' },
							] )
						}
					>
						{ __( 'Add social icon', 'joevernazza' ) }
					</Button>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Open social links in a new tab',
							'joevernazza'
						) }
						checked={ socialsNewTab }
						onChange={ ( value ) =>
							setAttributes( { socialsNewTab: value } )
						}
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Portfolio pages', 'joevernazza' ) }
					initialOpen={ false }
				>
					<p>
						{ __(
							'On a portfolio entry the headline, title and text are the entry’s title, brand and content, with this link above them.',
							'joevernazza'
						) }
					</p>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Back link text', 'joevernazza' ) }
						value={ backText }
						onChange={ ( value ) =>
							setAttributes( { backText: value } )
						}
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Back link URL', 'joevernazza' ) }
						help={ __(
							'E.g. /#projects for the Projects section on the home page.',
							'joevernazza'
						) }
						value={ backUrl }
						onChange={ ( value ) =>
							setAttributes( { backUrl: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<header { ...blockProps }>
				<RichText
					tagName="h1"
					className="sticky-left-side__headline"
					placeholder={ __( 'Headline…', 'joevernazza' ) }
					value={ headline }
					onChange={ ( value ) =>
						setAttributes( { headline: value } )
					}
					allowedFormats={ HEADING_FORMATS }
				/>
				<RichText
					tagName="p"
					className="sticky-left-side__title small"
					placeholder={ __( 'Title…', 'joevernazza' ) }
					value={ title }
					onChange={ ( value ) => setAttributes( { title: value } ) }
					allowedFormats={ HEADING_FORMATS }
				/>
				<RichText
					tagName="p"
					className="sticky-left-side__text"
					placeholder={ __( 'Text…', 'joevernazza' ) }
					value={ text }
					onChange={ ( value ) => setAttributes( { text: value } ) }
				/>
				<InnerBlocks
					allowedBlocks={ [ 'joevernazza/menu' ] }
					template={ MENU_TEMPLATE }
					templateLock="all"
				/>
				{ socials.length > 0 && (
					<ul
						className="sticky-left-side__social"
						aria-label={ __( 'Social media', 'joevernazza' ) }
					>
						{ socials.map( ( social, index ) => (
							<li
								key={ index }
								className="sticky-left-side__social-item"
							>
								<span className="sticky-left-side__social-link">
									{ ( svgPreviewSrc( social.svg ) ||
										iconSrcs[ index ] ) && (
										<img
											className="sticky-left-side__social-icon"
											src={
												svgPreviewSrc( social.svg ) ||
												iconSrcs[ index ]
											}
											alt=""
										/>
									) }
									{ social.tooltip && (
										<span className="sticky-left-side__social-tooltip">
											{ social.tooltip }
										</span>
									) }
								</span>
							</li>
						) ) }
					</ul>
				) }
			</header>
		</>
	);
}
