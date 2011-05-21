<?php
/**
 * Gallery Template.
 *
 * Requires the Taxonomy Images plugin.
 *
 * This template is closely modeled after the Wordpress core
 * Gallery Shortcode. It should inherit styles directly from
 * the theme. Unlike the gallery shortcode, this function will
 * not include style blocks directly in the html body. A few
 * styles have been added to this plugin's style.css file to
 * aid in it's display.
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

$o .= "\n\n\n" . '<div class="gallery term-gallery term-gallery-columns-' . $args['cols'] . ' gallery-columns-' . $args['cols'] . ' gallery-size-' . sanitize_html_class( $args['image_size'] ) . '">';

$count = 0;
foreach ( $terms as $term ) {
	$count++;

	$image = wp_get_attachment_image( $term->image_id, $args['image_size'], false, array(
		'class' => esc_attr( 'taxonomy-image ' . $args['image_size'] ),
		'alt'   => esc_attr( $term->name ),
		'title' => ''
		) );

	$link = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . $image . '</a>';

	$o .= "\n\n\t" . '<' . $args['itemtag'] . ' class="gallery-item">';
	$o .= "\n\t" . '<' . $args['icontag'] . ' class="gallery-icon">' . $link . '</' . $args['icontag'] . '>';
	$o .= "\n\t" . '<' . $args['captiontag'] . ' class="wp-caption-text gallery-caption">' . esc_html( $term->name ) . '</' . $args['captiontag'] . '>';
	$o .= "\n\t" . '</' . $args['itemtag'] . '>';

	if ( 0 == $count % $args['cols'] ) {
		$o .= "\n\n\t" . '<br style="clear:both;">';
	}
}
$o .= "\n" . '<br style="clear:both;">';
$o .= "\n" . '</div>';














