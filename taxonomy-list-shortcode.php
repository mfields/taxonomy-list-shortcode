<?php
/*
Plugin Name: Taxonomy List Shortcode
Plugin URI: http://wordpress.mfields.org/plugins/taxonomy-list-shortcode/
Description: Defines a shortcode which prints an unordered list for taxonomies.
Version: 1.0
Author: Michael Fields
Author URI: http://wordpress.mfields.org/
Copyright 2009-2010  Michael Fields  michael@mfields.org

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

include_once( 'taxonomy-administration-panel.php' );

/**
 * The directory in which Taxonomy List Shortcode is installed.
 */
define( 'MFIELDS_TAXONOMY_LIST_SHORTCODE_DIR', dirname( __FILE__ ) . '/' );

/* 2.9 Branch support */
if( !function_exists( 'taxonomy_exists' ) ) {
	function taxonomy_exists( $taxonomy ) {
		global $wp_taxonomies;
		return isset( $wp_taxonomies[$taxonomy] );
	}
}

if( !function_exists( 'mfields_taxonomy_list_shortcode_admin_section' ) ) {
	add_action( 'mfields_taxonomy_administration_panel', 'mfields_taxonomy_list_shortcode_admin_section' );
	function mfields_taxonomy_list_shortcode_admin_section() {
		$alert = '';
		
		/* Process the Form */
		if( isset( $_POST['mfields_taxonomy_list_shortcode_submit'] ) ) {
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
}
if( !function_exists( 'mf_taxonomy_format_text' ) ) {
	function mf_taxonomy_format_text( $text ) {
		$text = wptexturize( $text );
		$text = convert_chars( $text );
		$text = wpautop( $text );
		$text = shortcode_unautop( $text );
		$text = do_shortcode( $text );
		return $text;
	}
}
if( !function_exists( 'mf_taxonomy_list_activate' ) ) {
	/**
	 * Called when user activates this plugin.
	 * Adds a custom setting to the options table.
	 * @uses add_option
	 * @return void
	 */
	function mf_taxonomy_list_activate() {
		add_option( 'mfields_taxonomy_list_shortcode_enable_css', 1 );
	}
}
if( !function_exists( 'mf_taxonomy_list_deactivate' ) ) {
	/**
	 * Called when user deactivates this plugin.
	 * Deletes custom settings from the options table.
	 * @uses delete_option
	 * @return void
	 */
	function mf_taxonomy_list_deactivate() {
		delete_option( 'mfields_taxonomy_list_shortcode_enable_css' );
	}
}
if( !function_exists( 'mfields_sanitize_bool' ) ) {
	/**
	 * Always return a Boolean value.
	 * @param $bool bool
	 * @return bool
	 */
	function mfields_sanitize_bool( $bool ) {
		return ( $bool == 1 ) ? 1 : 0;
	}
}
if( !function_exists( 'mf_taxonomy_list_shortcode' ) ) {
	/**
	 * Process the Shortcode.
	 * @uses MFIELDS_TAXONOMY_LIST_SHORTCODE_DIR
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
	function mf_taxonomy_list_shortcode( $atts = array() ) {
		global $mfields_taxonomy_shortcode_templates;
		$o = ''; /* "Output" */
		$nav = '';
		$edit = '';
		$edit_img = WP_PLUGIN_URL . '/' . basename( plugin_dir_path(__FILE__) ) . '/edit.png';
		$term_args = array();
		$defaults = array(
			'tax' => 'post_tag',
			'cols' => 3,
			'background' => 'fff',
			'color' => '000',
			'show_counts' => 1,
			'per_page' => false,
			'show_all' => false,
			'template' => 'index',
			);
		$atts = shortcode_atts( $defaults, $atts );
		
		extract( $atts );
		$cols = mf_taxonomy_list_sanitize_cols( $cols );
		
		/* Pad Counts should always be true for hierarchical taxonomies. */
		$term_args['pad_counts'] = true;
		
		if( $show_all ) {
			$term_args['get'] = 'all';
		}
		
		/* Paging arguments for get_terms(). */
		$per_page = absint( $per_page );
		if( $per_page ) {
			$term_args['number'] = $per_page;
			
			$offset = 0;
			
			if( is_paged() )
				$offset = $per_page;
			
			$current_page = (int) get_query_var( 'paged' );
			
			if( !$current_page )
				$current_page = 1;
			
			$offset = $per_page * ( $current_page - 1 );
			
			$term_args['offset'] = $offset;
			
			/* Need to get count for all terms of this taxonomy. */
			$term_count_args = $term_args;
			unset( $term_count_args['number'] );
			unset( $term_count_args['offset'] );
			$total_terms = wp_count_terms( $tax, $term_count_args );
			
			/* HTML for paged navigation */
			if( $offset === 0 ) {
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
			if( $prev || $next ) {
				$nav = <<<EOF
				<div class="navigation">
					$prev
					$next
				</div>
EOF;
			}
		}
		
		/* More than one taxonomy has been specified in the tax parameter. */
		$sep = ',';
		if( false !== strstr( $tax, $sep ) ) {
			$tax = explode( $sep, $tax );
		}
		
		/* Get the terms for the given taxonomy. */
		$terms = get_terms( $tax, $term_args );
		
		
		if( is_array( $terms ) && count( $terms ) > 0 ) {
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
}
if( !function_exists( 'mfields_paged_taxonomy_link' ) ) {
	function mfields_paged_taxonomy_link( $pagenum ) {
		global $wp_rewrite;
			
		$request = remove_query_arg( 'paged' );
		$home_root = parse_url( get_home_url() );
		$home_root = ( isset( $home_root['path'] ) ) ? $home_root['path'] : '';
		$home_root = preg_quote( trailingslashit( $home_root ), '|' );
		$request = preg_replace( '|^'. $home_root . '|', '', $request );
		$request = preg_replace( '|^/+|', '', $request );
		
		if ( !$wp_rewrite->using_permalinks() ) {
			$base = trailingslashit( get_bloginfo( 'url' ) );
			if ( $pagenum > 1 ) {
				$request = add_query_arg( 'paged', $pagenum, $base . $request );
			} else {
				$request = $base . $request;
			}
		}
		else {
			$qs_regex = '|\?.*?$|';
			preg_match( $qs_regex, $request, $qs_match );

			if ( !empty( $qs_match[0] ) ) {
				$query_string = $qs_match[0];
				$request = preg_replace( $qs_regex, '', $request );
			} else {
				$query_string = '';
			}

			$request = preg_replace( '|page/\d+/?$|', '', $request);
			$request = preg_replace( '|^index\.php|', '', $request);
			$request = ltrim($request, '/');

			$base = trailingslashit( get_bloginfo( 'url' ) );

			if ( $wp_rewrite->using_index_permalinks() && ( $pagenum > 1 || '' != $request ) )
				$base .= 'index.php/';

			if ( $pagenum > 1 ) {
				$request = ( ( !empty( $request ) ) ? trailingslashit( $request ) : $request ) . user_trailingslashit( 'page/' . $pagenum, 'paged' );
			}

			$request = $base . $request . $query_string;
		}
		
		return $request;
	}
}
if( !function_exists( 'mf_taxonomy_list_css' ) ) {
	/**
	 * Print html style tag with pre-defined styles.
	 * @uses mfields_sanitize_bool
	 * @uses get_option
	 * @return void
	 */
	function mf_taxonomy_list_css() {
		$print_css = mfields_sanitize_bool( get_option( 'mfields_taxonomy_list_shortcode_enable_css' ) );
		if( $print_css === 1 ) {
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
}
if( !function_exists( 'mf_taxonomy_list_sanitize_cols' ) ) {
	/**
	 * Returns an integer between 1 and 5.
	 * @param $n int.
	 * @returns int.
	 */
	function mf_taxonomy_list_sanitize_cols( $n = 1 ) {
		$min = 1;
		$max = 5;
		$n = intval( $n );
		if( $n === $min || $n === $max )
			return $n;
		if( $n > $min && $n < $max )
			return $n;
		return $min;
	}
}
if( !function_exists( 'pr' ) ) {
	/**
	 * Recursively print stuff wrapped in a pre tag.
	 * @param $var mixed - just about anything ;)
	 * @return void
	 */
	function pr( $var ) {
		print '<pre>' . print_r( $var, true ) . '</pre>';
	}
}

if( !function_exists( 'get_home_url' ) ) {
	function get_home_url( $blog_id = null, $path = '', $scheme = null ) {
		$orig_scheme = $scheme;

		if ( !in_array( $scheme, array( 'http', 'https' ) ) )
			$scheme = is_ssl() && !is_admin() ? 'https' : 'http';

		if ( empty( $blog_id ) || !is_multisite() )
			$home = get_option( 'home' );
		else
			$home = get_blog_option( $blog_id, 'home' );

		$url = str_replace( 'http://', "$scheme://", $home );

		if ( !empty( $path ) && is_string( $path ) && strpos( $path, '..' ) === false )
			$url .= '/' . ltrim( $path, '/' );

		return apply_filters( 'home_url', $url, $path, $orig_scheme, $blog_id );
	}
}

/* Hook into WordPress */
add_shortcode( 'taxonomy-list', 'mf_taxonomy_list_shortcode' );
add_action( 'wp_head', 'mf_taxonomy_list_css' );
register_activation_hook( __FILE__, 'mf_taxonomy_list_activate' );
register_deactivation_hook( __FILE__, 'mf_taxonomy_list_deactivate' );
?>