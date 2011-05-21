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

$o.= "\n\t" . '<dl class="taxonomy-glossary">';
foreach ( (array) $terms as $term ) {
	$url = get_term_link( $term, $term->taxonomy );
	$count = intval( $term->count );
	$style = '';
	$style.= ( $background != 'fff' ) ? ' background:#' . $background . ';' : '';
	$style.= ( $color != '000' ) ? ' color:#' . $color . ';' : '';
	$style = ( !empty( $style ) ) ? ' style="' . esc_attr( trim( $style ) ) . '"' : '';

	$o.= "\n\t\t" . '<a id="' . esc_attr( $term->slug ) . '"></a>';
	$o.= "\n\t\t" . '<dt class="taxonomy-glossary-term"' . $style . '><a' . $style . ' class="term-name" href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . '</a>' . mf_taxonomy_list_shortcode_edit_term_link( $term ) . '</dt>';
	$o.= "\n\t\t" . '<dd class="taxonomy-glossary-definition"' . $style . '>' . term_description( $term->term_id, $term->taxonomy ) . '</dd>' . "\n";
}
$o.= "\n\t" . '</dl>';
?>