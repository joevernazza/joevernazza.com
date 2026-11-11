import { __, sprintf } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	Button,
	ComboboxControl,
	Disabled,
	Icon,
	PanelBody,
	RangeControl,
	SelectControl,
} from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';
import ServerSideRender from '@wordpress/server-side-render';

const LEVELS = [
	{ label: __( 'Heading 2', 'joevernazza' ), value: '2' },
	{ label: __( 'Heading 3', 'joevernazza' ), value: '3' },
	{ label: __( 'Heading 4', 'joevernazza' ), value: '4' },
];

const ENTRY_QUERY = {
	per_page: 100,
	status: 'publish',
	orderby: 'title',
	order: 'asc',
	_fields: 'id,title',
};

/**
 * "Portfolio entries" panel: add entries and arrange their order (drag, or
 * the arrow buttons), or remove them.
 *
 * @param {Object}   props
 * @param {number[]} props.entries  Picked entry IDs, in order.
 * @param {Function} props.onChange Called with the new ID list.
 */
function EntriesPanel( { entries, onChange } ) {
	const posts = useSelect(
		( select ) =>
			select( coreStore ).getEntityRecords(
				'postType',
				'portfolio',
				ENTRY_QUERY
			),
		[]
	);
	const titleOf = ( id ) => {
		const post = posts?.find( ( p ) => p.id === id );
		if ( ! post ) {
			return posts
				? __( '(unpublished or deleted)', 'joevernazza' )
				: __( 'Loading…', 'joevernazza' );
		}
		return (
			decodeEntities( post.title?.rendered ?? '' ) ||
			__( '(no title)', 'joevernazza' )
		);
	};

	const options = ( posts ?? [] )
		.filter( ( post ) => ! entries.includes( post.id ) )
		.map( ( post ) => ( {
			value: String( post.id ),
			label:
				decodeEntities( post.title?.rendered ?? '' ) ||
				__( '(no title)', 'joevernazza' ),
		} ) );

	// Drag and drop: while dragging, the list reorders live in local state
	// (dragOrder); dropping saves it, dropping elsewhere cancels.
	const [ dragId, setDragId ] = useState( null );
	const [ dragOrder, setDragOrder ] = useState( null );
	const list = dragOrder ?? entries;

	const dragStart = ( event, id ) => {
		event.dataTransfer.effectAllowed = 'move';
		event.dataTransfer.setData( 'text/plain', String( id ) ); // Firefox needs data to start a drag.
		setDragId( id );
		setDragOrder( entries );
	};
	const dragOver = ( event, overId ) => {
		if ( dragId === null ) {
			return;
		}
		event.preventDefault(); // Allows dropping here.
		if ( overId === dragId ) {
			return;
		}
		setDragOrder( ( order ) => {
			const next = order.filter( ( id ) => id !== dragId );
			next.splice( order.indexOf( overId ), 0, dragId );
			return next;
		} );
	};
	const drop = ( event ) => {
		if ( dragId === null ) {
			return;
		}
		event.preventDefault();
		if ( dragOrder && dragOrder.join() !== entries.join() ) {
			onChange( dragOrder );
		}
		setDragId( null );
		setDragOrder( null );
	};
	const dragEnd = () => {
		setDragId( null );
		setDragOrder( null );
	};

	const move = ( index, offset ) => {
		const next = [ ...entries ];
		const [ id ] = next.splice( index, 1 );
		next.splice( index + offset, 0, id );
		onChange( next );
	};

	return (
		<PanelBody title={ __( 'Portfolio entries', 'joevernazza' ) }>
			<p>
				{ entries.length
					? __(
							'Shown in this order. Drag them or use the arrows to reorder.',
							'joevernazza'
						)
					: __(
							'Add the portfolio entries to show.',
							'joevernazza'
						) }
			</p>

			{ entries.length > 0 && (
				<ol
					className="projects-panel__list"
					onDragOver={ ( event ) =>
						dragId !== null && event.preventDefault()
					}
					onDrop={ drop }
				>
					{ list.map( ( id, index ) => {
						const title = titleOf( id );
						return (
							<li
								key={ id }
								className="projects-panel__item"
								draggable
								onDragStart={ ( event ) =>
									dragStart( event, id )
								}
								onDragOver={ ( event ) =>
									dragOver( event, id )
								}
								onDragEnd={ dragEnd }
							>
								<span className="projects-panel__title">
									<Icon icon="move" size={ 16 } /> { title }
								</span>
								<span className="projects-panel__actions">
									<Button
										size="small"
										icon="arrow-up-alt2"
										label={ sprintf(
											/* translators: %s: entry title. */
											__(
												'Move %s earlier',
												'joevernazza'
											),
											title
										) }
										disabled={ index === 0 }
										accessibleWhenDisabled
										onClick={ () => move( index, -1 ) }
									/>
									<Button
										size="small"
										icon="arrow-down-alt2"
										label={ sprintf(
											/* translators: %s: entry title. */
											__(
												'Move %s later',
												'joevernazza'
											),
											title
										) }
										disabled={
											index === entries.length - 1
										}
										accessibleWhenDisabled
										onClick={ () => move( index, 1 ) }
									/>
									<Button
										size="small"
										icon="no-alt"
										isDestructive
										label={ sprintf(
											/* translators: %s: entry title. */
											__( 'Remove %s', 'joevernazza' ),
											title
										) }
										onClick={ () =>
											onChange(
												entries.filter(
													( e ) => e !== id
												)
											)
										}
									/>
								</span>
							</li>
						);
					} ) }
				</ol>
			) }

			<ComboboxControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Add an entry', 'joevernazza' ) }
				help={
					posts && ! options.length
						? __(
								'Every published entry is already added.',
								'joevernazza'
							)
						: undefined
				}
				value={ null }
				options={ options }
				onChange={ ( value ) =>
					value && onChange( [ ...entries, Number( value ) ] )
				}
			/>
		</PanelBody>
	);
}

export default function Edit( { attributes, setAttributes, name } ) {
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<EntriesPanel
					entries={ attributes.entries ?? [] }
					onChange={ ( entries ) => setAttributes( { entries } ) }
				/>
				<PanelBody
					title={ __( 'Settings', 'joevernazza' ) }
					initialOpen={ false }
				>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Entry title level', 'joevernazza' ) }
						help={ __(
							'Heading 3 under a section’s Heading 2 (e.g. “Projects”).',
							'joevernazza'
						) }
						value={ String( attributes.headingLevel ) }
						options={ LEVELS }
						onChange={ ( value ) =>
							setAttributes( { headingLevel: Number( value ) } )
						}
					/>
					<RangeControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __(
							'Text length (characters)',
							'joevernazza'
						) }
						help={ __(
							'The text is cut at the last whole word within this length, then “…” is added.',
							'joevernazza'
						) }
						value={ attributes.excerptLength }
						min={ 20 }
						max={ 200 }
						onChange={ ( value ) =>
							setAttributes( { excerptLength: value ?? 100 } )
						}
					/>
					<RangeControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __(
							'Mobile text length (characters)',
							'joevernazza'
						) }
						help={ __(
							'Used below tablet width (768px).',
							'joevernazza'
						) }
						value={ attributes.mobileExcerptLength }
						min={ 20 }
						max={ 200 }
						onChange={ ( value ) =>
							setAttributes( {
								mobileExcerptLength: value ?? 50,
							} )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				{ /* Disabled: the preview's links must not navigate the editor canvas. */ }
				<Disabled>
					<ServerSideRender
						block={ name }
						attributes={ attributes }
					/>
				</Disabled>
			</div>
		</>
	);
}
