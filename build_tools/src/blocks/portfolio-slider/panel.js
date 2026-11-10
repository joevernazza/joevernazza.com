/**
 * "Slider images" panel in the portfolio entry editor's sidebar. Each slider
 * image is a group of desktop, tablet and mobile versions; add as many as you
 * like. Saved to post meta as [ { desktop, tablet, mobile } ] attachment IDs
 * (0 = not set), which the Portfolio Slider and Full Portfolio blocks read.
 *
 * Loaded with the Portfolio Slider block's editor script, so it's present in
 * every block editor but only renders for portfolio entries.
 */
import { __, sprintf } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import {
	PluginDocumentSettingPanel,
	store as editorStore,
} from '@wordpress/editor';
import { useSelect } from '@wordpress/data';
import { store as coreStore, useEntityProp } from '@wordpress/core-data';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { useMemo } from '@wordpress/element';

import { META_KEY } from './constants.js';

const VERSIONS = [
	{
		key: 'desktop',
		label: __( 'Desktop', 'joevernazza' ),
		hint: __( '1024px and wider', 'joevernazza' ),
	},
	{
		key: 'tablet',
		label: __( 'Tablet', 'joevernazza' ),
		hint: __( '768–1023px', 'joevernazza' ),
	},
	{
		key: 'mobile',
		label: __( 'Mobile', 'joevernazza' ),
		hint: __( 'Below 768px', 'joevernazza' ),
	},
];

const EMPTY_SLIDE = { desktop: 0, tablet: 0, mobile: 0 };

/**
 * One version slot (Desktop / Tablet / Mobile) of a slider image.
 *
 * @param {Object}   props
 * @param {Object}   props.version  Entry from VERSIONS.
 * @param {number}   props.id       Attachment ID, or 0.
 * @param {Object}   props.media    Media record, once loaded.
 * @param {string}   props.slideNo  "Slider image 2" etc., for button labels.
 * @param {Function} props.onChange Called with the new attachment ID (0 to clear).
 */
function VersionSlot( { version, id, media, slideNo, onChange } ) {
	const thumb =
		media?.media_details?.sizes?.thumbnail?.source_url ?? media?.source_url;
	const name = sprintf(
		/* translators: 1: "Desktop", "Tablet" or "Mobile", 2: "Slider image 2" etc. */
		__( '%1$s, %2$s', 'joevernazza' ),
		version.label,
		slideNo
	);

	return (
		<div className="portfolio-slider-panel__slot">
			<div className="portfolio-slider-panel__slot-label">
				<strong>{ version.label }</strong>
				<span>{ version.hint }</span>
			</div>
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ [ 'image' ] }
					value={ id || undefined }
					onSelect={ ( item ) => onChange( item.id ) }
					render={ ( { open } ) => (
						<div className="portfolio-slider-panel__slot-body">
							{ id ? (
								<button
									type="button"
									className="portfolio-slider-panel__thumb-button"
									onClick={ open }
									aria-label={ sprintf(
										/* translators: %s: e.g. "Desktop, Slider image 2". */
										__( 'Replace %s', 'joevernazza' ),
										name
									) }
								>
									{ thumb ? (
										<img
											src={ thumb }
											alt=""
											className="portfolio-slider-panel__thumb"
										/>
									) : (
										<span className="portfolio-slider-panel__thumb" />
									) }
								</button>
							) : (
								<Button
									variant="secondary"
									size="compact"
									onClick={ open }
									aria-label={ sprintf(
										/* translators: %s: e.g. "Desktop, Slider image 2". */
										__( 'Choose %s', 'joevernazza' ),
										name
									) }
								>
									{ __( 'Choose', 'joevernazza' ) }
								</Button>
							) }
							{ !! id && (
								<Button
									size="small"
									icon="no-alt"
									isDestructive
									label={ sprintf(
										/* translators: %s: e.g. "Desktop, Slider image 2". */
										__( 'Remove %s', 'joevernazza' ),
										name
									) }
									onClick={ () => onChange( 0 ) }
								/>
							) }
						</div>
					) }
				/>
			</MediaUploadCheck>
		</div>
	);
}

