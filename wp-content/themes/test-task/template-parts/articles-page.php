<?php

$articles_query = test_task_get_news_query();
?>

<div class="site-content-grid">
	<section class="articles-panel" id="articles" aria-labelledby="articles-title">
		<h1 class="articles-panel__title" id="articles-title"><?php esc_html_e( 'Статьи', 'test-task' ); ?></h1>

		<?php if ( $articles_query->have_posts() ) : ?>
			<div class="posts-list">
				<?php while ( $articles_query->have_posts() ) : ?>
					<?php $articles_query->the_post(); ?>
					<?php get_template_part( 'template-parts/content', 'card' ); ?>
				<?php endwhile; ?>
			</div>

			<nav class="articles-pagination" aria-label="<?php esc_attr_e( 'Постраничная навигация', 'test-task' ); ?>">
				<a class="page-numbers prev" href="#articles"><?php esc_html_e( 'Назад', 'test-task' ); ?></a>
				<a class="page-numbers" href="#articles">1</a>
				<a class="page-numbers" href="#articles">2</a>
				<span class="page-numbers current" aria-current="page">3</span>
				<span class="page-numbers dots" aria-hidden="true">…</span>
				<a class="page-numbers" href="#articles">8</a>
				<a class="page-numbers" href="#articles">9</a>
				<a class="page-numbers" href="#articles">10</a>
				<a class="page-numbers next" href="#articles"><?php esc_html_e( 'Вперёд', 'test-task' ); ?></a>
			</nav>
		<?php else : ?>
			<p class="posts-list__empty"><?php esc_html_e( 'Материалы не найдены.', 'test-task' ); ?></p>
		<?php endif; ?>
	</section>

	<aside class="site-sidebar" aria-label="<?php esc_attr_e( 'Боковая панель', 'test-task' ); ?>">Sidebar</aside>
</div>

<?php wp_reset_postdata();
