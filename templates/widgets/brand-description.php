<?php
/**
 * Show a brands description when on a taxonomy page
 *
 * @package Mas_WC_Brands/Templates
 */

if ( $thumbnail ) {
	echo wp_kses_post( mas_wcbr_get_brand_thumbnail_image( $brand ) );
}

echo wp_kses_post( wpautop( wptexturize( term_description() ) ) );
