<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Test_Task_Likes_Database {
	const TABLE = 'article_likes';
	const DB_VERSION = '1.1.0';

	public static function table_name() {
		global $wpdb;

		return $wpdb->prefix . self::TABLE;
	}

	private static function get_news_category_id() {
		$category = get_category_by_slug( 'news' );

		return $category ? (int) $category->term_id : 0;
	}

	public static function maybe_create_table() {
		if ( self::DB_VERSION === get_option( 'test_task_likes_db_version' ) ) {
			return;
		}

		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name      = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			post_id bigint(20) unsigned NOT NULL,
			ip_address varchar(45) NOT NULL,
			page_url varchar(255) NOT NULL,
			vote_type tinyint(1) NOT NULL,
			voted_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY post_ip (post_id, ip_address),
			KEY post_id (post_id),
			KEY voted_at (voted_at)
		) {$charset_collate};";

		dbDelta( $sql );
		update_option( 'test_task_likes_db_version', self::DB_VERSION );
	}

	public static function save_vote( $post_id, $ip_address, $page_url, $vote_type ) {
		if ( ! in_array( $vote_type, array( 1, -1 ), true ) ) {
			return false;
		}

		global $wpdb;

		$table = self::table_name();
		$now   = current_time( 'mysql' );
		$sql   = $wpdb->prepare(
			"INSERT INTO {$table} (post_id, ip_address, page_url, vote_type, voted_at)
			VALUES (%d, %s, %s, %d, %s)
			ON DUPLICATE KEY UPDATE
			page_url = VALUES(page_url),
			vote_type = VALUES(vote_type),
			voted_at = VALUES(voted_at)",
			$post_id,
			$ip_address,
			$page_url,
			$vote_type,
			$now
		);

		return false !== $wpdb->query( $sql );
	}

	public static function get_counts( $post_id ) {
		global $wpdb;

		$table  = self::table_name();
		$counts = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					COALESCE(SUM(vote_type = 1), 0) AS likes,
					COALESCE(SUM(vote_type = -1), 0) AS dislikes
				FROM {$table}
				WHERE post_id = %d",
				$post_id
			),
			ARRAY_A
		);

		return array(
			'likes'    => (int) ( $counts['likes'] ?? 0 ),
			'dislikes' => (int) ( $counts['dislikes'] ?? 0 ),
		);
	}

	public static function get_user_vote( $post_id, $ip_address ) {
		global $wpdb;

		$table = self::table_name();
		$vote  = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT vote_type FROM {$table} WHERE post_id = %d AND ip_address = %s",
				$post_id,
				$ip_address
			)
		);

		return in_array( (int) $vote, array( 1, -1 ), true ) ? (int) $vote : 0;
	}

	public static function get_posts_stats( $orderby, $order, $per_page, $offset ) {
		global $wpdb;
		$category_id = self::get_news_category_id();

		if ( ! $category_id ) {
			return array();
		}

		$columns = array(
			'post_title' => 'p.post_title',
			'likes'      => 'likes',
			'dislikes'   => 'dislikes',
			'total'      => 'total',
		);
		$order_by = isset( $columns[ $orderby ] ) ? $columns[ $orderby ] : $columns['post_title'];
		$order    = 'DESC' === $order ? 'DESC' : 'ASC';
		$table    = self::table_name();
		$posts    = $wpdb->posts;
		$relations = $wpdb->term_relationships;
		$taxonomy  = $wpdb->term_taxonomy;
		$sql      = "SELECT
			p.ID AS post_id,
			p.post_title,
			COALESCE(SUM(l.vote_type = 1), 0) AS likes,
			COALESCE(SUM(l.vote_type = -1), 0) AS dislikes,
			COUNT(l.id) AS total
			FROM {$posts} p
			INNER JOIN {$relations} r ON r.object_id = p.ID
			INNER JOIN {$taxonomy} t ON t.term_taxonomy_id = r.term_taxonomy_id
			LEFT JOIN {$table} l ON l.post_id = p.ID
			WHERE p.post_type = 'post' AND p.post_status = 'publish' AND t.taxonomy = 'category' AND t.term_id = %d
			GROUP BY p.ID, p.post_title
			ORDER BY {$order_by} {$order}
			LIMIT %d OFFSET %d";

		return $wpdb->get_results( $wpdb->prepare( $sql, $category_id, $per_page, $offset ), ARRAY_A );
	}

	public static function get_posts_stats_count() {
		global $wpdb;
		$category_id = self::get_news_category_id();

		if ( ! $category_id ) {
			return 0;
		}

		$posts     = $wpdb->posts;
		$relations = $wpdb->term_relationships;
		$taxonomy  = $wpdb->term_taxonomy;
		$sql       = "SELECT COUNT(DISTINCT p.ID)
			FROM {$posts} p
			INNER JOIN {$relations} r ON r.object_id = p.ID
			INNER JOIN {$taxonomy} t ON t.term_taxonomy_id = r.term_taxonomy_id
			WHERE p.post_type = 'post' AND p.post_status = 'publish' AND t.taxonomy = 'category' AND t.term_id = %d";

		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $category_id ) );
	}

	public static function get_all_votes( $per_page, $offset ) {
		global $wpdb;
		$category_id = self::get_news_category_id();

		if ( ! $category_id ) {
			return array();
		}

		$table     = self::table_name();
		$posts     = $wpdb->posts;
		$relations = $wpdb->term_relationships;
		$taxonomy  = $wpdb->term_taxonomy;
		$sql   = "SELECT l.*, p.post_title
			FROM {$table} l
			INNER JOIN {$posts} p ON p.ID = l.post_id
			INNER JOIN {$relations} r ON r.object_id = p.ID
			INNER JOIN {$taxonomy} t ON t.term_taxonomy_id = r.term_taxonomy_id
			WHERE t.taxonomy = 'category' AND t.term_id = %d
			ORDER BY l.voted_at DESC, l.id DESC
			LIMIT %d OFFSET %d";

		return $wpdb->get_results( $wpdb->prepare( $sql, $category_id, $per_page, $offset ) );
	}

	public static function get_total_votes_count() {
		global $wpdb;
		$category_id = self::get_news_category_id();

		if ( ! $category_id ) {
			return 0;
		}

		$table     = self::table_name();
		$posts     = $wpdb->posts;
		$relations = $wpdb->term_relationships;
		$taxonomy  = $wpdb->term_taxonomy;
		$sql       = "SELECT COUNT(*)
			FROM {$table} l
			INNER JOIN {$posts} p ON p.ID = l.post_id
			INNER JOIN {$relations} r ON r.object_id = p.ID
			INNER JOIN {$taxonomy} t ON t.term_taxonomy_id = r.term_taxonomy_id
			WHERE t.taxonomy = 'category' AND t.term_id = %d";

		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $category_id ) );
	}
}
