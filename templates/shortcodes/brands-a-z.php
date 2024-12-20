<?php
/**
 * Brand A-Z listing
 *
 * @usedby [mas_product_brand_list]
 * @package Mas_WC_Brands/Templates
 */

?>
<div id="brands_a_z">

	<ul class="brands_index">
		<?php
		foreach ( $index as $i ) {
			if ( isset( $product_brands[ $i ] ) ) {
				echo '<li class="active"><a href="#brands-' .$i . '">' . esc_html( $i ) . '</a></li>';
			} elseif ( $show_empty ) {
				echo '<li><span>' . esc_html( $i ) . '</span></li>';
			}
		}
		?>
	</ul>

	<?php
	foreach ( $index as $i ) {
		if ( isset( $product_brands[ $i ] ) ) :
			?>

			<h3 id="brands-<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></h3>

			<ul class="brands">
				<?php
				foreach ( $product_brands[ $i ] as $brand ) {
					echo '<li><a href="' . esc_url( get_term_link( $brand->slug, $taxonomy ) ) . '">' . esc_html( $brand->name ) . '</a></li>';
				}
				?>
			</ul>

			<?php if ( $show_top_links ) : ?>
				<a class="top" href="#brands_a_z"><?php esc_html_e( '&uarr; Top', 'mas-wc-brands' ); ?></a>
			<?php endif; ?>

			<?php
		endif;
	};
	?>

</div>
