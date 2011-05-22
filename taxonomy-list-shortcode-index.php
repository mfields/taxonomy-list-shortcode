<?php
/**
 * Index Template.
 *
 * @since 2011-02-13
 */

if ( ! defined( 'MFIELDS_TAXONOMY_LIST_SHORTCODE_DIR' ) ) {
	exit;
}

/* Split the array into smaller pieces + generate html to display lists. */
$chunked = array_chunk( $terms, ceil( count( $terms ) / $args['cols'] ) );

$o.= "\n\t" . '<div class="mf_taxonomy_list">';
foreach ( $chunked as $k => $column ) {
	$o.= "\n\t" . '<ul class="mf_taxonomy_column mf_cols_' . $args['cols'] . '">';
	foreach ( $column as $term ) {
		$url = get_term_link( $term, $term->taxonomy );
		$count = intval( $term->count );
		$style = '';
		$style.= ( $args['background'] != 'fff' ) ? ' background:#' . $args['background'] . ';' : '';
		$style.= ( $args['color'] != '000' ) ? ' color:#' . $args['color'] . ';' : '';
		$style = ( !empty( $style ) ) ? ' style="' . trim( $style ) . '"' : '';

		$class = ( $args['show_counts'] ) ? ' class="has-quantity"' : '';
		$quantity = ( $args['show_counts'] ) ? ' <span' . $style . ' class="quantity">' . $count . '</span>' : '';

		$o.= "\n\t\t" . '<li' . $class . $style . '><a' . $style . ' class="term-name" href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . '</a>' . mf_taxonomy_list_shortcode_edit_term_link( $term ) . '' . $quantity . '</li>';
	}
	$o.=  "\n\t" . '</ul>';
}
$o.=  "\n\t" . '<div class="clear"></div>';
$o.=  "\n\t" . '</div>';
?>