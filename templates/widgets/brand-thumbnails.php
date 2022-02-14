<?php
/**
 * Show a grid of thumbnails
 *
 * @package Mas_WC_Brands/Templates
 */

wc_get_template(
	'shortcodes/brand-thumbnails.php',
	array(
		'taxonomy'      => $taxonomy,
		'brands'        => $brands,
		'columns'       => $columns,
		'fluid_columns' => $fluid_columns,
		'image_size'    => $image_size,
	),
	'mas-woocommerce-brands',
	untrailingslashit( MAS_WCBR_ABSPATH ) . '/templates/'
);
