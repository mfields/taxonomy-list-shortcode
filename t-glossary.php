<?php
/**
 * Glossary Template.
 * Displays taxonomy terms + desctiptions in a definition list.
 * 
 * Will only display terms that have descriptions.
 * Ignores the paging parameter.
 * 
 * 
 * @since 2010-09-15
 * @uses shortcode_atts()
 * @uses get_terms()
 * @uses mf_taxonomy_list_sanitize_cols()
 * @uses taxonomy_exists()
 * @uses get_terms()
 * @uses esc_url()
 * @uses get_term_link()
 * @param array $atts
 * @return string: unordered list(s) on sucess - empty string on failure.
 */
 
if( !defined( 'MFIELDS_TAXONOMY_LIST_SHORTCODE_DIR' ) ) {
	exit;
}

$o.= "\n\t" . '<dl class="taxonomy-glossary">';
foreach( (array) $terms as $term ) {
	$url = esc_url( get_term_link( $term, $term->taxonomy ) );
	$count = intval( $term->count );
	$style = '';
	$style.= ( $background != 'fff' ) ? ' background:#' . $background . ';' : '';
	$style.= ( $color != '000' ) ? ' color:#' . $color . ';' : '';
	$style = ( !empty( $style ) ) ? ' style="' . trim( $style ) . '"' : '';
	
	/* Edit Link for term */
	if ( current_user_can( 'manage_categories' ) ) {
		$title = 'Edit ' . $term->name;
		$href = admin_url( 'edit-tags.php' ) . '?action=edit&amp;taxonomy=' . $term->taxonomy . '&amp;tag_ID=' . (int) $term->term_id;
		$edit = '<a class="edit-term" href="' . esc_url( $href ) . '" title="' . esc_attr( $title ). '"><img src="' . $edit_img . '" alt="edit" /></a> ';
	}
	
	/* Term Description must not be empty. */
	if( '' !== trim( $term->description ) ) {
		$o.= "\n\t\t" . '<a id="' . esc_attr( $term->slug ) . '"></a>';
		$o.= "\n\t\t" . '<dt class="taxonomy-glossary-term"' . $style . '><a' . $style . ' class="term-name" href="' . $url . '">' . esc_html( $term->name ) . '</a>' . $edit . '</dt>';
		$o.= "\n\t\t" . '<dd class="taxonomy-glossary-definition"' . $style . '>' . term_description( $term->term_id, $term->taxonomy ) . '</dd>' . "\n";
	}
}
$o.= "\n\t" . '</dl>';
?>