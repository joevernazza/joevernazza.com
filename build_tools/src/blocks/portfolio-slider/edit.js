import { __, sprintf, _n } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';

import { META_KEY } from './constants.js';

/**
 * Editor preview. In the Single Portfolio template there's no specific entry,
 * so it explains where the images come from; inside an entry it shows how
 * many images the slider has.
 *
 * @param {Object} props         Block props.
 * @param {Object} props.context Block context (postId, postType).
 */
export default function Edit( { context } ) {
	const blockProps = useBlockProps( { className: 'portfolio-slider' } );
	const isEntry = context.postType === 'portfolio' && !! context.postId;
	const count = useSelect(
		( select ) => {
			if ( ! isEntry ) {
				return 0;
			}
			const entry = select( coreStore ).getEditedEntityRecord(
				'postType',
				'portfolio',
				context.postId
			);
			return ( entry?.meta?.[ META_KEY ] ?? [] ).filter(
				( slide ) => slide?.desktop || slide?.tablet || slide?.mobile
			).length;
		},
		[ isEntry, context.postId ]
	);

	let instructions = __(
		'Shows the portfolio entry’s slider images. Choose them in each entry’s “Slider images” panel.',
		'joevernazza'
	);
	if ( isEntry ) {
		instructions = count
			? sprintf(
					/* translators: %d: number of images. */
					_n(
						'%d image. Edit them in the “Slider images” panel.',
						'%d images. Edit them in the “Slider images” panel.',
						count,
						'joevernazza'
					),
					count
				)
			: __(
					'No images yet. Add them in the “Slider images” panel.',
					'joevernazza'
				);
	}

	return (
		<div { ...blockProps }>
			<Placeholder
				icon="images-alt2"
				label={ __( 'Portfolio Slider', 'joevernazza' ) }
				instructions={ instructions }
			/>
		</div>
	);
}
