import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

// The links depend on the portfolio entry being viewed, so the editor shows
// the same structure with labels instead (render.php has the real output).
export default function Edit() {
	const blockProps = useBlockProps( { className: 'page_pagination' } );

	return (
		<div { ...blockProps }>
			<p className="page_pagination__prev">
				{ __( '← Previous project', 'joevernazza' ) }
			</p>
			<p className="page_pagination__next">
				{ __( 'Next project →', 'joevernazza' ) }
			</p>
		</div>
	);
}
