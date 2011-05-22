<?php
/**
 * Definition List Template.
 *
 * Displays taxonomy terms + descriptions in a definition list.
 *
 * The purpose of this template is to set the value of of the $o
 * variable. This variable will be returned by mf_taxonomy_list_shortcode()
 * when the "definition-list" template has been requested. This
 * template can be overriden in a theme by copying this file into
 * the theme's folder. If a file having the same name as this is
 * present in the active theme, it will be used instead of this one.
 *
 * This file will inherit variable scope from mf_taxonomy_list_shortcode()
 * meaning that all variables defined there will also be available here.
 *
 * The supported variables that you may use include:
 *
 * $args (array) - Shortcode Arguments.
 *
 * $terms (array) - Term objects returned by get_terms();
 *
 * $nav (string) - HTML markup for paged navigation. You can simply
 * print this value at any point in the template or, if needed,
 * define custom html to be used in it's place by seting a new value.
 *
 * @since     1.1
 */

if ( ! defined( 'MFIELDS_TAXONOMY_LIST_SHORTCODE_DIR' ) ) {
	exit;
}

$o.= "\n\n\n" . '<div class="taxonomy-list-definition-list">';
$o.= "\n" . '<dl>';
foreach ( (array) $terms as $term ) {
	$o.= "\n" . '<dt  id="' . esc_attr( $term->slug ) . '" class="term-name">' . esc_html( $term->name ) . mf_taxonomy_list_shortcode_edit_term_link( $term ) . '</dt>';
	$o.= apply_filters( 'taxonomy-list-term-description', '', array(
		'term'   => $term,
		'before' => '<dd class="term-description">',
		'after'  => '</dd>',
	) );
}
$o.= "\n" . '</dl>';
$o.= "\n" . '</div>';