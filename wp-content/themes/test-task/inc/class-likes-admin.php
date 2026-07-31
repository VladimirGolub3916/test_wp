<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TEST_TASK_DIR . '/inc/class-likes-stats-table.php';

class Test_Task_Likes_Admin {
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
	}

	public static function register_menu() {
		add_menu_page(
			__( 'Лайки статей', 'test-task' ),
			__( 'Лайки', 'test-task' ),
			'manage_options',
			'test-task-likes-stats',
			array( __CLASS__, 'render_stats_page' ),
			'dashicons-thumbs-up',
			26
		);

		add_submenu_page(
			'test-task-likes-stats',
			__( 'Журнал голосов', 'test-task' ),
			__( 'Журнал голосов', 'test-task' ),
			'manage_options',
			'test-task-likes-log',
			array( __CLASS__, 'render_log_page' )
		);
	}

	public static function render_stats_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Недостаточно прав.', 'test-task' ) );
		}

		$table = new Test_Task_Likes_Stats_Table();
		$table->prepare_items();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Статистика лайков', 'test-task' ); ?></h1>
			<form method="get">
				<input type="hidden" name="page" value="test-task-likes-stats">
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
	}

	public static function render_log_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Недостаточно прав.', 'test-task' ) );
		}

		$per_page = 20;
		$paged    = max( 1, isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 1 );
		$offset   = ( $paged - 1 ) * $per_page;
		$total    = Test_Task_Likes_Database::get_total_votes_count();
		$votes    = Test_Task_Likes_Database::get_all_votes( $per_page, $offset );
		$pages    = (int) ceil( $total / $per_page );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Журнал голосов', 'test-task' ); ?></h1>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Статья', 'test-task' ); ?></th>
						<th><?php esc_html_e( 'Голос', 'test-task' ); ?></th>
						<th><?php esc_html_e( 'IP', 'test-task' ); ?></th>
						<th><?php esc_html_e( 'Страница', 'test-task' ); ?></th>
						<th><?php esc_html_e( 'Время', 'test-task' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $votes ) ) : ?>
						<tr><td colspan="5"><?php esc_html_e( 'Голосов пока нет.', 'test-task' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $votes as $vote ) : ?>
							<tr>
								<td>
									<?php if ( $vote->post_title ) : ?>
										<a href="<?php echo esc_url( get_permalink( (int) $vote->post_id ) ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $vote->post_title ); ?></a>
									<?php else : ?>
										<?php esc_html_e( 'Удалённый материал', 'test-task' ); ?>
									<?php endif; ?>
								</td>
								<td><?php echo 1 === (int) $vote->vote_type ? esc_html__( 'Лайк', 'test-task' ) : esc_html__( 'Дизлайк', 'test-task' ); ?></td>
								<td><?php echo esc_html( $vote->ip_address ); ?></td>
								<td><a href="<?php echo esc_url( $vote->page_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $vote->page_url ); ?></a></td>
								<td><?php echo esc_html( mysql2date( 'd.m.Y H:i', $vote->voted_at ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if ( $pages > 1 ) : ?>
				<div class="tablenav"><div class="tablenav-pages">
					<?php
					echo wp_kses_post(
						paginate_links(
							array(
								'base'      => add_query_arg( array( 'page' => 'test-task-likes-log', 'paged' => '%#%' ), admin_url( 'admin.php' ) ),
								'format'    => '',
								'current'   => $paged,
								'total'     => $pages,
								'prev_text' => '&laquo;',
								'next_text' => '&raquo;',
							)
						)
					);
					?>
				</div></div>
			<?php endif; ?>
		</div>
		<?php
	}
}
