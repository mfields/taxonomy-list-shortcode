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
 * Shortcode.
 *
 * Recognized Arguments:
 *
 * cols - (int) Number of columns to group the terms into. This option
 * is only use in the "index" and "gallery templates". Accepted values
 * are 1 - 5. Optional. Defaults to 3.
 *
 * image_size - (string) Value of any registered image size. This argument
 * will only be used by the "gallery" template. Optional.
 *
 * per_page - (int) Number of terms to show per page. Setting this value
 * will enable the list of terms to be displayed in separate pages. Paging
 * is only available when this shortcode is used in posts having the "page"
 * post_type. If paging is desired the page slug should not be "index".
 * Optional.
 *
 * tax - (string) Name of the taxonomy as registered with WordPress. More
 * than one taxonomy can be passed by separating the names with commas.
 *
 * template - (string) Specify a template to use to display the terms.
 * 3 different options are available: "index", "definition-list" and
 * "gallery". Please see each individual template for instructions on
 * their intended use. Optional. Defaults to "index".
 *
 * @param     array          Attributes for the shortcode.
 * @return    string         unordered list(s) on sucess - empty string on failure.
 *
 * @access    private
 * @since     0.1
 */
function mf_taxonomy_list_shortcode( $args = array() ) {
	static $instance = 0;
	$instance++;
	$o = '';
	$term_args = array(
		'hide_empty' => true,
		'pad_counts' => true,
		);
	$defaults = array(

		/* Global arguments. */
		'cols'        => 3,
		'image_size'  => 'thumbnail',
		'per_page'    => false,
		'show_counts' => 1,
		'tax'         => 'post_tag',
		'template'    => 'index',

		/* Index specific arguments. */
		'background'  => 'ffffff',
		'color'       => '000000',

		/* Gallery specific arguments. */
		'captiontag'  => 'dd',
		'itemtag'     => 'dl',
		'icontag'     => 'dt',
		);

	$args = shortcode_atts( $defaults, $args );

	if ( 0 !== strpos( $args['tax'], ',' ) ) {
		$args['tax'] = explode( ',', $args['tax'] );
	}

	$args['per_page'] = absint( $args['per_page'] );

	/* Only 1 - 5 columns are supported. */
	if ( absint( $args['cols'] ) > 5 ) {
		$cols = 1;
	}

	/* Sanitize colors. */
	$args['color'] = mf_taxonomy_list_sanitize_hex( $args['color'], $defaults['color'] );
	$args['background'] = mf_taxonomy_list_sanitize_hex( $args['background'], $defaults['background'] );

	/*
	 * Pass a custom value of 'taxonomy_list_has_description' get_terms().
	 * This value will be used to flag definition-list requests.
	 * When a definition-list is requested, it is important to only
	 * display terms that have descriptions. Please see
	 * mf_taxonomy_list_shortcode_terms_clauses()
	 * defined in this file.
	 */
	if ( 'definition-list' == $args['template'] ) {
		$term_args['taxonomy_list_has_description'] = true;
	}

	/*
	 * Calculate the number of the current paged view.
	 * Define the value of offset.
	 */
	$offset = false;
	if ( is_page() && ! empty( $args['per_page'] ) ) {
		$current = 0;
		if ( is_front_page() ) {
			$current = (int) get_query_var( 'page' );
		}
		else {
			$current = (int) get_query_var( 'paged' );
		}
		if ( empty( $current ) ) {
			$current = 1;
		}
		$offset = $args['per_page'] * ( $current - 1 );
	}

	/*
	 * Query for terms.
	 */
	if ( 'gallery' == $args['template'] ) {
		$terms = apply_filters( 'taxonomy-images-get-terms', '', array() );
	}
	else {
		$terms = get_terms( $args['tax'], $term_args );
	}
	if ( is_wp_error( $terms ) ) {
		return '';
	}
	$total = count( $terms );

	/* Paged navigation. */
	$nav = '';
	if ( false !== $offset ) {

		/* Select Terms to display on this paged view. */
		$terms = array_slice ( $terms, $offset, $args['per_page'] );
		$count = count( $terms );

		/* HTML for paged navigation */
		$prev = null;
		if ( 0 < $offset ) {
			$prev = '<div class="alignleft"><a href="' . esc_url( mfields_paged_taxonomy_link( $current - 1 ) ) . '">' . esc_html( apply_filters( 'mf_taxonomy_list_shortcode_link_prev', 'Previous' ) ) .' </a></div>';
		}
		$next = null;
		if ( $offset + $count < $total ) {
			$next = '<div class="alignright"><a href="' . esc_url( mfields_paged_taxonomy_link( $current + 1 ) ) . '">' . esc_html( apply_filters( 'mf_taxonomy_list_shortcode_link_next', 'Next' ) ) . '</a></div>';
		}
		if ( $prev || $next ) {
			$nav = '<div class="navigation">' . $prev . $next . '</div><div class="clear"></div>';
		}
	}

	/* Include template. */
	if ( in_array( $args['template'], array( 'index', 'definition-list', 'gallery' ) ) ) {
		$template_name = 'taxonomy-list-shortcode-' . $args['template'] . '.php';
		$template = locate_template( $template_name );
		if ( ! empty( $template ) ) {
			include $template;
		}
		else {
			include MFIELDS_TAXONOMY_LIST_SHORTCODE_DIR . $template_name;
		}
	}

	/* Print output. */
	return $o;
}
add_shortcode( 'taxonomy-list', 'mf_taxonomy_list_shortcode' );


