<?php
/**
 * Taxonomy Administration Panel
 * 
 * Creates an Administration Panel for taxonomy plugins under "Settings" .
 * @author Michael Fields <michael@mfields.org>
 * @version 0.1
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package mfields_taxonomy
 */

if( !function_exists( 'mfields_taxonomy_panel_hook_submenu' ) ) {
	add_action( 'admin_menu', 'mfields_taxonomy_panel_hook_submenu' );
	function mfields_taxonomy_panel_hook_submenu() {
		global $mfields_taxonomy_panel_hook;
		$mfields_taxonomy_panel_hook = add_options_page( 'Taxonomy', 'Taxonomy', 'level_10', 'mfields_taxonomy_panel', 'mfields_taxonomy_panel' );
	}
}

if( !function_exists( 'mfields_taxonomy_panel' ) ) {
	function mfields_taxonomy_panel() {
		global $wpdb;
		
		/* Output page to browser. */
		print "\n" . '<div class="wrap">';
		print "\n" . '<h2>Taxonomy Settings</h2>';
		do_action( 'mfields_taxonomy_administration_panel' );
		print "\n" . '</div>';
	}
}	
?>