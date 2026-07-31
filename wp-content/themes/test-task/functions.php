<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TEST_TASK_VERSION', '1.2.0' );
define( 'TEST_TASK_DIR', get_template_directory() );
define( 'TEST_TASK_URI', get_template_directory_uri() );

require_once TEST_TASK_DIR . '/inc/class-likes-database.php';
require_once TEST_TASK_DIR . '/inc/class-likes-handler.php';
require_once TEST_TASK_DIR . '/inc/class-likes-admin.php';

function test_task_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_image_size( 'test-task-card', 760, 428, true );
}
add_action( 'after_setup_theme', 'test_task_setup' );

function test_task_enqueue_assets() {
	wp_enqueue_style( 'test-task-main', TEST_TASK_URI . '/assets/css/main.css', array(), TEST_TASK_VERSION );
	wp_enqueue_script( 'test-task-likes', TEST_TASK_URI . '/assets/js/likes.js', array(), TEST_TASK_VERSION, true );
	wp_localize_script(
		'test-task-likes',
		'testTaskLikes',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'test_task_vote' ),
			'error'   => __( 'Не удалось сохранить голос. Попробуйте ещё раз.', 'test-task' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'test_task_enqueue_assets' );

function test_task_init() {
	Test_Task_Likes_Database::maybe_create_table();
	Test_Task_Likes_Handler::init();
	Test_Task_Likes_Admin::init();
}
add_action( 'init', 'test_task_init' );

function test_task_activate_theme() {
	Test_Task_Likes_Database::maybe_create_table();
}
add_action( 'after_switch_theme', 'test_task_activate_theme' );

function test_task_get_news_query() {
	$paged = max( 1, absint( get_query_var( 'paged' ) ), absint( get_query_var( 'page' ) ) );

	return new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 10,
			'category_name'       => 'news',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'paged'               => $paged,
			'ignore_sticky_posts' => true,
		)
	);
}

function test_task_get_card_image_url( $post_id ) {
	return (string) get_the_post_thumbnail_url( $post_id, 'test-task-card' );
}

function test_task_get_card_author( $post_id ) {
	$author = get_post_meta( $post_id, '_test_task_card_author', true );

	if ( $author ) {
		return (string) $author;
	}

	return get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) );
}

function test_task_get_vote_counts( $post_id ) {
	return Test_Task_Likes_Database::get_counts( (int) $post_id );
}

function test_task_get_user_vote( $post_id ) {
	return Test_Task_Likes_Handler::get_user_vote( (int) $post_id );
}
