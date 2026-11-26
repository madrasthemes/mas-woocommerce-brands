<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Mas_WC_Brands_Shortcodes class
 *
 * @class       Mas_WC_Brands_Shortcodes
 * @version     1.0.0
 * @category    Class
 * @author      Madras Themes
 */
if ( ! class_exists( 'Mas_WC_Brands_Shortcodes' ) ) {
	class Mas_WC_Brands_Shortcodes {

		protected static $brand_taxonomy = '';

		/**
		 * Init shortcodes.
		 */
		public static function init() {
			$brand_taxonomy = Mas_WC_Brands()->get_brand_taxonomy();
			self::$brand_taxonomy = $brand_taxonomy;
			
			if( ! empty( $brand_taxonomy ) ) {
				$shortcodes = array(
					'mas_product_brand'                    => __CLASS__ . '::output_product_brand',
					'mas_product_brand_list'               => __CLASS__ . '::output_product_brand_list',
					'mas_product_brand_thumbnails'         => __CLASS__ . '::output_product_brand_thumbnails',
					'mas_brand_products'                   => __CLASS__ . '::output_brand_products',
				);

				foreach ( $shortcodes as $shortcode => $function ) {
					add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
				}
			}
		}

		/**
		 * Output product brand shortcode.
		 *
		 * @access public
		 * @param array $atts Shortcode attributes.
		 * @return string
		 */
		public static function output_product_brand( $atts ) {
			global $post;

			$brand_taxonomy = self::$brand_taxonomy;

			$atts = shortcode_atts(
				array(
					'width'   => '',
					'height'  => '',
					'class'   => 'aligncenter',
					'post_id' => '',
				),
				$atts,
				'mas_product_brand'
			);

			$post_id = absint( $atts['post_id'] );
			if ( ! $post_id ) {
				if ( ! $post instanceof WP_Post ) {
					return '';
				}
				$post_id = $post->ID;
			}

			$brands = wp_get_post_terms( $post_id, $brand_taxonomy, array( 'fields' => 'ids' ) );

			if ( is_wp_error( $brands ) || empty( $brands ) ) {
				return '';
			}

			$width  = $atts['width'];
			$height = $atts['height'];

			if ( $width || $height ) {
				$width  = $width ? $width : 'auto';
				$height = $height ? $height : 'auto';
			}

			ob_start();

			foreach ( $brands as $brand ) {
				$thumbnail = mas_wcbr_get_brand_thumbnail_url( $brand );

				if ( $thumbnail ) {
					$term = get_term_by( 'id', $brand, $brand_taxonomy );
					if ( ! $term || is_wp_error( $term ) ) {
						continue;
					}

					wc_get_template(
						'shortcodes/single-brand.php',
						array(
							'taxonomy'  => $brand_taxonomy,
							'term'      => $term,
							'width'     => $width,
							'height'    => $height,
							'thumbnail' => $thumbnail,
							'class'     => $atts['class'],
						),
						'mas-woocommerce-brands',
						untrailingslashit( MAS_WCBR_ABSPATH ) . '/templates/'
					);
				}
			}

			return ob_get_clean();
		}

		/**
		 * Output product brand A-Z list shortcode.
		 *
		 * @access public
		 * @param array $atts Shortcode attributes.
		 * @return string
		 */
		public static function output_product_brand_list( $atts ) {
			$brand_taxonomy = self::$brand_taxonomy;

			$atts = shortcode_atts(
				array(
					'show_top_links'    => true,
					'show_empty'        => true,
					'show_empty_brands' => false,
				),
				$atts,
				'mas_product_brand_list'
			);

			$show_top_links    = wp_validate_boolean( $atts['show_top_links'] );
			$show_empty        = wp_validate_boolean( $atts['show_empty'] );
			$show_empty_brands = wp_validate_boolean( $atts['show_empty_brands'] );

			$product_brands = array();
			$terms          = get_terms(
				array(
					'taxonomy'   => $brand_taxonomy,
					'hide_empty' => ! $show_empty_brands,
				)
			);

			if ( ! $terms || is_wp_error( $terms ) || ! is_array( $terms ) ) {
				return '';
			}

			foreach ( $terms as $term ) {
				$term_letter = strtolower( substr( (string) $term->slug, 0, 1 ) );

				if ( '' !== $term_letter && ctype_alpha( $term_letter ) ) {
					$product_brands[ $term_letter ][] = $term;
				} else {
					$product_brands['0-9'][] = $term;
				}
			}

			ob_start();

			wc_get_template(
				'shortcodes/brands-a-z.php',
				array(
					'taxonomy'       => $brand_taxonomy,
					'terms'          => $terms,
					'index'          => array_merge( range( 'a', 'z' ), array( '0-9' ) ),
					'product_brands' => $product_brands,
					'show_empty'     => $show_empty,
					'show_top_links' => $show_top_links,
				),
				'mas-woocommerce-brands',
				untrailingslashit( MAS_WCBR_ABSPATH ) . '/templates/'
			);

			return ob_get_clean();
		}

		/**
		 * Output product brand thumbnails shortcode.
		 *
		 * @access public
		 * @param array $atts Shortcode attributes.
		 * @return string
		 */
		public static function output_product_brand_thumbnails( $atts ) {
			$brand_taxonomy = self::$brand_taxonomy;

			$atts = shortcode_atts(
				array(
					'columns'       => 4,
					'hide_empty'    => 0,
					'orderby'       => 'name',
					'order'         => '',
					'slug'          => '',
					'include'       => '',
					'exclude'       => '',
					'number'        => '',
					'image_size'    => '',
					'fluid_columns' => false,
				),
				$atts,
				'mas_product_brand_thumbnails'
			);

			$columns = absint( $atts['columns'] );
			if ( $columns < 1 ) {
				$columns = 4;
			}

			$exclude = ! empty( $atts['exclude'] ) ? array_filter( array_map( 'intval', explode( ',', $atts['exclude'] ) ) ) : array();
			$order   = $atts['order'];

			if ( empty( $order ) ) {
				$order = 'name' === $atts['orderby'] ? 'asc' : 'desc';
			}

			$taxonomy_args = array(
				'taxonomy'   => $brand_taxonomy,
				'hide_empty' => wp_validate_boolean( $atts['hide_empty'] ),
				'orderby'    => $atts['orderby'],
				'slug'       => $atts['slug'],
				'include'    => $atts['include'],
				'exclude'    => $exclude,
				'number'     => $atts['number'],
				'order'      => $order,
			);

			$brands = get_terms( $taxonomy_args );

			if ( ! $brands || is_wp_error( $brands ) || ! is_array( $brands ) ) {
				return '';
			}

			ob_start();

			wc_get_template(
				'shortcodes/brand-thumbnails.php',
				array(
					'taxonomy'      => $brand_taxonomy,
					'brands'        => $brands,
					'columns'       => $columns,
					'image_size'    => $atts['image_size'],
					'fluid_columns' => $atts['fluid_columns'],
				),
				'mas-woocommerce-brands',
				untrailingslashit( MAS_WCBR_ABSPATH ) . '/templates/'
			);

			return ob_get_clean();
		}

		/**
		 * Output brand products shortcode.
		 *
		 * @access public
		 * @param array $atts Shortcode attributes.
		 * @return string
		 */
		public static function output_brand_products( $atts ) {
			$brand_taxonomy = self::$brand_taxonomy;

			$atts = shortcode_atts(
				array(
					'per_page' => '12',
					'columns'  => '4',
					'orderby'  => 'title',
					'order'    => 'desc',
					'brand'    => '',
					'operator' => 'IN',
				),
				$atts,
				'mas_brand_products'
			);

			if ( ! $atts['brand'] ) {
				return '';
			}

			$operator = strtoupper( $atts['operator'] );
			if ( ! in_array( $operator, array( 'IN', 'NOT IN', 'AND' ), true ) ) {
				$operator = 'IN';
			}

			// Default ordering args.
			$ordering_args = WC()->query->get_catalog_ordering_args( $atts['orderby'], $atts['order'] );
			$meta_query    = WC()->query->get_meta_query();
			$query_args    = array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'orderby'             => $ordering_args['orderby'],
				'order'               => $ordering_args['order'],
				'posts_per_page'      => intval( $atts['per_page'] ),
				'meta_query'          => $meta_query,
				'tax_query'           => array(
					array(
						'taxonomy' => $brand_taxonomy,
						'terms'    => array_map( 'sanitize_title', explode( ',', $atts['brand'] ) ),
						'field'    => 'slug',
						'operator' => $operator,
					),
				),
			);

			if ( isset( $ordering_args['meta_key'] ) ) {
				$query_args['meta_key'] = $ordering_args['meta_key'];
			}

			$return = self::product_loop( $query_args, $atts, $brand_taxonomy );

			// Remove ordering query arguments.
			WC()->query->remove_ordering_args();

			return $return;
		}

		/**
		 * Loop over found products.
		 *
		 * @access public
		 * @param  array  $query_args Query arguments.
		 * @param  array  $atts       Shortcode attributes.
		 * @param  string $loop_name  Loop name.
		 * @return string
		 */
		public static function product_loop( $query_args, $atts, $loop_name ) {
			global $woocommerce_loop;

			$columns                     = max( 1, absint( $atts['columns'] ) );
			$woocommerce_loop['columns'] = $columns;
			$woocommerce_loop['name']    = $loop_name;
			$query_args                  = apply_filters( 'woocommerce_shortcode_products_query', $query_args, $atts, $loop_name );
			$products                    = new WP_Query( $query_args );

			ob_start();

			if ( $products->have_posts() ) {
				// Prime caches before grabbing objects.
				update_post_caches( $products->posts, array( 'product', 'product_variation' ) );
				?>

				<?php do_action( "woocommerce_shortcode_before_{$loop_name}_loop", $atts ); ?>

				<?php woocommerce_product_loop_start(); ?>

					<?php while ( $products->have_posts() ) : ?>
						<?php $products->the_post(); ?>

						<?php wc_get_template_part( 'content', 'product' ); ?>

					<?php endwhile; // end of the loop. ?>

				<?php woocommerce_product_loop_end(); ?>

				<?php do_action( "woocommerce_shortcode_after_{$loop_name}_loop", $atts ); ?>

				<?php
			} else {
				do_action( "woocommerce_shortcode_{$loop_name}_loop_no_results", $atts );
			}

			woocommerce_reset_loop();
			wp_reset_postdata();

			return '<div class="woocommerce columns-' . esc_attr( $columns ) . '">' . ob_get_clean() . '</div>';
		}
	}
}