<?php
/*
Plugin Name: KSAS Faculty Books Metabox for Posts
Plugin URI: http://krieger.jhu.edu/communications/web/plugins/faculty-books
Description: Creates the metabox for faculty books details.
Version: 1.1
Author: Cara Peckens
Author URI: mailto:cpeckens@jhu.edu
License: GPL2
*/

$faculty_books_metabox = array( 
	'id' => 'faculty_books',
	'title' => 'Faculty Books Details',
	'page' => array('post'),
	'context' => 'normal',
	'priority' => 'high',
	'fields' => array(

				array(
					'name' 			=> 'Publisher',
					'desc' 			=> '',
					'id' 			=> 'ecpt_publisher',
					'class' 		=> 'ecpt_publisher',
					'type' 			=> 'text',
					'std'			=> ''													
				),
				array(
					'name' 			=> 'Publication Date',
					'desc' 			=> '',
					'id' 			=> 'ecpt_pub_date',
					'class' 		=> 'ecpt_pub_date',
					'type' 			=> 'text',
					'std'			=> ''													
				),
				array(
					'name' 			=> 'Purchase (Amazon) Link',
					'desc' 			=> '(Do NOT include http://)',
					'id' 			=> 'ecpt_pub_link',
					'class' 		=> 'ecpt_pub_link',
					'type' 			=> 'text',
					'std'			=> ''													
				),
				array(
					'name' 			=> 'Role',
					'desc' 			=> '',
					'id' 			=> 'ecpt_pub_role',
					'class' 		=> 'ecpt_pub_role',
					'type' 			=> 'select2',
					'options' => array('author','co-author','editor', 'contributor', 'translator'),
					'std'			=> ''
				),				
				array(
					'name' 			=> 'Author',
					'desc' 			=> '',
					'id' 			=> 'ecpt_pub_author',
					'class' 		=> 'ecpt_pub_author',
					'type' 			=> 'select',
					'std'			=> ''
				),				
				
));	

		
			
add_action('admin_menu', 'ecpt_add_faculty_books_meta_box');
function ecpt_add_faculty_books_meta_box() {

	global $faculty_books_metabox;		

	foreach($faculty_books_metabox['page'] as $page) {
		add_meta_box($faculty_books_metabox['id'], $faculty_books_metabox['title'], 'ecpt_show_faculty_books_box', $page, 'normal', 'default', $faculty_books_metabox);
	}
}

// function to show meta boxes
function ecpt_show_faculty_books_box()	{
	global $post;
	global $faculty_books_metabox;
	global $ecpt_prefix;
	global $wp_version;
	
	// Use nonce for verification
	echo '<input type="hidden" name="ecpt_faculty_books_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';
	
	echo '<table class="form-table">';

	foreach ($faculty_books_metabox['fields'] as $field) {
		// get current post meta data

		$meta = get_post_meta($post->ID, $field['id'], true);
		
		echo '<tr>',
				'<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
				'<td class="ecpt_field_type_' . str_replace(' ', '_', $field['type']) . '">';
		switch ($field['type']) {
			case 'text':
				echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" /><br/>', '', $field['desc'];
				break;
			case 'select' :
				$author_select_query = new WP_Query(array(
					'post-type' => 'people',
					'role' => 'faculty',
					'meta_key' => 'ecpt_people_alpha',
					'orderby' => 'meta_value',
					'order' => 'ASC',
					'posts_per_page' => '-1')); 
				$authors = $author_select_query->get_posts();
				echo '<select name="', $field['id'], '" id="', $field['id'], '">';
				foreach($authors as $author) {
					echo '<option value="' . $author->ID . '"', $meta == $author->ID ? ' selected="selected"' : '', '>', $author->post_title, '</option>';
		}
				echo '</select>';
				break;
			case 'select2':
			echo '<select name="', $field['id'], '" id="', $field['id'], '">';
				foreach ($field['options'] as $option) {
				
					echo '<option value="' . $option . '"', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
				}
				echo '</select>';
			break;
		}
		echo     '<td>',
			'</tr>';
	}
	
	echo '</table>';
}	

add_action('save_post', 'ecpt_faculty_books_save');

