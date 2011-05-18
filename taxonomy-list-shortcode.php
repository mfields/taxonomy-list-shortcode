<?php
/*
Plugin Name:       Taxonomy List Shortcode
Plugin URI:        http://wordpress.mfields.org/plugins/taxonomy-list-shortcode/
Description:       Defines a shortcode which prints an unordered list for taxonomies.
Version:           1.1-BETA
Author:            Michael Fields
Author URI:        http://wordpress.mfields.org/

Copyright 2009-2011  Michael Fields  michael@mfields.org

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as published by
the Free Software Foundation.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

edit.png is a modified version of gtk-edit.png from the Gnome icons set
*/


define( 'MFIELDS_TAXONOMY_LIST_SHORTCODE_VERSION', '1.2-dev' );
define( 'MFIELDS_TAXONOMY_LIST_SHORTCODE_URL',     plugin_dir_url( __FILE__ ) );
define( 'MFIELDS_TAXONOMY_LIST_SHORTCODE_DIR',     dirname( __FILE__ ) . '/' );


/**
 * Custom Styles
 *
 * Adds custom stylesheet to public views.
 * Themes can suppress styles by defining a constant named
 * MFIELDS_TAXONOMY_LIST_SHORTCODE_NO_STYLES in functions.php.
 *
 * @access     private
 * @since      unknown
 * @alter      2011-05-18
 */
function mf_taxonomy_list_css() {
	if ( defined( 'MFIELDS_TAXONOMY_LIST_SHORTCODE_NO_STYLES' ) ) {
		return;
	}
	wp_enqueue_style( 'taxonomy-list-shortcode', MFIELDS_TAXONOMY_LIST_SHORTCODE_URL . '/style.css', array(), MFIELDS_TAXONOMY_LIST_SHORTCODE_VERSION, 'screen' );
}
add_action( 'wp_print_styles', 'mf_taxonomy_list_css' );


/**
 * Get terms having descriptions.
 *
 * Only query for terms with descriptions when glossary
 * template is used.
 *
 * This filter is intended to fire during the 'terms_clauses' hook
 * in the WordPress core function get_terms().
 *
 * @param     array          SQL bits used to create a full term query.
 * @param     array          List of taxonomies to query for.
 * @param     array          Arguments passed to get_terms().
 * @return    array          SQL bits used to create a full term query.
 *
 * @access    private
 * @since     1.0
 */
function mf_taxonomy_list_shortcode_terms_clauses( $pieces, $taxonomies, $args ) {
	if (
		isset( $pieces['where'] ) &&
		isset( $args['taxonomy_list_shortcode_template'] ) &&
		'glossary' == $args['taxonomy_list_shortcode_template']
	) {
		$pieces['where'] .= " AND tt.description != ''";
	}
	return $pieces;
}
add_filter( 'terms_clauses', 'mf_taxonomy_list_shortcode_terms_clauses', 10, 3 );


/**
 * Edit Term Link.
 *
 * Print a link to edit a given term.
 *
 * @param     stdClass       Term Object.
 * @return    string         HTML anchor element.
 *
 * @access    private
 * @since     1.0
 */
function mf_taxonomy_list_shortcode_edit_term_link( $term ) {
	if ( ! isset( $term->taxonomy ) ) {
		return '';
	}

	$taxonomy = get_taxonomy( $term->taxonomy );

	$cap = '';
	if ( isset( $taxonomy->cap->edit_terms ) ) {
		$cap = $taxonomy->cap->edit_terms;
	}

	if ( ! current_user_can( $cap ) ) {
		return '';
	}

	if ( ! isset( $term->term_id ) ) {
		return '';
	}

	return '<a class="edit-term" href="' . esc_url( add_query_arg( array( 'action'   => 'edit', 'taxonomy' => $term->taxonomy, 'tag_ID'   => $term->term_id ), admin_url( 'edit-tags.php' ) ) ) . '"><img src="' . esc_url( MFIELDS_TAXONOMY_LIST_SHORTCODE_URL . '/edit.png' ) . '" alt="' . esc_attr__( 'Edit', 'taxonomy_list_shortcode' ) . '" /></a> ';
}


/**
 * Shortcode.
 *
 * @param     array          Attributes for the shortcode.
 * @return    string         unordered list(s) on sucess - empty string on failure.
 *
 * @access    private
 * @since     0.1
 */
