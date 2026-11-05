import { registerBlockType } from '@wordpress/blocks';

import metadata from './block.json';
import Edit from './edit.js';
import './style.scss';

// Dynamic block: render.php outputs the markup from the saved attributes.
registerBlockType( metadata.name, { edit: Edit, save: () => null } );
