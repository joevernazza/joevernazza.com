import { __, sprintf } from '@wordpress/i18n';
import {
	InspectorControls,
	URLInput,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	TextControl,
	ToggleControl,
} from '@wordpress/components';

const NEW_ITEM = { text: '', url: '', opensInNewTab: false };

/**
 * One menu item's settings: text, URL (with page search), new tab, move and
 * remove.
 *
 * @param {Object}   props
 * @param {Object}   props.item     { text, url, opensInNewTab }.
 * @param {number}   props.index    Position in the list.
 * @param {number}   props.count    Number of items.
 * @param {Function} props.onChange Called with the changed fields.
 * @param {Function} props.onMove   Called with -1 or 1.
 * @param {Function} props.onRemove Removes the item.
 */
function MenuItemSettings( {
	item,
	index,
	count,
	onChange,
	onMove,
	onRemove,
} ) {
	const name =
		item.text ||
		sprintf(
			/* translators: %d: position in the menu. */
			__( 'Menu item %d', 'joevernazza' ),
			index + 1
		);

	return (
		<fieldset className="menu-panel__item">
			<legend className="menu-panel__legend">{ name }</legend>
			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Text', 'joevernazza' ) }
				value={ item.text ?? '' }
				onChange={ ( text ) => onChange( { text } ) }
			/>
			<URLInput
				__nextHasNoMarginBottom
				label={ __( 'URL', 'joevernazza' ) }
				value={ item.url ?? '' }
				onChange={ ( url, post ) =>
					// Picking a page from the search fills in empty text.
					onChange(
						post?.title && ! item.text
							? { url, text: post.title }
							: { url }
					)
				}
			/>
			<p className="menu-panel__help">
				{ __(
					'Type a URL, or search for a page by name.',
					'joevernazza'
				) }
			</p>
			<ToggleControl
				__nextHasNoMarginBottom
				label={ __( 'Open in new tab', 'joevernazza' ) }
				checked={ !! item.opensInNewTab }
				onChange={ ( opensInNewTab ) => onChange( { opensInNewTab } ) }
			/>
			<div className="menu-panel__actions">
				<Button
					size="small"
					icon="arrow-up-alt2"
					label={ sprintf(
						/* translators: %s: menu item name. */
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
						/* translators: %s: menu item name. */
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
	const { items, label } = attributes;
	const blockProps = useBlockProps( { className: 'menu' } );

	const setItems = ( next ) => setAttributes( { items: next } );
	const addItem = () => setItems( [ ...items, { ...NEW_ITEM } ] );
	const updateItem = ( index, fields ) =>
		setItems(
			items.map( ( item, i ) =>
				i === index ? { ...item, ...fields } : item
			)
		);
	const moveItem = ( index, offset ) => {
		const next = [ ...items ];
		const [ item ] = next.splice( index, 1 );
		next.splice( index + offset, 0, item );
		setItems( next );
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Menu items', 'joevernazza' ) }>
					{ items.map( ( item, index ) => (
						<MenuItemSettings
							key={ index }
							item={ item }
							index={ index }
							count={ items.length }
							onChange={ ( fields ) =>
								updateItem( index, fields )
							}
							onMove={ ( offset ) => moveItem( index, offset ) }
							onRemove={ () =>
								setItems(
									items.filter( ( _, i ) => i !== index )
								)
							}
						/>
					) ) }
					<Button variant="secondary" onClick={ addItem }>
						{ __( 'Add menu item', 'joevernazza' ) }
					</Button>
				</PanelBody>
				<PanelBody
					title={ __( 'Menu settings', 'joevernazza' ) }
					initialOpen={ false }
				>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Menu name', 'joevernazza' ) }
						help={ __(
							'Read out by screen readers, e.g. “Main” or “Footer”. Give each menu on a page a different name. Default: “Menu”.',
							'joevernazza'
						) }
						value={ label }
						onChange={ ( value ) =>
							setAttributes( { label: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<nav
				{ ...blockProps }
				aria-label={ label || __( 'Menu', 'joevernazza' ) }
			>
				{ items.length > 0 ? (
					<ul className="menu__list">
						{ items.map( ( item, index ) => (
							<li
								key={ index }
								className="menu__item animate animate--fade"
							>
								{ /* A <span>, not a link: clicking selects the block instead of navigating the editor. */ }
								<span className="menu__link">
									{ item.text ||
										__( '(no text)', 'joevernazza' ) }
								</span>
							</li>
						) ) }
					</ul>
				) : (
					<Button variant="secondary" onClick={ addItem }>
						{ __( 'Add menu item', 'joevernazza' ) }
					</Button>
				) }
			</nav>
		</>
	);
}
