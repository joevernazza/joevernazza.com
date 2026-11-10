import { registerBlockType } from '@wordpress/blocks';

import metadata from './block.json';
import Edit from './edit.js';
import './style.scss';

registerBlockType( metadata.name, {
	edit: Edit,
	// Dynamic block: render.php outputs the markup from the attributes.
	save: () => null,
} );
