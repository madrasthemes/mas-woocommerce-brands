<?php
/**
 * Class to setup brand attribute
 *
 * @package Mas_WC_Brands
 */
if ( ! class_exists( 'Mas_WC_Brands_Admin_Taxonomies' ) ) {
	class Mas_WC_Brands_Admin_Taxonomies {

		public function __construct() {

			$brand_taxonomy = Mas_WC_Brands()->get_brand_taxonomy();

			if( ! empty( $brand_taxonomy ) ) {
				
				// Add form
				add_action( "{$brand_taxonomy}_add_form_fields",	array( $this, 'add_brand_fields' ), 10 );
				add_action( "{$brand_taxonomy}_edit_form_fields",	array( $this, 'edit_brand_fields' ), 10, 2 );
				add_action( 'create_term',							array( $this, 'save_brand_fields' ), 	10, 3 );
				add_action( 'edit_term',							array( $this, 'save_brand_fields' ), 	10, 3 );

				// Add columns
				add_filter( "manage_edit-{$brand_taxonomy}_columns",	array( $this, 'product_brand_columns' ) );
				add_filter( "manage_{$brand_taxonomy}_custom_column",	array( $this, 'product_brand_column' ), 10, 3 );
			}
		}

		/**
		 * Brand thumbnail fields.
		 *
		 * @return void
		 */
		public function add_brand_fields() {
			wp_enqueue_script( 'mas-wc-brands-admin' );
			wp_nonce_field( 'mas_wc_brands_save_term_meta', 'mas_wc_brands_term_meta_nonce' );
			?>
			<div class="form-field">
				<label><?php esc_html_e( 'Thumbnail', 'mas-wc-brands' ); ?></label>
				<div id="product_brand_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo esc_url( wc_placeholder_img_src() ); ?>" width="60px" height="60px" alt="" /></div>
				<div style="line-height:60px;">
					<input type="hidden" id="product_brand_thumbnail_id" name="product_brand_thumbnail_id" />
					<button type="button" class="mas_wc_br_upload_image_button button"><?php esc_html_e( 'Upload/Add image', 'mas-wc-brands' ); ?></button>
					<button type="button" class="mas_wc_br_remove_image_button button"><?php esc_html_e( 'Remove image', 'mas-wc-brands' ); ?></button>
				</div>
				<div class="clear"></div>
			</div>
			<?php
		}

		/**
		 * Edit brand thumbnail field.
		 *
		 * @param mixed $term     Term (brand) being edited.
		 * @param mixed $taxonomy Taxonomy of the term being edited.
		 */
		public function edit_brand_fields( $term, $taxonomy ) {
			wp_enqueue_script( 'mas-wc-brands-admin' );
			wp_nonce_field( 'mas_wc_brands_save_term_meta', 'mas_wc_brands_term_meta_nonce' );

			$thumbnail_id = absint( get_term_meta( $term->term_id, 'thumbnail_id', true ) );
			$image        = $thumbnail_id ? wp_get_attachment_thumb_url( $thumbnail_id ) : false;
			if ( ! $image ) {
				$image = wc_placeholder_img_src();
			}

			?>
			<tr class="form-field">
				<th scope="row" valign="top"><label><?php esc_html_e( 'Thumbnail', 'mas-wc-brands' ); ?></label></th>
				<td>
					<div id="product_brand_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo esc_url( $image ); ?>" alt="" style="max-width: 150px; height: auto;" /></div>
					<div style="line-height:60px;">
						<input type="hidden" id="product_brand_thumbnail_id" name="product_brand_thumbnail_id" value="<?php echo esc_attr( $thumbnail_id ); ?>" />
						<button type="button" class="mas_wc_br_upload_image_button button"><?php esc_html_e( 'Upload/Add image', 'mas-wc-brands' ); ?></button>
						<button type="button" class="mas_wc_br_remove_image_button button"><?php esc_html_e( 'Remove image', 'mas-wc-brands' ); ?></button>
					</div>
					<div class="clear"></div>
				</td>
			</tr>
			<?php
		}

		/**
		 * Save brand thumbnail field.
		 *
		 * @param int    $term_id  Term ID being saved.
		 * @param int    $tt_id    Term taxonomy ID.
		 * @param string $taxonomy Taxonomy of the term being saved.
		 * @return void
		 */
		public function save_brand_fields( $term_id, $tt_id, $taxonomy ) {
			if ( $taxonomy !== Mas_WC_Brands()->get_brand_taxonomy() ) {
				return;
			}

			if ( ! current_user_can( 'edit_term', $term_id ) ) {
				return;
			}

			if ( isset( $_POST['product_brand_thumbnail_id'] ) ) {
				if (
					! isset( $_POST['mas_wc_brands_term_meta_nonce'] ) ||
					! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mas_wc_brands_term_meta_nonce'] ) ), 'mas_wc_brands_save_term_meta' )
				) {
					return;
				}

				update_term_meta( $term_id, 'thumbnail_id', absint( wp_unslash( $_POST['product_brand_thumbnail_id'] ) ) );
			}

			delete_transient( 'wc_term_counts' );
		}

		/**
		 * Thumbnail column added to brand admin.
		 *
		 * @param mixed $columns
		 * @return array
		 */
		public function product_brand_columns( $columns ) {
			$new_columns          = array();
			$new_columns['cb']    = isset( $columns['cb'] ) ? $columns['cb'] : '';
			$new_columns['thumb'] = esc_html__( 'Image', 'mas-wc-brands' );

			unset( $columns['cb'] );

			unset( $columns['description'] );

			return array_merge( $new_columns, $columns );
		}

		/**
		 * Thumbnail column value added to brand admin.
		 *
		 * @param string $columns Column output.
		 * @param string $column  Column name.
		 * @param int    $id      Term ID.
		 * @return string
		 */
		public function product_brand_column( $columns, $column, $id ) {
			if ( 'thumb' === $column ) {
				$thumbnail_id = absint( get_term_meta( $id, 'thumbnail_id', true ) );
				$image        = $thumbnail_id ? wp_get_attachment_thumb_url( $thumbnail_id ) : false;

				if ( ! $image ) {
					$image = wc_placeholder_img_src();
				}

				// Prevent esc_url from breaking spaces in urls for image embeds.
				// Ref: http://core.trac.wordpress.org/ticket/23605
				$image = str_replace( ' ', '%20', (string) $image );

				$columns .= '<img src="' . esc_url( $image ) . '" alt="' . esc_attr__( 'Thumbnail', 'mas-wc-brands' ) . '" class="wp-post-image" height="48" width="48" />';
			}

			return $columns;
		}
	}
}

new Mas_WC_Brands_Admin_Taxonomies();