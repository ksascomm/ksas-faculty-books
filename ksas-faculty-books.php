<?php
/**
 * Plugin Name: KSAS Faculty Books
 * Plugin URI: http://krieger.jhu.edu/
 * Description: Creates faculty books custom post type.
 * Version: 3.1
 * Author: KSAS Communications
 * Author URI: mailto:ksaswen@jhu.edu
 * License: GPL2
 */

/**
 * Creating a function to create our CPT
 */
function faculty_books_custom_post_type() {

	// Set UI labels for this Custom Post Type.
		$labels = array(
			'name'               => _x( 'Faculty Books', 'Post Type General Name' ),
			'singular_name'      => _x( 'Faculty Book', 'Post Type Singular Name' ),
			'menu_name'          => __( 'Faculty Books' ),
			'parent_item_colon'  => __( 'Parent Faculty Book' ),
			'all_items'          => __( 'All Faculty Books' ),
			'view_item'          => __( 'View Faculty Book' ),
			'add_new_item'       => __( 'Add New Faculty Book' ),
			'add_new'            => __( 'Add New' ),
			'edit_item'          => __( 'Edit Faculty Book' ),
			'update_item'        => __( 'Update Faculty Book' ),
			'search_items'       => __( 'Search Faculty Book' ),
			'not_found'          => __( 'Not Found' ),
			'not_found_in_trash' => __( 'Not found in Trash' ),
		);

		// Set other options for this Custom Post Type.

		$args = array(
			'label'               => __( 'Faculty Books' ),
			'description'         => __( 'Published works by Faculty' ),
			'labels'              => $labels,
			// Features this CPT supports in Post Editor.
			'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions' ),
			// You can associate this CPT with a taxonomy or custom taxonomy.
			'taxonomies'          => array( 'books' ),

			/*
			A hierarchical CPT is like Pages and can have
			* Parent and child items. A non-hierarchical CPT
			* is like Posts.
			*/
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-book-alt',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'show_in_rest'        => true,

		);

		// Register this Custom Post Type.
		register_post_type( 'faculty-books', $args );
}

	/*
	Hook into the 'init' action so that the function
	* Containing this post type registration is not
	* unnecessarily executed.
	*/

	add_action( 'init', 'faculty_books_custom_post_type', 0 );


/**
 * Meta Boxes
 */
$faculty_books_metabox = array(
	'id'       => 'faculty_books',
	'title'    => 'Faculty Books Details',
	'page'     => array( 'faculty-books' ),
	'context'  => 'normal',
	'priority' => 'high',
	'fields'   => array(

		array(
			'name'  => 'Publisher',
			'desc'  => '',
			'id'    => 'ecpt_publisher',
			'class' => 'ecpt_publisher',
			'type'  => 'text',
			'std'   => '',
		),
		array(
			'name'  => 'Publication Date',
			'desc'  => 'Year Only',
			'id'    => 'ecpt_pub_date',
			'class' => 'ecpt_pub_date',
			'type'  => 'text',
			'std'   => '',
		),
		array(
			'name'  => 'Purchase (Amazon) Link',
			'desc'  => '(Do NOT include http://)',
			'id'    => 'ecpt_pub_link',
			'class' => 'ecpt_pub_link',
			'type'  => 'text',
			'std'   => '',
		),
		array(
			'name'    => 'Role',
			'desc'    => '',
			'id'      => 'ecpt_pub_role',
			'class'   => 'ecpt_pub_role',
			'type'    => 'select2',
			'options' => array( 'author', 'co-author', 'editor', 'co-editor', 'contributor', 'translator' ),
			'std'     => '',
		),
		array(
			'name'  => 'Author',
			'desc'  => '',
			'id'    => 'ecpt_pub_author',
			'class' => 'ecpt_pub_author',
			'type'  => 'select',
			'std'   => '',
		),

	),
);


