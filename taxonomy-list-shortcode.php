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

/**
 * The directory in which this plugin is installed.
 */
define( 'MFIELDS_TAXONOMY_LIST_SHORTCODE_DIR', dirname( __FILE__ ) . '/' );

/**
 * Activate.
 *
 * Called when user activates this plugin.
 * Adds a custom setting to the options table.
 *
 * @return    void
 */
function mf_taxonomy_list_activate() {
	add_option( 'mfields_taxonomy_list_shortcode_enable_css', 1 );
}
register_activation_hook( __FILE__, 'mf_taxonomy_list_activate' );

/**
 * Deactivate.
 *
 * Called when user deactivates this plugin.
 * Deletes custom settings from the options table.
 *
 * @return    void
 */
function mf_taxonomy_list_deactivate() {
	delete_option( 'mfields_taxonomy_list_shortcode_enable_css' );
}
register_deactivation_hook( __FILE__, 'mf_taxonomy_list_deactivate' );

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
 * This filter is intended to fire during the 'terms_clauses' hook
 * in the WordPress core function get_terms().
 *
 * @param     stdClass       Term Object.
 * @return    string         HTML anchor element.
 *
 * @access    private
 * @since     1.0
 */
function mf_taxonomy_list_shortcode_edit_term_link( $term ) {
	if ( ! isset( $term->name ) || ! isset( $term->taxonomy ) || ! isset( $term->taxonomy ) ) {
		return '';
	}
	if ( current_user_can( 'manage_categories' ) ) {
		$edit_img = WP_PLUGIN_URL . '/' . basename( plugin_dir_path( __FILE__ ) ) . '/edit.png';
		$href = admin_url( 'edit-tags.php' ) . '?action=edit&amp;taxonomy=' . urlencode( $term->taxonomy ) . '&amp;tag_ID=' . (int) $term->term_id;
		return '<a class="edit-term" href="' . esc_url( $href ) . '" title="' . sprintf( esc_attr__( 'Edit %1$s', 'taxonomy_list_shortcode' ), $term->name ) . '"><img src="' . esc_url( $edit_img ) . '" alt="' . esc_attr__( 'edit', 'taxonomy_list_shortcode' ) . '" /></a> ';
	}
	return '';
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
	$o = ''; /* "Output" */
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

	/* We need to pass $template to get_terms as well. */
	if ( isset( $template ) && $template == 'glossary' ) {
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
	if ( is_array( $terms ) && count( $terms ) > 0 ) {
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

/**
 * Custom Styles
 *
 * Print html style tag with pre-defined styles.
 *
 * @return     void
 *
 * @access     private
 * @since      unknown
 */
function mf_taxonomy_list_css() {
	if ( 0 < (int) get_option( 'mfields_taxonomy_list_shortcode_enable_css' ) ) {
		$o = <<<EOF
	<style type="text/css">
	html>body .entry ul.mf_taxonomy_column { /* Reset for the Default Theme. */
		margin: 0px;
		padding: 0px;
		list-style-type: none;
		padding-left: 0px;
		text-indent: 0px;
	}
	ul.mf_taxonomy_column,
	.entry ul.mf_taxonomy_column {
		float: left;
		margin: 0;
		padding: 0 0 1em;
		list-style-type: none;
		list-style-position: outside;
	}
	.mf_cols_1{ width:99%; }
	.mf_cols_2{ width:49.5%; }
	.mf_cols_3{ width:33%; }
	.mf_cols_4{ width:24.75%; }
	.mf_cols_5{ width:19.77%; }
	.entry ul.mf_taxonomy_column li:before {
		content: "";
	}
	.mf_taxonomy_column li,
	.entry ul.mf_taxonomy_column li {
		list-style: none, outside;
		position: relative;
		height: 1.5em;
		z-index: 0;
		background: #fff;
		margin: 0 1em .4em 0;
	}
	.mf_taxonomy_column li.has-quantity,
	.entry ul.mf_taxonomy_column li.has-quantity {
		border-bottom: 1px dotted #888;
	}
	.mf_taxonomy_column a.edit-term {
		height: 16px;
		width: 16px;
		display: block;
	}
	.logged-in .mf_taxonomy_column a.term-name {
		left: 16px;
		padding-left: 4px;
	}
	.mf_taxonomy_column a.edit-term,
	.mf_taxonomy_column a.term-name,
	.mf_taxonomy_column .quantity {
		position:absolute;
		bottom: -0.2em;
		line-height: 1em;
		background: #fff;
		z-index:10;
	}
	.mf_taxonomy_column a.term-name {
		display: block;
		left:0;
		padding-right: 0.3em;
		text-decoration: none;
	}
	.mf_taxonomy_column .quantity {
		display: block;
		right:0;
		padding-left: 0.3em;
	}
	.mf_taxonomy_list .clear {
		clear:both;
	}
	</style>
EOF;
	print '<!-- mf-taxonomy-list -->' . "\n" . preg_replace( '/\s+/', ' ', $o );
	}
}
add_action( 'wp_head', 'mf_taxonomy_list_css' );

include_once( 'taxonomy-administration-panel.php' );

function mfields_taxonomy_list_shortcode_admin_section() {

	/* Process the Form */
	if ( isset( $_POST['mfields_taxonomy_list_shortcode_submit'] ) ) {
		$css = ( isset( $_POST['mfields_taxonomy_list_shortcode_enable_css'] ) ) ? 1 : 0;
		$css_human = ( $css ) ? 'true' : 'false';
		$updated = update_option( 'mfields_taxonomy_list_shortcode_enable_css', $css );
	}

	$checked = checked( '1', get_option( 'mfields_taxonomy_list_shortcode_enable_css' ), false );

	print <<<EOF
		<div class="mfields-taxonomy-plugin">
		<h3>Taxonomy List Shortcode</h3>
		<form action="" method="post">
			<p><label for="mfields_taxonomy_list_shortcode_enable_css"><input name="mfields_taxonomy_list_shortcode_enable_css" type="checkbox" id="mfields_taxonomy_list_shortcode_enable_css" value="1"{$checked} /> Enable CSS</label></p>
			<input class="button" type="submit" name="mfields_taxonomy_list_shortcode_submit" value="Update Settings">
		</form>
		</div>
EOF;
}
add_action( 'mfields_taxonomy_administration_panel', 'mfields_taxonomy_list_shortcode_admin_section' );