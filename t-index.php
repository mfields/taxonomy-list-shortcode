<?php

/* Split the array into smaller pieces + generate html to display lists. */
$chunked = array_chunk( $terms, ceil( count( $terms ) / $cols ) );

$o.= "\n\t" . '<div class="mf_taxonomy_list">';
foreach( $chunked as $k => $column ) {
	$o.= "\n\t" . '<ul class="mf_taxonomy_column mf_cols_' . $cols . '">';
	foreach( $column as $term ) {
		$url = esc_url( get_term_link( $term, $term->taxonomy ) );
		$count = intval( $term->count );
		$style = '';
		$style.= ( $background != 'fff' ) ? ' background:#' . $background . ';' : '';
		$style.= ( $color != '000' ) ? ' color:#' . $color . ';' : '';
		$style = ( !empty( $style ) ) ? ' style="' . trim( $style ) . '"' : '';
		
		$li_class = ( $show_counts ) ? ' class="has-quantity"' : '';
		$quantity = ( $show_counts ) ? ' <span' . $style . ' class="quantity">' . $count . '</span>' : '';
		
		if ( current_user_can( 'manage_categories' ) ) {
			$title = 'Edit ' . $term->name;
			$href = admin_url( 'edit-tags.php' ) . '?action=edit&amp;taxonomy=' . $term->taxonomy . '&amp;tag_ID=' . (int) $term->term_id;
			$edit = '<a class="edit-term" href="' . esc_url( $href ) . '" title="' . esc_attr( $title ). '"><img src="' . $edit_img . '" alt="edit" /></a> ';
		}
		$o.= "\n\t\t" . '<li' . $li_class . $style . '><a' . $style . ' class="term-name" href="' . $url . '">' . $term->name . '</a>' . $edit . '' . $quantity . '</li>';
	}
	$o.=  "\n\t" . '</ul>';
}
$o.=  "\n\t" . '<div class="clear"></div>';
$o.=  "\n\t" . '</div>';
?>