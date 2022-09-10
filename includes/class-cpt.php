<?php
/**
 * Quiz Blocks Custom Post Type Registration
 *
 * @package Quiz_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * Quiz_Blocks_CPT class.
 *
 * Handles custom post type functionality for Quiz_Blocks.
 */
class Quiz_Blocks_CPT {

	/**
	 * Quiz_Blocks_CPT constructor.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register_cpt' ), PHP_INT_MAX );

		add_filter( 'manage_quiz_posts_columns', array( $this, 'set_custom_columns' ) );

		add_action( 'manage_quiz_posts_custom_column', array( $this, 'submissions_column_data' ), PHP_INT_MAX, 2 );
		add_action( 'manage_quiz_posts_custom_column', array( $this, 'shortcode_column_data' ), PHP_INT_MAX, 2 );

		add_action( 'admin_init', array( $this, 'clear_quiz_submissions' ) );

		add_action( 'admin_notices', array( $this, 'trashed_submissions_admin_notice' ) );

		add_filter( 'removable_query_args', array( $this, 'remove_custom_query_args' ) );

		add_filter( 'enter_title_here', array( $this, 'change_placeholder_title_text' ) );

		add_filter( 'the_title', array( $this, 'filter_no_quiz_name_title' ) );

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
		$columns['shortcode']   = __( 'Quiz Shortcode', 'quiz-blocks' );

		return $columns;
	}

	/**
	 * Display our custom Quiz data in the custom columns.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function submissions_column_data( $column, $post_id ) {

		if ( 'submissions' !== $column ) {

			return;

		}

		$submissions = get_post_meta( $post_id, 'results', true );
		$submissions = ! $submissions ? array() : $submissions;

		printf(
			/* translators: %s is an integer value for the number of submission. */
			esc_html( _n( '%s submission', '%s submissions', number_format_i18n( count( $submissions ) ), 'quiz-blocks' ) ),
			esc_html( number_format_i18n( count( $submissions ) ) )
		);

		$delete_url = wp_nonce_url(
			add_query_arg(
				array(
					'quiz-blocks-action' => 'trash-submissions',
					'quiz-id'            => $post_id,
				),
				admin_url( 'edit.php?post_type=quiz' )
			),
			'trash-submissions'
		);

		$view_submission_url = add_query_arg(
			array(
				'quiz' => $post_id,
			),
			admin_url( 'edit.php?post_type=quiz&page=view-submissions' )
		);

		$quiz_name = get_the_title( $post_id );

		$quiz_name = empty( $quiz_name ) ? __( '(no name)', 'quiz-blocks' ) : $quiz_name;

		?>
		<div class="row-actions">
			<span class="view">
				<a href="<?php echo esc_url( $view_submission_url ); ?>" class="view-submissions" aria-label="<?php esc_attr_e( 'View Submissions', 'quiz-blocks' ); ?>"><?php esc_attr_e( 'View Submissions', 'quiz-blocks' ); ?></a>
			</span>
			|
			<span class="trash">
				<a
					href="<?php echo esc_url( $delete_url ); ?>"
					class="submitdelete"
					onclick="return confirm('<?php printf( /* translators: %s is the name of the quiz. */ esc_attr__( 'Are you sure you want to delete the %s submissions?', 'quiz-blocks' ), esc_attr( $quiz_name ) ); ?>')"
					aria-label="<?php esc_attr_e( 'Clear All User Submissions', 'quiz-blocks' ); ?>"
				>
					<?php esc_attr_e( 'Delete Submissions', 'quiz-blocks' ); ?>
				</a>
			</span>
		</div>
		<?php

	}

	/**
	 * Display our quiz shortcode back to the user.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function shortcode_column_data( $column, $post_id ) {

		if ( 'shortcode' !== $column ) {

			return;

		}

		printf(
			'<code>[quiz id="%s"]</code>',
			esc_html( $post_id )
		);

	}

	/**
	 * Clear all submissions for a quiz.
	 */
	public function clear_quiz_submissions() {

		if ( ! isset( $_GET['quiz-blocks-action'] ) || ! isset( $_GET['quiz-id'] ) || ! isset( $_GET['_wpnonce'] ) ) {

			return;

		}

		$url_attributes = wp_parse_args( wp_get_referer() );

		$action  = filter_input( INPUT_GET, 'quiz-blocks-action', FILTER_SANITIZE_STRING );
		$quiz_id = filter_input( INPUT_GET, 'quiz-id', FILTER_VALIDATE_INT );
		$nonce   = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $nonce, 'trash-submissions' ) ) {

			return;

		}

		$redirect_url = ( isset( $url_attributes['page'] ) && 'view-submissions' === $url_attributes['page'] ) ? sprintf(
			admin_url( 'edit.php?post_type=quiz&page=view-submissions&quiz=%s' ),
			$quiz_id,
		) : admin_url( 'edit.php?post_type=quiz' );

		delete_post_meta( $quiz_id, 'results' );

		wp_safe_redirect(
			add_query_arg(
				array(
					'submissions-deleted' => true,
					'quiz-id'             => $quiz_id,
				),
				$redirect_url
			),
		);

	}

	/**
	 * Display an admin notice when the submissions have been deleted.
	 */
	public function trashed_submissions_admin_notice() {

		if ( ! isset( $_GET['submissions-deleted'] ) || ! isset( $_GET['quiz-id'] ) ) { // phpcs:ignore

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
				esc_html( empty( $quiz->post_title ) ? __( '(no name)', 'block-quiz' ) : $quiz->post_title )
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

	/**
	 * Change the quiz custom post type title placeholder.
	 *
	 * @param string $title The title text placeholder.
	 *
	 * @return string The title placeholder text.
	 */
	public function change_placeholder_title_text( $title ) {

		$screen = get_current_screen();

		if ( 'quiz' === $screen->post_type ) {

			$title = __( 'Quiz Name', 'quiz-blocks' );

		}

		return $title;

	}

	/**
	 * Filter the title of a quiz when no title is added.
	 *
	 * @param string $post_title The post title.
	 *
	 * @return string The entered post title when one exists, else (no name).
	 */
	public function filter_no_quiz_name_title( $post_title ) {

		global $pagenow;

		$post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING );

		if ( ! is_admin() || empty( $pagenow ) || 'edit.php' !== $pagenow || ! $post_type || 'quiz' !== $post_type ) {

			return $post_title;

		}

		return empty( $post_title ) ? '(no name)' : $post_title;

	}

}

new Quiz_Blocks_CPT();