function SliderImagesPanel() {
	const [ meta, setMeta ] = useEntityProp( 'postType', 'portfolio', 'meta' );
	const slides = useMemo( () => meta?.[ META_KEY ] ?? [], [ meta ] );
	const setSlides = ( value ) => setMeta( { ...meta, [ META_KEY ]: value } );

	const ids = useMemo(
		() =>
			[
				...new Set(
					slides.flatMap( ( slide ) =>
						VERSIONS.map( ( v ) => slide[ v.key ] )
					)
				),
			].filter( Boolean ),
		[ slides ]
	);
	// Media records (undefined until loaded); useSelect compares shallowly.
	const records = useSelect(
		( select ) => ids.map( ( id ) => select( coreStore ).getMedia( id ) ),
		[ ids ]
	);
	const mediaById = Object.fromEntries(
		ids.map( ( id, i ) => [ id, records[ i ] ] )
	);

	const updateSlide = ( index, key, id ) =>
		setSlides(
			slides.map( ( slide, i ) =>
				i === index ? { ...EMPTY_SLIDE, ...slide, [ key ]: id } : slide
			)
		);
	const move = ( index, offset ) => {
		const next = [ ...slides ];
		const [ slide ] = next.splice( index, 1 );
		next.splice( index + offset, 0, slide );
		setSlides( next );
	};

	return (
		<PluginDocumentSettingPanel
			name="joevernazza-slider-images"
			title={ __( 'Slider images', 'joevernazza' ) }
			className="portfolio-slider-panel"
		>
			{ slides.length > 0 && (
				<ol className="portfolio-slider-panel__list">
					{ slides.map( ( slide, index ) => {
						const slideNo = sprintf(
							/* translators: %d: position in the slider. */
							__( 'Slider image %d', 'joevernazza' ),
							index + 1
						);
						return (
							<li
								key={ index }
								className="portfolio-slider-panel__slide"
							>
								<div className="portfolio-slider-panel__slide-header">
									<strong>{ slideNo }</strong>
									<div className="portfolio-slider-panel__actions">
										<Button
											size="small"
											icon="arrow-up-alt2"
											label={ sprintf(
												/* translators: %s: "Slider image 2" etc. */
												__(
													'Move %s earlier',
													'joevernazza'
												),
												slideNo
											) }
											disabled={ index === 0 }
											accessibleWhenDisabled
											onClick={ () => move( index, -1 ) }
										/>
										<Button
											size="small"
											icon="arrow-down-alt2"
											label={ sprintf(
												/* translators: %s: "Slider image 2" etc. */
												__(
													'Move %s later',
													'joevernazza'
												),
												slideNo
											) }
											disabled={
												index === slides.length - 1
											}
											accessibleWhenDisabled
											onClick={ () => move( index, 1 ) }
										/>
										<Button
											size="small"
											icon="trash"
											isDestructive
											label={ sprintf(
												/* translators: %s: "Slider image 2" etc. */
												__(
													'Remove %s',
													'joevernazza'
												),
												slideNo
											) }
											onClick={ () =>
												setSlides(
													slides.filter(
														( s, i ) => i !== index
													)
												)
											}
										/>
									</div>
								</div>
								{ VERSIONS.map( ( version ) => (
									<VersionSlot
										key={ version.key }
										version={ version }
										id={ slide[ version.key ] || 0 }
										media={
											mediaById[ slide[ version.key ] ]
										}
										slideNo={ slideNo }
										onChange={ ( id ) =>
											updateSlide(
												index,
												version.key,
												id
											)
										}
									/>
								) ) }
							</li>
						);
					} ) }
				</ol>
			) }

			<Button
				variant="secondary"
				__next40pxDefaultSize
				icon="plus"
				onClick={ () => setSlides( [ ...slides, { ...EMPTY_SLIDE } ] ) }
			>
				{ __( 'Add slider image', 'joevernazza' ) }
			</Button>

			<p className="portfolio-slider-panel__help">
				{ __(
					'A missing version uses the nearest one that’s set, so Desktop alone works everywhere. Empty slider images aren’t saved. The first one’s Desktop image is also the card image in the Full Portfolio grid. Alt text comes from the Desktop image’s alt text in the Media Library.',
					'joevernazza'
				) }
			</p>
		</PluginDocumentSettingPanel>
	);
}

function SliderImagesPanelForPortfolio() {
	const postType = useSelect(
		( select ) => select( editorStore ).getCurrentPostType(),
		[]
	);
	return postType === 'portfolio' ? <SliderImagesPanel /> : null;
}

registerPlugin( 'joevernazza-slider-images', {
	render: SliderImagesPanelForPortfolio,
} );