add_action( 'admin_menu', 'ecpt_add_faculty_books_meta_box' );
/** Add Faculty Books Meta Box */
function ecpt_add_faculty_books_meta_box() {

	global $faculty_books_metabox;

	foreach ( $faculty_books_metabox['page'] as $page ) {
		add_meta_box( $faculty_books_metabox['id'], $faculty_books_metabox['title'], 'ecpt_show_faculty_books_box', $page, 'normal', 'default', $faculty_books_metabox );
	}
}

/** Function to show meta boxes */
function ecpt_show_faculty_books_box() {
	global $post;
	global $faculty_books_metabox;
	global $ecpt_prefix;
	global $wp_version;

	// Use nonce for verification.
	echo '<input type="hidden" name="ecpt_faculty_books_meta_box_nonce" value="', wp_create_nonce( basename( __FILE__ ) ), '" />';

	echo '<table class="form-table">';

	foreach ( $faculty_books_metabox['fields'] as $field ) {
		// get current post meta data.

		$meta = get_post_meta( $post->ID, $field['id'], true );

		echo '<tr>',
				'<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
				'<td class="ecpt_field_type_' . str_replace( ' ', '_', $field['type'] ) . '">';
		switch ( $field['type'] ) {
			case 'text':
				echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" /><br/>', '', $field['desc'];
				break;
			case 'select':
				$author_select_query = new WP_Query(
					array(
						'post_type'      => 'people',
						'tax_query'      => array(
							'relation' => 'OR',
							array(
								'taxonomy' => 'role',
								'field'    => 'slug',
								'terms'    => array( 'adjunct-faculty' ),
							),
							array(
								'taxonomy' => 'role',
								'field'    => 'slug',
								'terms'    => array( 'faculty', 'aa-faculty', 'ae-visiting' ),
							),
						),
						'meta_key'       => 'ecpt_people_alpha',
						'orderby'        => 'meta_value',
						'order'          => 'ASC',
						'posts_per_page' => '-1',
					)
				);
				$authors             = $author_select_query->get_posts();
				echo '<select name="', $field['id'], '" id="', $field['id'], '">';
				foreach ( $authors as $author ) {
					echo '<option value="' . $author->ID . '"', $meta == $author->ID ? ' selected="selected"' : '', '>', $author->post_title, '</option>';
				}
				echo '</select>';
				break;
			case 'select2':
				echo '<select name="', $field['id'], '" id="', $field['id'], '">';
				foreach ( $field['options'] as $option ) {

					echo '<option value="' . $option . '"', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
				}
				echo '</select>';
				break;
		}
		echo '<td>',
			'</tr>';
	}

	echo '</table>';
}

add_action( 'save_post', 'ecpt_faculty_books_save' );

