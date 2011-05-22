<?php
/**
 * Definition List Template.
 *
 * Displays taxonomy terms + descriptions in a definition list.
 *
 * @since 2011-02-13
 */

if ( ! defined( 'MFIELDS_TAXONOMY_LIST_SHORTCODE_DIR' ) ) {
	exit;
}

$o.= "\n\n\n" . '<dl class="taxonomy-definition-list">';
foreach ( (array) $terms as $term ) {
	$o.= "\n" . '<dt  id="' . esc_attr( $term->slug ) . '" class="taxonomy-glossary-term">' . esc_html( $term->name ) . mf_taxonomy_list_shortcode_edit_term_link( $term ) . '</dt>';
	$o.= apply_filters( 'taxonomy-list-term-description', '', array(
		'term'   => $term,
		'before' => '<dd class="taxonomy-glossary-definition">',
		'after'  => '</dd>',
	) );
}
$o.= "\n" . '</dl>';
?>