/**
 * BLOCK: Additional Accordion block
 */

// Import block dependencies and components
import Edit from './components/edit';
import Save from './components/save';

// Import CSS
import './styles/style.scss';
import './styles/editor.scss';

// Components
const { __ } = wp.i18n;

// Extend component
const { Component } = wp.element;

// Register block
const { registerBlockType } = wp.blocks;

const blockAttributes = {
	accordionTitle: {
		type: 'array',
		selector: '.ab-accordion-title',
		source: 'children',
	},
	accordionText: {
		type: 'array',
		selector: '.ab-accordion-text',
		source: 'children',
	},
	accordionAlignment: {
		type: 'string',
	},
	accordionFontSize: {
		type: 'number',
		default: 18,
	},
	accordionOpen: {
		type: 'boolean',
		default: false,
	},
};

// Register the block
registerBlockType( 'cgb/block-accordion', {
	title: __( 'Accordion', 'additional-blocks' ),
	description: __(
		'Add accordion block with a title and text.',
		'additional-blocks'
	),
	icon: 'editor-ul',
	category: 'common',
	keywords: [
		__( 'accordion', 'additional-blocks' ),
		__( 'additional-blocks', 'additional-blocks' ),
	],
	attributes: blockAttributes,

	ab_settings_data: {
		ab_accordion_accordionFontSize: {
			title: __( 'Title Font Size', 'additional-blocks' ),
		},
		ab_accordion_accordionOpen: {
			title: __( 'Open by default', 'additional-blocks' ),
		},
	},

	// Render the block components
	edit: ( props ) => {
		return <Edit { ...props } />;
	},

	// Save the attributes and markup
	save: ( props ) => {
		return <Save { ...props } />;
	},

} );