/** Save data from meta box */
function ecpt_faculty_books_save( $post_id ) {
	global $post;
	global $faculty_books_metabox;

	// verify nonce.
	if ( ! isset( $_POST['ecpt_faculty_books_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['ecpt_faculty_books_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// check autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	// check permissions.
	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	foreach ( $faculty_books_metabox['fields'] as $field ) {

		$old = get_post_meta( $post_id, $field['id'], true );
		$new = $_POST[ $field['id'] ];

		if ( $new && $new != $old ) {
			if ( $field['type'] == 'date' ) {
				$new = ecpt_format_date( $new );
				update_post_meta( $post_id, $field['id'], $new );
			} else {
				update_post_meta( $post_id, $field['id'], $new );

			}
		} elseif ( '' == $new && $old ) {
			delete_post_meta( $post_id, $field['id'], $old );
		}
	}
}
$faculty_books_metabox2 = array(
	'id'       => 'faculty_books2',
	'title'    => 'Second Author Details',
	'page'     => array( 'faculty-books' ),
	'context'  => 'normal',
	'priority' => 'medium',
	'fields'   => array(
		array(
			'name'  => 'Is there a second author?',
			'desc'  => 'Tick checkbox if yes',
			'id'    => 'ecpt_author_cond',
			'class' => 'ecpt_author_cond',
			'type'  => 'checkbox',
			'std'   => '',
		),
		array(
			'name'    => 'Second Author Role',
			'desc'    => '',
			'id'      => 'ecpt_pub_role2',
			'class'   => 'ecpt_pub_role2',
			'type'    => 'select2',
			'options' => array( 'author', 'co-author', 'editor', 'co-editor', 'contributor', 'translator' ),
			'std'     => '',
		),
		array(
			'name'  => 'Second Author',
			'desc'  => '',
			'id'    => 'ecpt_pub_author2',
			'class' => 'ecpt_pub_author2',
			'type'  => 'select',
			'std'   => '',
		),


	),
);

add_action( 'admin_menu', 'ecpt_add_faculty_books_meta_box2' );
/** Add Second Faculty Books Meta Box */
function ecpt_add_faculty_books_meta_box2() {

	global $faculty_books_metabox2;

	foreach ( $faculty_books_metabox2['page'] as $page ) {
		add_meta_box( $faculty_books_metabox2['id'], $faculty_books_metabox2['title'], 'ecpt_show_faculty_books_box2', $page, 'normal', 'default', $faculty_books_metabox2 );
	}
}

/** Function to show meta boxes */
function ecpt_show_faculty_books_box2() {
	global $post;
	global $faculty_books_metabox2;
	global $ecpt_prefix;
	global $wp_version;

	// Use nonce for verification.
	echo '<input type="hidden" name="ecpt_faculty_books_meta_box2_nonce" value="', wp_create_nonce( basename( __FILE__ ) ), '" />';

	echo '<table class="form-table">';

	foreach ( $faculty_books_metabox2['fields'] as $field ) {
		// get current post meta data.

		$meta = get_post_meta( $post->ID, $field['id'], true );

		echo '<tr>',
				'<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
				'<td class="ecpt_field_type_' . str_replace( ' ', '_', $field['type'] ) . '">';
		switch ( $field['type'] ) {
			case 'text':
				echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" /><br/>', '', $field['desc'];
				break;
			case 'select':
				$author_select_query = new WP_Query(
					array(
						'post_type'      => 'people',
						'tax_query'      => array(
							'relation' => 'OR',
							array(
								'taxonomy' => 'role',
								'field'    => 'slug',
								'terms'    => array( 'adjunct-faculty' ),
							),
							array(
								'taxonomy' => 'role',
								'field'    => 'slug',
								'terms'    => array( 'faculty', 'aa-faculty', 'ae-visiting' ),
							),
						),
						'meta_key'       => 'ecpt_people_alpha',
						'orderby'        => 'meta_value',
						'order'          => 'ASC',
						'posts_per_page' => '-1',
					)
				);
				$authors             = $author_select_query->get_posts();
				echo '<select name="', $field['id'], '" id="', $field['id'], '">';
				echo '<option name="no-author" value="no-author" selected="selected"></option>';
				foreach ( $authors as $author ) {
					echo '<option value="' . $author->ID . '"', $meta == $author->ID ? ' selected="selected"' : '', '>', $author->post_title, '</option>';
				}
				echo '</select>';
				break;
			case 'select2':
				echo '<select name="', $field['id'], '" id="', $field['id'], '">';
				foreach ( $field['options'] as $option ) {

					echo '<option value="' . $option . '"', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
				}
				echo '</select>';
				break;
			case 'checkbox':
				echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />&nbsp;';
				echo $field['desc'];
				break;
		}
		echo '<td>',
			'</tr>';
	}

	echo '</table>';
}

add_action( 'save_post', 'ecpt_faculty_books_save2' );

/** Save data from meta box*/
function ecpt_faculty_books_save2( $post_id ) {
	global $post;
	global $faculty_books_metabox2;

	// verify nonce.
	if ( ! isset( $_POST['ecpt_faculty_books_meta_box2_nonce'] ) || ! wp_verify_nonce( $_POST['ecpt_faculty_books_meta_box2_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// check autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	// check permissions.
	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	foreach ( $faculty_books_metabox2['fields'] as $field ) {

		$old = get_post_meta( $post_id, $field['id'], true );
		$new = $_POST[ $field['id'] ];

		if ( $new && $new != $old ) {
			if ( $field['type'] == 'date' ) {
				$new = ecpt_format_date( $new );
				update_post_meta( $post_id, $field['id'], $new );
			} else {
				update_post_meta( $post_id, $field['id'], $new );

			}
		} elseif ( '' == $new && $old ) {
			delete_post_meta( $post_id, $field['id'], $old );
		}
	}
}

/*************Faculty Books Widget*****************/
	/**
	 * Register widget with WordPress.
	 */
class Faculty_Books_Widget extends WP_Widget {
	/** The first parameter passed to parent::__construct() is a string representing the id of this widget */
	public function __construct() {
		$widget_options  = array(
			'classname'   => 'ksas_books',
			'description' => __( 'Displays faculty books at random or by date', 'ksas_books' ),
		);
		$control_options = array(
			'width'   => 300,
			'height'  => 350,
			'id_base' => 'ksas_books-widget',
		);
		parent::__construct( 'ksas_books-widget', __( 'Faculty Books', 'ksas_books' ), $widget_options, $control_options );
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title']    = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['random']   = isset( $new_instance['random'] ) ? wp_strip_all_tags( $new_instance['random'] ) : '';
		$instance['quantity'] = isset( $new_instance['quantity'] ) ? wp_strip_all_tags( $new_instance['quantity'] ) : '';
		$instance['link']     = isset( $new_instance['link'] ) ? wp_strip_all_tags( $new_instance['link'] ) : '';
		if ( taxonomy_exists( 'program' ) ) {
			$instance['program'] = wp_strip_all_tags( $new_instance['program'] );
		}
		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array(
			'title'    => __( 'Faculty Books', 'ksas_books' ),
			'quantity' => __( '3', 'ksas_books' ),
			'program'  => __( '', 'ksas_books' ),
			'random'   => 'rand',
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo esc_html( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'hybrid' ); ?></label>
			<input id="<?php echo esc_html( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_html( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_html( $instance['title'] ); ?>" style="width:100%;" />
		</p>

		<!-- Order: Latest or Random -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'random' ) ); ?>"><?php esc_html_e( 'Order (Latest or Random)', 'ksas_books' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'random' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'random' ) ); ?>" class="widefat" style="width:100%;">
			<option value="date" 
			<?php
			if ( 'date' === $instance['random'] ) {
				echo 'selected="selected"';}
			?>
			>Latest Only</option>
			<option value="rand" 
			<?php
			if ( 'rand' === $instance['random'] ) {
				echo 'selected="selected"';}
			?>
			>Random</option>
			</select>
		</p>

		<!-- Number of Stories: Text Input -->
		<p>
			<label for="<?php echo esc_html( $this->get_field_id( 'quantity' ) ); ?>"><?php esc_html_e( 'Number of stories to display:', 'ksas_books' ); ?></label>
			<input id="<?php echo esc_html( $this->get_field_id( 'quantity' ) ); ?>" name="<?php echo esc_html( $this->get_field_name( 'quantity' ) ); ?>" value="<?php echo esc_html( $instance['quantity'] ); ?>" style="width:100%;" />
		</p>

		<!-- Widget Link: Archive Link -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>"><?php _e( 'Link to Faculty Books Archive:', 'hybrid' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'link' ) ); ?>" value="<?php echo $instance['link']; ?>" style="width:100%;" />
		</p>

		<!-- Widget Conditional: Program Taxonomy -->
		<?php if ( taxonomy_exists( 'program' ) ) { ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'program' ); ?>"><?php _e( 'Choose Program:', 'ksas_books' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'program' ); ?>" name="<?php echo $this->get_field_name( 'program' ); ?>" class="widefat" style="width:100%;">
			<?php
			global $wpdb;
				$categories = get_categories(
					array(
						'orderby'    => 'name',
						'order'      => 'ASC',
						'hide_empty' => 1,
						'taxonomy'   => 'program',
					)
				);
			foreach ( $categories as $category ) {
				$category_choice = $category->slug;
				$category_title  = $category->name;
				?>
			<option value="<?php echo $category_choice; ?>"
				<?php
				if ( $category_choice == $instance['category_choice'] ) {
					echo 'selected="selected"';}
				?>
				><?php echo $category_title; ?></option>
			<?php } ?>
			</select>
		</p>
			<?php
		}
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		/* Our variables from the widget settings. */
		$title        = apply_filters( 'widget_title', $instance['title'] );
		$random       = isset( $instance['random'] ) ? $instance['random'] : '';
		$quantity     = $instance['quantity'];
		$archive_link = isset( $instance['link'] ) ? $instance['link'] : '';
		if ( taxonomy_exists( 'program' ) ) {
			$program = $instance['program'];
		}
		echo $args['before_widget'];

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		if ( taxonomy_exists( 'program' ) ) {
			$books_widget_query = new WP_Query(
				array(
					'post_type'      => 'faculty-books',
					'program'        => $program,
					'posts_per_page' => $quantity,
					'orderby'        => $random,
				)
			);
		} else {
			$books_widget_query = new WP_Query(
				array(
					'post_type'      => 'faculty-books',
					'posts_per_page' => $quantity,
					'orderby'        => $random,
				)
			);
		}
		if ( $books_widget_query->have_posts() ) :
			?>
			<?php if ( ! empty( $archive_link ) ) : ?>
			<div class="view-more-link news-section flex flex-row-reverse">
				<a class="button" href="<?php echo ( esc_url( $archive_link ) ); ?>">View more <?php echo esc_html( $title ); ?>&nbsp;<span class="fa fa-chevron-circle-right" aria-hidden="true"></span></a>
			</div>
			<?php endif; ?>
		<div class="book-listings">
			<?php
			while ( $books_widget_query->have_posts() ) :
				$books_widget_query->the_post();
				global $post;
				?>
				<article aria-labelledby="book-<?php the_ID(); ?>">
				<?php
				$faculty_post_id  = get_post_meta( $post->ID, 'ecpt_pub_author', true );
				$faculty_post_id2 = get_post_meta( $post->ID, 'ecpt_pub_author2', true );
				?>
					<?php
					if ( has_post_thumbnail() ) {
						the_post_thumbnail( 'full', array( 'alt' => esc_html( get_the_title() ) ) );  }
					?>
					<h3>
						<a href="<?php the_permalink(); ?>" id="book-<?php the_ID(); ?>"><?php the_title(); ?><span class="link"></span></a>
					</h3>
					<p>
					<strong><?php echo esc_html( get_the_title( $faculty_post_id ) ); ?>,&nbsp;<?php echo esc_html( get_post_meta( $post->ID, 'ecpt_pub_role', true ) ); ?>
						<?php
						if ( get_post_meta( $post->ID, 'ecpt_author_cond', true ) == 'on' ) {
							?>
							<br>
							<?php echo esc_html( get_the_title( $faculty_post_id2 ) ); ?> ,&nbsp;
									<?php
										echo esc_html( get_post_meta( $post->ID, 'ecpt_pub_role2', true ) );
						}
						?>
					</strong></p>
				</article>
				<?php
		endwhile;
			?>
		</div>
			<?php
		endif;
		echo $args['after_widget'];
	}
}

/** Register Widget */
function ksas_load_faculty_books_widget() {
	register_widget( 'Faculty_Books_Widget' );
}

add_action( 'widgets_init', 'ksas_load_faculty_books_widget' );

?>
