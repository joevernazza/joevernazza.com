import { registerBlockType } from '@wordpress/blocks';

import metadata from './block.json';
import Edit from './edit.js';
// Sidebar panels appear in the order they're registered.
import './details-panel.js';
import './panel.js';
import './editor.scss';
import './style.scss';

registerBlockType( metadata.name, {
	edit: Edit,
	// Dynamic block: render.php outputs the slider from the entry's meta.
	save: () => null,
} );