function mf_taxonomy_list_shortcode( $atts = array() ) {
	$o = '';
	$nav = '';
	$term_args = array(
		'pad_counts' => true,
		'hide_empty' => true,
		);
	$defaults = array(
		'tax'         => 'post_tag',
		'cols'        => 3,
		'background'  => 'fff',
		'color'       => '000',
		'show_counts' => 1,
		'per_page'    => false,
		'template'    => 'index',
		);

	$atts = shortcode_atts( $defaults, $atts );
	extract( $atts );

	/* Only 1 - 5 columns are supported. */
	if ( ! in_array( (int) $cols, array( 1, 2, 3, 4, 5 ) ) ) {
		$cols = 1;
	}

	/*
	 * Pass the value of $template to get_terms().
	 * This value will be used to flag glossary requests.
	 * When a glossary is requested, it is important to only
	 * display terms that have descriptions. Please see
	 * mf_taxonomy_list_shortcode_terms_clauses()
	 * defined in this file.
	 */
	if ( 'glossary' == $args['template'] ) {
		$term_args['taxonomy_list_shortcode_template'] = $template;
	}

	/*
	 * Paging arguments for get_terms(). For the "page" post_type only.
	 */
	$per_page = absint( $per_page );
	if ( $per_page && is_page() ) {
		$term_args['number'] = $per_page;

		$current_page = 0;
		if ( is_front_page() ) {
			$current_page = (int) get_query_var( 'page' );
		}
		else {
			$current_page = (int) get_query_var( 'paged' );
		}
		if ( empty( $current_page ) ) {
			$current_page = 1;
		}

		$offset = $per_page * ( $current_page - 1 );

		$term_args['offset'] = $offset;

		/* Need to get count for all terms of this taxonomy. */
		$term_count_args = $term_args;
		unset( $term_count_args['number'] );
		unset( $term_count_args['offset'] );
		$total_terms = wp_count_terms( $tax, $term_count_args );

		/* HTML for paged navigation */
		if ( 0 === $offset ) {
			$prev = null;
		}
		else {
			$href = mfields_paged_taxonomy_link( $current_page - 1 );
			$prev = '<div class="alignleft"><a href="' . $href . '">' . apply_filters( 'mf_taxonomy_list_shortcode_link_prev', 'Previous' ) .' </a></div>';
		}
		if ( ( $offset + $per_page ) >= $total_terms ) {
			$next = null;
		}
		else {
			$href = mfields_paged_taxonomy_link( $current_page + 1 );
			$next = '<div class="alignright"><a href="' . $href . '">' . apply_filters( 'mf_taxonomy_list_shortcode_link_next', 'Next' ) . '</a></div>';
		}
		if ( $prev || $next ) {
			$nav = <<<EOF
			<div class="navigation">
				$prev
				$next
			</div>
			<div class="clear"></div>
EOF;
		}
	}

	/* The user-defined taxonomy does not exist - return an empty string. */
	if ( ! taxonomy_exists( $tax ) ) {
		return $o;
	}

	/* Get the terms for the given taxonomy. */
	$terms = get_terms( $tax, $term_args );

	/* Include template. */
	if ( is_array( $terms ) && ! empty( $terms ) ) {
		switch( $template ) {
			case 'glossary' :
				include MFIELDS_TAXONOMY_LIST_SHORTCODE_DIR . 't-glossary.php';
				break;
			case 'index' :
			default:
				include MFIELDS_TAXONOMY_LIST_SHORTCODE_DIR . 't-index.php';
				break;
		}
	}
	$o.= $nav;
	$o = "\n\t" . '<!-- START mf-taxonomy-list-plugin -->' . $o . "\n\t" . '<!-- END mf-taxonomy-list-plugin -->' . "\n" ;
	return $o;
}
add_shortcode( 'taxonomy-list', 'mf_taxonomy_list_shortcode' );


/**
 * Paged taxonomy link.
 *
 * Return a url to a paged post object.
 *
 * This function is based on the private core
 * function _wp_link_page() defined around line
 * 681 of wp-includes/post-template.php
 *
 * @param      int            Page number.
 * @return     string
 *
 * @access     private
 * @since      1.0
 */
function mfields_paged_taxonomy_link( $n ) {
	if ( 1 == $n ) {
		$url = get_permalink();
	}
	else {
		/* No permalinks - append 'page' or 'paged' variable to the query string. */
		if ( '' == get_option( 'permalink_structure' ) || in_array( get_post_status(), array( 'draft', 'pending' ) ) ) {
			if ( is_front_page() ) {
				$url = add_query_arg( 'page', $n, get_permalink() );
			}
			else {
				$url = add_query_arg( 'paged', $n, get_permalink() );
			}
		}
		/* Permalinks are enabled - build url from rewrite config. */
		else {
			global $wp_rewrite;
			$url = trailingslashit( get_permalink() ) . user_trailingslashit( $wp_rewrite->pagination_base . '/' . $n, 'single_paged' );
		}
	}
	return $url;
}