/**
 * Reset Cache Key
 *
 * In case where get_terms() has been called before this shortcode
 * is called with the same arguments AND the requested template
 * is the definition list, we need to force the cache key to have
 * a unique value so that the query will be executed. Requesting
 * the definition list template triggers a custom clause to be
 * defined in mf_taxonomy_list_shortcode_terms_clauses().
 *
 * @access    private
 * @since     1.1
 */
function taxonomy_list_shortcode_reset_cache_key( $args ) {
	if ( isset( $args['taxonomy_list_has_description'] ) ) {
		wp_cache_set( 'last_changed', time() . '-description-only', 'terms' );
	}
	else {
		wp_cache_set( 'last_changed', time(), 'terms' );
	}

	return $args;
}
add_filter( 'get_terms_args', 'taxonomy_list_shortcode_reset_cache_key' );


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
 * Only query for terms with descriptions when definition-list
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
	if ( isset( $pieces['where'] ) && isset( $args['taxonomy_list_has_description'] ) ) {
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
 * Term Desciption.
 *
 * Gets the description for a given term for use in templates.
 * This function will append an html link to the description
 * before it is passed through text filters.
 *
 * Recognized arguments:
 *
 * term - (stdClass) Wordpress term object. Required.
 *
 * before - (string) Text to prepend to the term description. Optional.
 * Defaults to an empty string.
 *
 * after - (string) Text to prepend to the term description. Optional.
 * Defaults to an empty string.
 *
 * link_text - (string) Text to use inside the link that will appended
 * to the term description. Optional. Defaults to "View entries".
 *
 * @param     mixed     Default value. Not used.
 * @param     array     Named arguments. Please see above for detailed description.
 * @return    string    Term description ready for use in templates with archive link appended.
 *
 * @access    private
 * @since     1.1
 */
function mf_taxonomy_list_term_description( $default, $args = array() ) {
	$args = wp_parse_args( $args, array(
		'term'      => false,
		'before'    => '',
		'after'     => '',
		'link_text' => __( 'View entries', 'taxonomy-list' )
		) );

	if ( ! isset( $args['term']->taxonomy ) || ! isset( $args['term']->description ) ) {
		return '1234';
	}

	if ( empty( $args['term']->description ) ) {
		return '5678';
	}

	$args['term']->description .= ' <a class="term-archive-link" href="' . esc_url( get_term_link( $args['term'], $args['term']->taxonomy ) ) . '">' . esc_html( $args['link_text'] ) . '</a>';

	return $args['before'] . sanitize_term_field( 'description', $args['term']->description, $args['term']->term_id, $args['term']->taxonomy, 'display' ) . $args['after'];
}
add_filter( 'taxonomy-list-term-description', 'mf_taxonomy_list_term_description', 10, 2 );


/**
 * Is a given string a color formatted in hexidecimal notation?
 *
 * @param     string    Color in hexidecimal notation. "#" may or may not be prepended to the string.
 * @return    bool
 *
 * @access    private
 * @since     1.1
 */
function mf_taxonomy_list_validate_hex( $hex ) {
	$hex = trim( (string) $hex );
	if ( 0 === strpos( $hex, '#' ) ) {
		$hex = substr( $hex, 1 );
	}
	else if ( 0 === strpos( $hex, '%23' ) ) {
		$hex = substr( $hex, 3 );
	}
	if ( 0 === preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
		return false;
	}
	return true;
}


/**
 * Sanitize a color represented in hexidecimal notation.
 *
 * @param     string    Unknown value to sanitize.
 * @param     string    The value that this function should return if it cannot be recognized as a color.
 * @return    string    $hex if valid, $default if not.
 *
 * @access    private
 * @since     1.1
 */
function mf_taxonomy_list_sanitize_hex( $hex, $default = '' ) {
	if ( mf_taxonomy_list_validate_hex( $hex ) ) {
		return $hex;
	}
	return $default;
}