<?php
/**
 * Quiz Blocks Custom Post Type Registration
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

class Quiz_Blocks_CPT {

	public function __construct() {

		add_action( 'init', array( $this, 'register_cpt' ), PHP_INT_MAX );

		add_filter( 'manage_quiz_posts_columns', array( $this, 'set_custom_columns' ) );
		add_action( 'manage_quiz_posts_custom_column', array( $this, 'custom_column_data' ), PHP_INT_MAX, 2 );

		add_action( 'admin_init', array( $this, 'clear_quiz_submissions' ) );

		add_action( 'admin_notices', array( $this, 'trashed_submissions_admin_notice' ) );

		add_filter( 'removable_query_args', array( $this, 'remove_custom_query_args' ) );

		add_filter( 'enter_title_here', array( $this, 'change_placeholder_title_text' ) );

	}

	/**
	 * Register the Quiz post type.
	 */
	public function register_cpt() {

		$labels = array(
			'name'                  => _x( 'Quizzes', 'Post Type General Name', 'quiz-blocks' ),
			'singular_name'         => _x( 'Quiz', 'Post Type Singular Name', 'quiz-blocks' ),
			'menu_name'             => __( 'Quizzes', 'quiz-blocks' ),
			'name_admin_bar'        => __( 'Quizzes', 'quiz-blocks' ),
			'archives'              => __( 'Quiz Archives', 'quiz-blocks' ),
			'attributes'            => __( 'Quiz Attributes', 'quiz-blocks' ),
			'parent_item_colon'     => __( 'Parent Quiz:', 'quiz-blocks' ),
			'all_items'             => __( 'All Quizzes', 'quiz-blocks' ),
			'add_new_item'          => __( 'Add New Quiz', 'quiz-blocks' ),
			'add_new'               => __( 'Add New Quiz', 'quiz-blocks' ),
			'new_item'              => __( 'New Quiz', 'quiz-blocks' ),
			'edit_item'             => __( 'Edit Quiz', 'quiz-blocks' ),
			'update_item'           => __( 'Update Quiz', 'quiz-blocks' ),
			'view_item'             => __( 'View Quiz', 'quiz-blocks' ),
			'view_items'            => __( 'View Quizzes', 'quiz-blocks' ),
			'search_items'          => __( 'Search Quiz', 'quiz-blocks' ),
			'not_found'             => __( 'Quiz Not found', 'quiz-blocks' ),
			'not_found_in_trash'    => __( 'Quiz Not found in Trash', 'quiz-blocks' ),
			'featured_image'        => __( 'Quiz Image', 'quiz-blocks' ),
			'set_featured_image'    => __( 'Set quiz image', 'quiz-blocks' ),
			'remove_featured_image' => __( 'Remove quiz image', 'quiz-blocks' ),
			'use_featured_image'    => __( 'Use as quiz image', 'quiz-blocks' ),
			'insert_into_item'      => __( 'Insert into quiz', 'quiz-blocks' ),
			'uploaded_to_this_item' => __( 'Uploaded to this quiz', 'quiz-blocks' ),
			'items_list'            => __( 'Quiz list', 'quiz-blocks' ),
			'items_list_navigation' => __( 'Quiz list navigation', 'quiz-blocks' ),
			'filter_items_list'     => __( 'Filter quiz list', 'quiz-blocks' ),
		);

		$args = array(
			'label'               => __( 'Quiz', 'quiz-blocks' ),
			'description'         => __( 'Quiz post type', 'quiz-blocks' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor' ),
			'taxonomies'          => array( 'category' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-forms',
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'page',
			'show_in_rest'        => true,
		);

		register_post_type( 'quiz', $args );

	}

	/**
	 * Add custom columns to the Quiz post type.
	 *
	 * @param array $columns The original post columns.
	 *
	 * @return array Filtered post columns array.
	 */
	public function set_custom_columns( $columns ) {
		unset( $columns['date'] );
		unset( $columns['categories'] );

		$columns['submissions'] = __( 'Quiz Submissions', 'quiz-blocks' );

		return $columns;
	}

	/**
	 * Display our custom Quiz data in the custom columns.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function custom_column_data( $column, $post_id ) {

		if ( 'submissions' !== $column ) {

			return;

		}

		$submissions = get_post_meta( $post_id, 'results' );

		printf(
			/* translators: %s is an integer value for the number of submission. */
			_n( '%s submission', '%s submissions', number_format_i18n( count( $submissions ) ), 'quiz-blocks' ),
			number_format_i18n( count( $submissions ) )
		);

		$url = wp_nonce_url(
			add_query_arg(
				array(
					'wpquiz-action' => 'trash-submissions',
					'quiz-id'       => $post_id,
				),
				admin_url( 'edit.php?post_type=quiz' )
			),
			'trash-submissions'
		);

		?>
		<div class="row-actions">
			<span class="trash">
				<a href="<?php echo esc_url( $url ); ?>" class="submitdelete" aria-label="<?php esc_attr_e( 'Clear All User Submissions', 'quiz-blocks' ); ?>"><?php esc_attr_e( 'Delete Quiz Submissions', 'quiz-blocks' ); ?></a>
			</span>
		</div>
		<?php

	}

	/**
	 * Clear all submissions for a quiz.
	 */
	public function clear_quiz_submissions() {

		if ( ! isset( $_GET['wpquiz-action'] ) || ! isset( $_GET['quiz-id'] ) || ! isset( $_GET['_wpnonce'] ) ) {

			return;

		}

		$action  = filter_input( INPUT_GET, 'wpquiz-action', FILTER_SANITIZE_STRING );
		$quiz_id = filter_input( INPUT_GET, 'quiz-id', FILTER_VALIDATE_INT );
		$nonce   = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $nonce, 'trash-submissions' ) ) {

			return;

		}

		delete_post_meta( $quiz_id, 'results' );

		wp_safe_redirect(
			add_query_arg(
				array(
					'submissions-deleted' => true,
					'quiz-id'             => $quiz_id,
				),
				admin_url( 'edit.php?post_type=quiz' )
			),
		);

	}

	/**
	 * Display an admin notice when the submissions have been deleted.
	 */
	public function trashed_submissions_admin_notice() {

		if ( ! isset( $_GET['submissions-deleted'] ) || ! isset( $_GET['quiz-id'] ) ) {

			return;

		}

		$quiz_id = filter_input( INPUT_GET, 'quiz-id', FILTER_VALIDATE_INT );

		$quiz = get_post( $quiz_id );

		printf(
			'<div class="notice notice-success">
				<p>%1$s</p>
			</div>',
			sprintf(
				/* translators: %s is the quiz name. */
				esc_html__( '%s submissions deleted.', 'quiz-blocks' ),
				$quiz->post_title
			)
		);

	}

	/**
	 * Remove our custom query arguments from the URL.
	 *
	 * @param array $query_args Original query arguments array.
	 *
	 * @return array Filtered list of query arguments to remove.
	 */
	public function remove_custom_query_args( $query_args ) {

		array_push( $query_args, 'submissions-deleted', 'quiz-id' );

		return $query_args;

	}

	public function change_placeholder_title_text( $title ) {

		$screen = get_current_screen();

		if ( 'quiz' === $screen->post_type ) {

			$title = __( 'Quiz Name', 'quiz-blocks' );

		}

		return $title;

	}

}

new Quiz_Blocks_CPT();
