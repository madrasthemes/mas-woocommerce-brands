<?php
/**
 * Brand Description Widget
 *
 * When viewing a brand archive, show the current brands description + image
 *
 * @version  1.0.5
 * @package  Mas_WC_Brands/Widgets
 */

if ( ! class_exists( 'Mas_WC_Widget_Brand_Description' ) ) {
	/**
	 * Brand Description widget.
	 */
	class Mas_WC_Widget_Brand_Description extends WP_Widget {

		/**
		 * CSS class.
		 *
		 * @var string
		 */
		public $woo_widget_cssclass;

		/**
		 * Widget description.
		 *
		 * @var string
		 */
		public $woo_widget_description;

		/**
		 * Widget ID.
		 *
		 * @var string
		 */
		public $woo_widget_idbase;

		/**
		 * Widget name.
		 *
		 * @var string
		 */
		public $woo_widget_name;

		/**
		 * Constructor.
		 */
		public function __construct() {

			/* Widget variable settings. */
			$this->woo_widget_name        = esc_html__( 'MAS WC Brand Description', 'mas-wc-brands' );
			$this->woo_widget_description = esc_html__( 'When viewing a brand archive, show the current brands description.', 'mas-wc-brands' );
			$this->woo_widget_idbase      = 'mas_wc_brands_brand_description';
			$this->woo_widget_cssclass    = 'widget_brand_description';

			/* Widget settings. */
			$widget_ops = array(
				'classname'   => $this->woo_widget_cssclass,
				'description' => $this->woo_widget_description,
			);

			/* Create the widget. */
			parent::__construct( $this->woo_widget_idbase, $this->woo_widget_name, $widget_ops );
		}

		/**
		 * Output widget.
		 *
		 * @param array $args     Arguments.
		 * @param array $instance Widget instance.
		 *
		 * @see WP_Widget
		 */
		public function widget( $args, $instance ) {
			$brand_taxonomy = Mas_WC_Brands()->get_brand_taxonomy();

			if ( empty( $brand_taxonomy ) ) {
				return;
			}

			extract( $args );

			if ( ! is_tax( $brand_taxonomy ) ) {
				return;
			}

			if ( ! get_query_var( 'term' ) ) {
				return;
			}

			$thumbnail = '';
			$term      = get_term_by( 'slug', get_query_var( 'term' ), $brand_taxonomy );

			$thumbnail = mas_wcbr_get_brand_thumbnail_url( $term->term_id, 'large' );

			echo $before_widget . $before_title . $term->name . $after_title;

			wc_get_template(
				'widgets/brand-description.php',
				array(
					'thumbnail' => $thumbnail,
					'brand'     => $term,
				),
				'mas-woocommerce-brands',
				untrailingslashit( MAS_WCBR_ABSPATH ) . '/templates/'
			);

			echo $after_widget;
		}

		/**
		 * Updates a particular instance of a widget.
		 *
		 * @see    WP_Widget->update
		 * @param  array $new_instance New instance.
		 * @param  array $old_instance Old instance.
		 * @return array
		 */
		public function update( $new_instance, $old_instance ) {
			$instance['title'] = wp_strip_all_tags( stripslashes( $new_instance['title'] ) );
			return $instance;
		}

		/**
		 * Outputs the settings update form.
		 *
		 * @see   WP_Widget->form
		 *
		 * @param array $instance Instance.
		 */
		public function form( $instance ) {
			?>
				<p>
					<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'mas-wc-brands' ); ?></label>
					<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="
																	  <?php
																		if ( isset( $instance['title'] ) ) {
																			echo esc_attr( $instance['title'] );}
																		?>
					" />
				</p>
			<?php
		}

	}
}
