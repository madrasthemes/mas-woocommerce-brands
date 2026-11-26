<?php
/**
 * Show a grid of thumbnails
 *
 * @package Mas_WC_Brands/Templates
 */

$columns       = max( 1, absint( $columns ) );
$wrapper_class = 'columns-' . $columns;
if ( wp_validate_boolean( $fluid_columns ) ) {
	$wrapper_class .= ' fluid-columns';
}
?>
<div class="brand-thumbnails <?php echo esc_attr( $wrapper_class ); ?>">
<?php
	$count  = 0;
	$brands = array_values( $brands );

foreach ( $brands as $index => $brand ) :
	$term_link = get_term_link( $brand->slug, $taxonomy );
	if ( is_wp_error( $term_link ) ) {
		continue;
	}

	$count++;
	$class = 'thumbnail';

	if ( 0 === $count % 2 ) {
		$class .= ' even';
	} else {
		$class .= ' odd';
	}

	if ( 0 === $index || 0 === $index % $columns ) {
		$class .= ' first';
	} elseif ( 0 === ( $index + 1 ) % $columns ) {
		$class .= ' last';
	}
	?>

	<div class="<?php echo esc_attr( $class ); ?>">
		<a href="<?php echo esc_url( $term_link ); ?>" title="<?php echo esc_attr( $brand->name ); ?>">
		<?php echo wp_kses_post( mas_wcbr_get_brand_thumbnail_image( $brand, $image_size ) ); ?>
		</a>
	</div>

<?php endforeach; ?>
</div>
