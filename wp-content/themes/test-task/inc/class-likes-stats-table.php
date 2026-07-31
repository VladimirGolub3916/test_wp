<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Test_Task_Likes_Stats_Table extends WP_List_Table {
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'stat',
				'plural'   => 'stats',
				'ajax'     => false,
			)
		);
	}

	public function get_columns() {
		return array(
			'post_title' => __( 'Статья', 'test-task' ),
			'likes'      => __( 'Лайки', 'test-task' ),
			'dislikes'   => __( 'Дизлайки', 'test-task' ),
			'total'      => __( 'Всего', 'test-task' ),
		);
	}

	public function get_sortable_columns() {
		return array(
			'post_title' => array( 'post_title', false ),
			'likes'      => array( 'likes', true ),
			'dislikes'   => array( 'dislikes', true ),
			'total'      => array( 'total', true ),
		);
	}

	public function prepare_items() {
		$per_page = 20;
		$orderby  = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'post_title';
		$order    = isset( $_GET['order'] ) ? strtoupper( sanitize_key( wp_unslash( $_GET['order'] ) ) ) : 'ASC';
		$allowed  = array( 'post_title', 'likes', 'dislikes', 'total' );
		$orderby  = in_array( $orderby, $allowed, true ) ? $orderby : 'post_title';
		$order    = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'ASC';
		$paged    = $this->get_pagenum();
		$total    = Test_Task_Likes_Database::get_posts_stats_count();

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$this->items           = Test_Task_Likes_Database::get_posts_stats( $orderby, $order, $per_page, ( $paged - 1 ) * $per_page );
		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
			)
		);
	}

	public function column_default( $item, $column_name ) {
		if ( in_array( $column_name, array( 'likes', 'dislikes', 'total' ), true ) ) {
			return esc_html( (string) absint( $item[ $column_name ] ) );
		}

		return '';
	}

	public function column_post_title( $item ) {
		$title = $item['post_title'] ? $item['post_title'] : __( 'Без названия', 'test-task' );

		return sprintf(
			'<strong><a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a></strong>',
			esc_url( get_permalink( (int) $item['post_id'] ) ),
			esc_html( $title )
		);
	}
}
