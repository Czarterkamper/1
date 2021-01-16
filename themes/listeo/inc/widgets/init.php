<?php
/**
 * Load widgets

 * @since 4.0.3
 */

// File Security Check
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Widgets list */
$listeo_widgets = array(
	'inc/widgets/list.php',
	'inc/widgets/header.php',

);
$listeo_widgets = apply_filters( 'listeo_widgets', $listeo_widgets );
foreach ( $listeo_widgets as $listeo_widget ) {
	include_once locate_template( $listeo_widget );
}