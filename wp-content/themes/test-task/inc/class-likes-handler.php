<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Test_Task_Likes_Handler {
	public static function init() {
		add_action( 'wp_ajax_test_task_vote', array( __CLASS__, 'handle_vote' ) );
		add_action( 'wp_ajax_nopriv_test_task_vote', array( __CLASS__, 'handle_vote' ) );
	}

	public static function get_client_ip() {
		$ip_address = isset( $_SERVER['REMOTE_ADDR'] ) ? trim( (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		return filter_var( $ip_address, FILTER_VALIDATE_IP ) ? $ip_address : '0.0.0.0';
	}

	public static function get_user_vote( $post_id ) {
		return Test_Task_Likes_Database::get_user_vote( $post_id, self::get_client_ip() );
	}

	public static function handle_vote() {
		if ( ! check_ajax_referer( 'test_task_vote', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Сессия истекла. Обновите страницу.', 'test-task' ) ), 403 );
		}

		$post_id   = isset( $_POST['postId'] ) ? absint( $_POST['postId'] ) : 0;
		$vote_type = isset( $_POST['voteType'] ) ? sanitize_key( wp_unslash( $_POST['voteType'] ) ) : '';

		if ( ! $post_id || 'post' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Материал недоступен.', 'test-task' ) ), 404 );
		}

		if ( ! in_array( $vote_type, array( 'like', 'dislike' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Некорректный голос.', 'test-task' ) ), 400 );
		}

		$numeric_vote = 'like' === $vote_type ? 1 : -1;
		$ip_address   = self::get_client_ip();
		$current_vote = Test_Task_Likes_Database::get_user_vote( $post_id, $ip_address );

		if ( $current_vote !== $numeric_vote ) {
			$page_url = isset( $_POST['pageUrl'] ) ? self::get_page_url( wp_unslash( $_POST['pageUrl'] ) ) : home_url( '/' );

			if ( ! Test_Task_Likes_Database::save_vote( $post_id, $ip_address, $page_url, $numeric_vote ) ) {
				wp_send_json_error( array( 'message' => __( 'Не удалось сохранить голос.', 'test-task' ) ), 500 );
			}
		}

		$counts = Test_Task_Likes_Database::get_counts( $post_id );

		wp_send_json_success(
			array(
				'likes'    => $counts['likes'],
				'dislikes' => $counts['dislikes'],
				'userVote' => Test_Task_Likes_Database::get_user_vote( $post_id, $ip_address ),
			)
		);
	}

	private static function get_page_url( $value ) {
		$url       = esc_url_raw( $value );
		$home_url  = home_url( '/' );
		$home_host = strtolower( (string) wp_parse_url( $home_url, PHP_URL_HOST ) );
		$url_host  = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
		$home_port = (int) wp_parse_url( $home_url, PHP_URL_PORT );
		$url_port  = (int) wp_parse_url( $url, PHP_URL_PORT );
		$scheme    = wp_parse_url( $url, PHP_URL_SCHEME );

		if ( ! $url || ! in_array( $scheme, array( 'http', 'https' ), true ) || $url_host !== $home_host || $url_port !== $home_port ) {
			return $home_url;
		}

		return substr( $url, 0, 255 );
	}
}
