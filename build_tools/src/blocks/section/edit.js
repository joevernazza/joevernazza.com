import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

export default function Edit() {
	// The inner blocks sit directly in the <section>.
	return <section { ...useInnerBlocksProps( useBlockProps() ) } />;
}