// Save data from meta box
function ecpt_faculty_books_save($post_id) {
	global $post;
	global $faculty_books_metabox;
	
	// verify nonce
	if (!isset($_POST['ecpt_faculty_books_meta_box_nonce']) || !wp_verify_nonce($_POST['ecpt_faculty_books_meta_box_nonce'], basename(__FILE__))) {
		return $post_id;
	}

	// check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}

	// check permissions
	if ('page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return $post_id;
		}
	} elseif (!current_user_can('edit_post', $post_id)) {
		return $post_id;
	}
	
	foreach ($faculty_books_metabox['fields'] as $field) {
	
		$old = get_post_meta($post_id, $field['id'], true);
		$new = $_POST[$field['id']];
		
		if ($new && $new != $old) {
			if($field['type'] == 'date') {
				$new = ecpt_format_date($new);
				update_post_meta($post_id, $field['id'], $new);
			} else {
				update_post_meta($post_id, $field['id'], $new);
				
				
			}
		} elseif ('' == $new && $old) {
			delete_post_meta($post_id, $field['id'], $old);
		}
	}
}
$faculty_books_metabox2 = array( 
	'id' => 'faculty_books2',
	'title' => 'Second Author Details',
	'page' => array('post'),
	'context' => 'normal',
	'priority' => 'medium',
	'fields' => array(
				array(
					'name' 			=> 'Is there a second author?',
					'desc' 			=> 'Tick checkbox if yes',
					'id' 			=> 'ecpt_author_cond',
					'class' 		=> 'ecpt_author_cond',
					'type' 			=> 'checkbox',
					'std'			=> ''													
				),
				array(
					'name' 			=> 'Second Author Role',
					'desc' 			=> '',
					'id' 			=> 'ecpt_pub_role2',
					'class' 		=> 'ecpt_pub_role2',
					'type' 			=> 'select2',
					'options' => array('author','co-author','editor', 'contributor', 'translator'),
					'std'			=> ''
				),				
				array(
					'name' 			=> 'Second Author',
					'desc' 			=> '',
					'id' 			=> 'ecpt_pub_author2',
					'class' 		=> 'ecpt_pub_author2',
					'type' 			=> 'select',
					'std'			=> ''
				),				

				
));	
add_action('admin_menu', 'ecpt_add_faculty_books_meta_box2');
function ecpt_add_faculty_books_meta_box2() {

	global $faculty_books_metabox2;		

	foreach($faculty_books_metabox2['page'] as $page) {
		add_meta_box($faculty_books_metabox2['id'], $faculty_books_metabox2['title'], 'ecpt_show_faculty_books_box2', $page, 'normal', 'default', $faculty_books_metabox2);
	}
}

// function to show meta boxes
function ecpt_show_faculty_books_box2()	{
	global $post;
	global $faculty_books_metabox2;
	global $ecpt_prefix;
	global $wp_version;
	
	// Use nonce for verification
	echo '<input type="hidden" name="ecpt_faculty_books_meta_box2_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';
	
	echo '<table class="form-table">';

	foreach ($faculty_books_metabox2['fields'] as $field) {
		// get current post meta data

		$meta = get_post_meta($post->ID, $field['id'], true);
		
		echo '<tr>',
				'<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
				'<td class="ecpt_field_type_' . str_replace(' ', '_', $field['type']) . '">';
		switch ($field['type']) {
			case 'text':
				echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" /><br/>', '', $field['desc'];
				break;
			case 'select' :
				$author_select_query = new WP_Query(array(
					'post-type' => 'people',
					'role' => 'faculty',
					'meta_key' => 'ecpt_people_alpha',
					'orderby' => 'meta_value',
					'order' => 'ASC',
					'posts_per_page' => '-1')); 
				$authors = $author_select_query ->get_posts();
				echo '<select name="', $field['id'], '" id="', $field['id'], '">';
				foreach($authors as $author) {
					echo '<option value="' . $author->ID . '"', $meta == $author->ID ? ' selected="selected"' : '', '>', $author->post_title, '</option>';
				}
				echo '</select>';
				break;
			case 'select2':
			echo '<select name="', $field['id'], '" id="', $field['id'], '">';
				foreach ($field['options'] as $option) {
				
					echo '<option value="' . $option . '"', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
				}
				echo '</select>';
			break;
			case 'checkbox':
				echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />&nbsp;';
				echo $field['desc'];
				break;
		}
		echo     '<td>',
			'</tr>';
	}
	
	echo '</table>';
}	

add_action('save_post', 'ecpt_faculty_books_save2');

// Save data from meta box
function ecpt_faculty_books_save2($post_id) {
	global $post;
	global $faculty_books_metabox2;
	
	// verify nonce
	if (!isset($_POST['ecpt_faculty_books_meta_box2_nonce']) || !wp_verify_nonce($_POST['ecpt_faculty_books_meta_box2_nonce'], basename(__FILE__))) {
		return $post_id;
	}

	// check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}

	// check permissions
	if ('page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return $post_id;
		}
	} elseif (!current_user_can('edit_post', $post_id)) {
		return $post_id;
	}
	
	foreach ($faculty_books_metabox2['fields'] as $field) {
	
		$old = get_post_meta($post_id, $field['id'], true);
		$new = $_POST[$field['id']];
		
		if ($new && $new != $old) {
			if($field['type'] == 'date') {
				$new = ecpt_format_date($new);
				update_post_meta($post_id, $field['id'], $new);
			} else {
				update_post_meta($post_id, $field['id'], $new);
				
				
			}
		} elseif ('' == $new && $old) {
			delete_post_meta($post_id, $field['id'], $old);
		}
	}
}

