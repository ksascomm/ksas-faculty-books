<?php
/**
 * Plugin Name: KSAS Faculty Books
 * Plugin URI: http://krieger.jhu.edu/
 * Description: Creates faculty books custom post type.
 * Version: 4.0
 * Author: KSAS Communications
 * Author URI: mailto:ksaswen@jhu.edu
 * License: GPL2
 *
 * @package KSAS_Faculty_Books
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Faculty Books Custom Post Type.
 */
function faculty_books_custom_post_type() {
	$labels = array(
		'name'               => _x( 'Faculty Books', 'Post Type General Name', 'ksas_books' ),
		'singular_name'      => _x( 'Faculty Book', 'Post Type Singular Name', 'ksas_books' ),
		'menu_name'          => __( 'Faculty Books', 'ksas_books' ),
		'all_items'          => __( 'All Faculty Books', 'ksas_books' ),
		'add_new'            => __( 'Add New', 'ksas_books' ),
		'add_new_item'       => __( 'Add New Faculty Book', 'ksas_books' ),
		'edit_item'          => __( 'Edit Faculty Book', 'ksas_books' ),
		'view_item'          => __( 'View Faculty Book', 'ksas_books' ),
		'search_items'       => __( 'Search Faculty Book', 'ksas_books' ),
		'not_found'          => __( 'Not Found', 'ksas_books' ),
		'not_found_in_trash' => __( 'Not found in Trash', 'ksas_books' ),
	);

	$args = array(
		'labels'              => $labels,
		'public'              => true,
		'has_archive'         => true,
		'menu_icon'           => 'dashicons-book-alt',
		'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions' ),
		'taxonomies'          => array( 'books' ),
		'show_in_rest'        => true,
		'capability_type'     => 'post',
		'hierarchical'        => false,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'menu_position'       => 5,
	);

	register_post_type( 'faculty-books', $args );
}
add_action( 'init', 'faculty_books_custom_post_type' );

/**
 * Register Faculty Books Field Group via ACF
 */
add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'        => 'group_faculty_books_details',
				'title'      => 'Faculty Book Details',
				'fields'     => array(
					// Primary Author (Relationship to People CPT).
					array(
						'key'           => 'field_ecpt_pub_author',
						'label'         => 'Author',
						'name'          => 'ecpt_pub_author', // Matches your current meta key.
						'type'          => 'post_object',
						'post_type'     => array( 'people' ),
						'return_format' => 'id',
						'ui'            => 1,
					),
					array(
						'key'     => 'field_ecpt_pub_role',
						'label'   => 'Role',
						'name'    => 'ecpt_pub_role',
						'type'    => 'select',
						'choices' => array(
							'author'      => 'Author',
							'co-author'   => 'Co-Author',
							'editor'      => 'Editor',
							'co-editor'   => 'Co-Editor',
							'contributor' => 'Contributor',
							'translator'  => 'Translator',
						),
					),
					array(
						'key'   => 'field_ecpt_publisher',
						'label' => 'Publisher',
						'name'  => 'ecpt_publisher',
						'type'  => 'text',
					),
					array(
						'key'          => 'field_ecpt_pub_date',
						'label'        => 'Publication Date',
						'name'         => 'ecpt_pub_date',
						'type'         => 'text',
						'instructions' => 'Year Only',
					),
					array(
						'key'          => 'field_ecpt_pub_link',
						'label'        => 'Publisher/Purchase Link',
						'name'         => 'ecpt_pub_link',
						'type'         => 'url', // Better validation than standard text.
						'instructions' => 'Please paste full url, including https://',
					),
					// Second Author Conditional Logic.
					array(
						'key'   => 'field_ecpt_author_cond',
						'label' => 'Is there a second author?',
						'name'  => 'ecpt_author_cond',
						'type'  => 'true_false',
						'ui'    => 1,
					),
					array(
						'key'               => 'field_ecpt_pub_author2',
						'label'             => 'Second Author',
						'name'              => 'ecpt_pub_author2',
						'type'              => 'post_object',
						'post_type'         => array( 'people' ),
						'return_format'     => 'id',
						'ui'                => 1,
						'conditional_logic' => array(
							array(
								array(
									'field'    => 'field_ecpt_author_cond',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),
					),
					array(
						'key'               => 'field_ecpt_pub_role2',
						'label'             => 'Second Author Role',
						'name'              => 'ecpt_pub_role2',
						'type'              => 'select',
						'choices'           => array(
							'author'      => 'Author',
							'co-author'   => 'Co-Author',
							'editor'      => 'Editor',
							'co-editor'   => 'Co-Editor',
							'contributor' => 'Contributor',
							'translator'  => 'Translator',
						),
						'conditional_logic' => array(
							array(
								array(
									'field'    => 'field_ecpt_author_cond',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),
					),
				),
				'location'   => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'faculty-books',
						),
					),
				),
				'menu_order' => 0,
				'position'   => 'normal',
				'style'      => 'default',
			)
		);
	}
);

/**
 * Load Widget Class
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-faculty-books-widget.php';
/**
 * Register Faculty Books Widget.
 */
function ksas_register_faculty_books_widget() {
	register_widget( 'Faculty_Books_Widget' );
}
add_action( 'widgets_init', 'ksas_register_faculty_books_widget' );

/**
 * Custom CSS for Faculty Books Admin
 */
function ksas_faculty_books_admin_styles() {
	$screen = get_current_screen();
	if ( 'faculty-books' === $screen->post_type ) {
		echo '<style>
            .acf-postbox .acf-field {
                width: 100% !important;
                max-width: 1000px !important;
            }
        </style>';
	}
}
add_action( 'admin_head', 'ksas_faculty_books_admin_styles' );

/**
 * Exclude Graduate Students and Job Market Candidate roles from the Faculty Books Author dropdowns.
 *
 * @param array $args    The query arguments for the Post Object field.
 * @return array Modified query arguments for the WP_Query.
 */
function ksas_exclude_grad_students_from_books( $args ) {
	// Ensure tax_query is an array if not already set.
	if ( ! isset( $args['tax_query'] ) ) {
		$args['tax_query'] = array();
	}
	// Use a tax_query to exclude the 'graduate-student' slug.
	$args['tax_query'] = array(
		array(
			'taxonomy' => 'role',
			'field'    => 'slug',
			'terms'    => array( 'graduate-student', 'job-market-candidate' ),
			'operator' => 'NOT IN',
		),
	);

	return $args;
}

// Apply to the Primary Author field.
add_filter( 'acf/fields/post_object/query/key=field_ecpt_pub_author', 'ksas_exclude_grad_students_from_books', 10, 3 );

// Apply to the Second Author field.
add_filter( 'acf/fields/post_object/query/key=field_ecpt_pub_author2', 'ksas_exclude_grad_students_from_books', 10, 3 );
