<?php
/**
 * Faculty Books Widget Class
 *
 * @package KSAS_Faculty_Books
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget to display Faculty Books.
 */
class Faculty_Books_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'ksas_books-widget',
			__( 'Faculty Books', 'ksas_books' ),
			array(
				'classname'   => 'ksas_books',
				'description' => __( 'Displays faculty books at random or by date', 'ksas_books' ),
			)
		);
	}

	/**
	 * Frontend display of widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values.
	 */
	public function widget( $args, $instance ) {
		$title        = apply_filters( 'widget_title', $instance['title'] );
		$random       = ! empty( $instance['random'] ) ? $instance['random'] : 'date';
		$quantity     = ! empty( $instance['quantity'] ) ? absint( $instance['quantity'] ) : 3;
		$archive_link = ! empty( $instance['link'] ) ? $instance['link'] : '';
		$program      = ! empty( $instance['program'] ) ? $instance['program'] : '';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_widget'];

		if ( $title ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		$query_args = array(
			'post_type'      => 'faculty-books',
			'posts_per_page' => $quantity,
			'orderby'        => $random,
			'no_found_rows'  => true, // Performance optimization for simple widgets.
		);

		if ( taxonomy_exists( 'program' ) && ! empty( $program ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy'         => 'program',
					'field'            => 'slug',
					'terms'            => $program,
					'include_children' => false,
				),
			);
		}

		$books_query = new WP_Query( $query_args );

		if ( $books_query->have_posts() ) : ?>
			<?php if ( $archive_link ) : ?>
				<div class="view-more-link news-section flex flex-row-reverse">
					<a class="button" href="<?php echo esc_url( $archive_link ); ?>">
						View more <?php echo esc_html( $title ); ?>&nbsp;<span class="fa fa-chevron-circle-right" aria-hidden="true"></span>
					</a>
				</div>
			<?php endif; ?>
			<div class="book-listings">
				<?php
				while ( $books_query->have_posts() ) :
					$books_query->the_post();
					$book_id = get_the_ID();
					// Primary Author Data.
					$author_id = get_post_meta( $book_id, 'ecpt_pub_author', true );
					$role      = get_post_meta( $book_id, 'ecpt_pub_role', true );

					// Second Author Data - Use wp_validate_boolean for legacy 'on' vs ACF '1'.
					$has_second_author = wp_validate_boolean( get_post_meta( $book_id, 'ecpt_author_cond', true ) );
					$author_id2        = get_post_meta( $book_id, 'ecpt_pub_author2', true );
					$role2             = get_post_meta( $book_id, 'ecpt_pub_role2', true );
					?>
					<article aria-labelledby="book-<?php the_ID(); ?>">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'large', array( 'alt' => get_the_title() ) ); ?>
						<?php endif; ?>
						<h3>
							<a href="<?php the_permalink(); ?>" id="book-<?php the_ID(); ?>"><?php the_title(); ?></a>
						</h3>
						<p>
							<strong>
							<?php if ( $author_id ) : ?>
								<?php echo esc_html( get_the_title( $author_id ) ); ?>,&nbsp;<?php echo esc_html( $role ); ?>
							<?php endif; ?>

							<?php if ( $has_second_author && $author_id2 ) : ?>
								<br><?php echo esc_html( get_the_title( $author_id2 ) ); ?>,&nbsp;<?php echo esc_html( $role2 ); ?>
							<?php endif; ?>
							</strong>
						</p>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<?php
		endif;
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values.
	 */
	public function form( $instance ) {
		$title    = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Faculty Books', 'ksas_books' );
		$quantity = ! empty( $instance['quantity'] ) ? $instance['quantity'] : 3;
		$random   = ! empty( $instance['random'] ) ? $instance['random'] : 'date';
		$link     = ! empty( $instance['link'] ) ? $instance['link'] : '';
		$program  = ! empty( $instance['program'] ) ? $instance['program'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'ksas_books' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'random' ) ); ?>"><?php esc_html_e( 'Order:', 'ksas_books' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'random' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'random' ) ); ?>" class="widefat">
				<option value="date" <?php selected( $random, 'date' ); ?>>Latest Only</option>
				<option value="rand" <?php selected( $random, 'rand' ); ?>>Random</option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'quantity' ) ); ?>"><?php esc_html_e( 'Number of books:', 'ksas_books' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'quantity' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'quantity' ) ); ?>" type="number" value="<?php echo esc_attr( $quantity ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>"><?php esc_html_e( 'Archive Link:', 'ksas_books' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'link' ) ); ?>" type="text" value="<?php echo esc_url( $link ); ?>">
		</p>
		<?php if ( taxonomy_exists( 'program' ) ) : ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'program' ) ); ?>"><?php esc_html_e( 'Choose Program:', 'ksas_books' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'program' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'program' ) ); ?>" class="widefat">
				<option value=""><?php esc_html_e( '-- All Programs --', 'ksas_books' ); ?></option>
				<?php
				$categories = get_terms(
					array(
						'taxonomy'   => 'program',
						'hide_empty' => true,
					)
				);
				foreach ( $categories as $category ) {
					echo '<option value="' . esc_attr( $category->slug ) . '" ' . selected( $program, $category->slug, false ) . '>' . esc_html( $category->name ) . '</option>';
				}
				?>
			</select>
		</p>
			<?php
		endif;
	}

	/**
	 * Update widget settings.
	 *
	 * @param array $new_instance New settings.
	 * @param array $old_instance Old settings.
	 * @return array Updated settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance             = $old_instance;
		$instance['title']    = sanitize_text_field( $new_instance['title'] );
		$instance['quantity'] = absint( $new_instance['quantity'] );
		$instance['random']   = sanitize_text_field( $new_instance['random'] );
		$instance['link']     = esc_url_raw( $new_instance['link'] );
		$instance['program']  = sanitize_text_field( $new_instance['program'] );
		return $instance;
	}
}