function check_category_terms(){
 
        // see if we already have populated any terms
    $term = get_terms( 'category', array( 'hide_empty' => false ) );
 
    // if no terms then lets add our terms
    if( empty( $term ) ){
        $terms = define_category_terms();
        foreach( $terms as $term ){
            if( !term_exists( $term['name'], 'category' ) ){
                wp_insert_term( $term['name'], 'category', array( 'slug' => $term['slug'] ) );
            }
        }
    }
}

add_action( 'init', 'check_category_terms' );

function define_category_terms(){
 
$terms = array(
		'0' => array( 'name' => 'Faculty Books','slug' => 'books'),
		);
 
    return $terms;
}

/*************Faculty Books Widget*****************/
class Faculty_Books_Widget extends WP_Widget {
	function Faculty_Books_Widget() {
		$widget_options = array( 'classname' => 'ksas_books', 'description' => __('Displays faculty books at random', 'ksas_books') );
		$control_options = array( 'width' => 300, 'height' => 350, 'id_base' => 'ksas_books-widget' );
		$this->WP_Widget( 'ksas_books-widget', __('Faculty Books', 'ksas_books'), $widget_options, $control_options );
	}

	/* Widget Display */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$quantity = $instance['quantity'];
		if(taxonomy_exists('program')) { 
			$program = $instance['program'];
		}
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;
		if(taxonomy_exists('program')) { 
			$books_widget_query = new WP_Query(array(
						'post_type' => 'post',
						'category_name' => 'books',
						'program' => $program,
						'posts_per_page' => $quantity,
						'orderby' => 'rand',
						));
		} else {
			$books_widget_query = new WP_Query(array(
						'post_type' => 'post',
						'category_name' => 'books',
						'posts_per_page' => $quantity,
						'orderby' => 'rand',
						));
		}
		if ( $books_widget_query->have_posts() ) :  while ($books_widget_query->have_posts()) : $books_widget_query->the_post(); global $post;?>
				<article class="row">
				<?php $faculty_post_id = get_post_meta($post->ID, 'ecpt_pub_author', true);
					  $faculty_post_id2 = get_post_meta($post->ID, 'ecpt_pub_author2', true); ?>
						<a href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail()) { ?> 
								<?php the_post_thumbnail('directory'); ?>
							<?php } ?>
							<h6><?php the_title(); ?></h6>
							<p><b><?php echo get_the_title($faculty_post_id); ?>,&nbsp;<?php echo get_post_meta($post->ID, 'ecpt_pub_role', true); ?>
							<?php if (get_post_meta($post->ID, 'ecpt_author_cond', true) == 'on') { ?><br>
								<?php echo get_the_title($faculty_post_id2); ?> ,&nbsp;<?php echo get_post_meta($post->ID, 'ecpt_pub_role2', true); }?>
							</b></p>
						</a>
				</article>
		<?php endwhile; endif;  echo $after_widget;
	}

	/* Update/Save the widget settings. */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['quantity'] = strip_tags( $new_instance['quantity'] );
		if(taxonomy_exists('program')) { 
			$instance['program'] = strip_tags( $new_instance['program']);
		}
		return $instance;
	}

	/* Widget Options */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => __('Faculty Books', 'ksas_books'), 'quantity' => __('3', 'ksas_books'), 'program' => __('', 'ksas_books'));
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<!-- Number of Stories: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'quantity' ); ?>"><?php _e('Number of stories to display:', 'ksas_books'); ?></label>
			<input id="<?php echo $this->get_field_id( 'quantity' ); ?>" name="<?php echo $this->get_field_name( 'quantity' ); ?>" value="<?php echo $instance['quantity']; ?>" style="width:100%;" />
		</p>
		<!-- Choose Profile Type: Select Box -->
		<?php if(taxonomy_exists('program')) { ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'program' ); ?>"><?php _e('Choose Program:', 'ksas_books'); ?></label> 
			<select id="<?php echo $this->get_field_id( 'program' ); ?>" name="<?php echo $this->get_field_name( 'program' ); ?>" class="widefat" style="width:100%;">
			<?php global $wpdb;
				$categories = get_categories(array(
								'orderby'                  => 'name',
								'order'                    => 'ASC',
								'hide_empty'               => 1,
								'taxonomy' => 'program'));
		    foreach($categories as $category){
		    	$category_choice = $category->slug;
		        $category_title = $category->name; ?>
		       <option value="<?php echo $category_choice; ?>" <?php if ( $category_choice == $instance['category_choice'] ) echo 'selected="selected"'; ?>><?php echo $category_title; ?></option>
		    <?php } ?>
			</select>
		</p>

	<?php }
	}
}

function ksas_load_faculty_books_widget() {
	register_widget('Faculty_Books_Widget');
}
	add_action( 'widgets_init', 'ksas_load_faculty_books_widget' );

?>