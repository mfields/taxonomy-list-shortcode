<?php
/**
 * Glossary Template.
 *
 * Displays taxonomy terms + descriptions in a definition list.
 *
 * @since 2011-02-13
 */

if ( ! defined( 'MFIELDS_TAXONOMY_LIST_SHORTCODE_DIR' ) ) {
	exit;
}

$o.= "\n\n\n" . '<dl class="taxonomy-glossary">';
foreach ( (array) $terms as $term ) {
	$o.= "\n" . '<dt  id="' . esc_attr( $term->slug ) . '" class="taxonomy-glossary-term"><a class="term-name" href="' . esc_url( get_term_link( $term, $term->taxonomy ) ) . '">' . esc_html( $term->name ) . '</a>' . mf_taxonomy_list_shortcode_edit_term_link( $term ) . '</dt>';
	$o.= "\n" . '<dd class="taxonomy-glossary-definition">' . term_description( $term->term_id, $term->taxonomy ) . '</dd>';
}
$o.= "\n" . '</dl>';
?>