<?php
/**
 * Gallery Template.
 *
 * Requires the Taxonomy Images plugin.
 *
 * @package      Taxonomy List Shortcode
 * @author       Michael Fields <michael@mfields.org>
 * @copyright    Copyright (c) 2011, Michael Fields
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since        1.1
 */

if ( ! defined( 'MFIELDS_TAXONOMY_LIST_SHORTCODE_DIR' ) ) {
	exit;
}

print "\n\n\n" . '<div class="gallery term-gallery term-gallery-columns-' . $args['cols'] . ' gallery-columns-' . $args['cols'] . ' gallery-size-' . sanitize_html_class( $args['image_size'] ) . '">';

$count = 0;
foreach ( $terms as $term ) {
	$count++;

	if ( in_array( $args['image_size'], array( 'thumbnail', 'medium', 'large' ) ) ) {
		$w = absint( get_option( $args['image_size'] . '_size_w' ) );
		$h = absint( get_option( $args['image_size'] . '_size_h' ) );
	}

	$image = wp_get_attachment_image( $term->image_id, $args['image_size'], false, array(
		'class' => esc_attr( 'taxonomy-image ' . $args['image_size'] ),
		'alt'   => esc_attr( $term->name ),
		'title' => ''
		) );

	$link = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . $image . '</a>';

	print "\n\n\t" . '<' . $args['itemtag'] . ' class="gallery-item">';
	print "\n\t" . '<' . $args['icontag'] . ' class="gallery-icon">' . $link . '</' . $args['icontag'] . '>';
	print "\n\t" . '<' . $args['captiontag'] . ' class="wp-caption-text gallery-caption">' . esc_html( $term->name ) . '</' . $args['captiontag'] . '>';
	print "\n\t" . '</' . $args['itemtag'] . '>';

	if ( 0 == $count % $args['cols'] ) {
		print "\n\n\t" . '<br style="clear:both;">';
	}
}
print "\n" . '<br style="clear:both;">';
print "\n" . '</div>';














