<?php
/**
 * Show a grid of thumbnails
 *
 * @package Mas_WC_Brands/Templates
 */

$wrapper_class = 'columns-' . $columns;
if ( wp_validate_boolean( $fluid_columns ) ) {
	$wrapper_class .= ' fluid-columns';
}
?>
<div class="brand-thumbnails <?php echo esc_attr( $wrapper_class ); ?>">
<?php
	$count     = 0;
	$style_att = '';

	$brands = array_values( $brands );

foreach ( $brands as $index => $brand ) :
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

	if ( '' === $wrapper_class ) {
		$width     = floor( ( ( 100 - ( ( $columns - 1 ) * 2 ) ) / $columns ) * 100 ) / 100;
		$style_att = ' style="width: ' . intval( $width ) . '%;"';
	}
	?>

	<div class="<?php echo esc_attr( $class ); ?>">
		<a href="<?php echo esc_url( get_term_link( $brand->slug, $taxonomy ) ); ?>" title="<?php echo esc_attr( $brand->name ); ?>">
		<?php echo mas_wcbr_get_brand_thumbnail_image( $brand, $image_size ); ?>
		</a>
	</div>

<?php endforeach; ?>
</div>
