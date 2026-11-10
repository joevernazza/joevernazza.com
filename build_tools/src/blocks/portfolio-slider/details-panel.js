/**
 * "Project details" panel in the portfolio entry editor's sidebar: the
 * entry's Role and Brand, saved to post meta (registered in
 * mu-plugins/joevernazza/inc/portfolio.php). On the entry's page the Sticky
 * Left Side header shows them under the title: Role, then Brand.
 *
 * Loaded with the Portfolio Slider block's editor script, so it's present in
 * every block editor but only renders for portfolio entries.
 */
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import {
	PluginDocumentSettingPanel,
	store as editorStore,
} from '@wordpress/editor';
import { useSelect } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';
import { Flex, TextControl } from '@wordpress/components';

import { BRAND_META_KEY, ROLE_META_KEY } from './constants.js';

function ProjectDetailsPanel() {
	const [ meta, setMeta ] = useEntityProp( 'postType', 'portfolio', 'meta' );

	return (
		<PluginDocumentSettingPanel
			name="joevernazza-project-details"
			title={ __( 'Project details', 'joevernazza' ) }
		>
			<Flex direction="column" gap={ 4 }>
				<TextControl
					__next40pxDefaultSize
					__nextHasNoMarginBottom
					label={ __( 'Role', 'joevernazza' ) }
					help={ __( 'Shown under the brand.', 'joevernazza' ) }
					value={ meta?.[ ROLE_META_KEY ] ?? '' }
					onChange={ ( value ) =>
						setMeta( { ...meta, [ ROLE_META_KEY ]: value } )
					}
				/>
				<TextControl
					__next40pxDefaultSize
					__nextHasNoMarginBottom
					label={ __( 'Brand', 'joevernazza' ) }
					value={ meta?.[ BRAND_META_KEY ] ?? '' }
					onChange={ ( value ) =>
						setMeta( { ...meta, [ BRAND_META_KEY ]: value } )
					}
				/>
			</Flex>
		</PluginDocumentSettingPanel>
	);
}

function ProjectDetailsPanelForPortfolio() {
	const postType = useSelect(
		( select ) => select( editorStore ).getCurrentPostType(),
		[]
	);
	return postType === 'portfolio' ? <ProjectDetailsPanel /> : null;
}

registerPlugin( 'joevernazza-project-details', {
	render: ProjectDetailsPanelForPortfolio,
} );
