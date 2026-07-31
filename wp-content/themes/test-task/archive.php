<?php
get_header();
?>

<section class="archive-hero">
	<div class="archive-hero__inner">
		<p class="archive-hero__eyebrow"><?php esc_html_e( 'Раздел статей', 'test-task' ); ?></p>
		<h1 class="archive-hero__title"><?php the_archive_title(); ?></h1>
		<?php if ( category_description() ) : ?>
			<p class="archive-hero__text"><?php echo wp_kses_post( category_description() ); ?></p>
		<?php endif; ?>
	</div>
</section>

<section class="posts-preview">
	<div class="posts-preview__header">
		<h2 class="posts-preview__title"><?php esc_html_e( 'Анонсы статей', 'test-task' ); ?></h2>
		<p class="posts-preview__subtitle"><?php esc_html_e( 'Читайте свежие материалы и ставьте лайки прямо из списка.', 'test-task' ); ?></p>
	</div>

	<?php if ( have_posts() ) : ?>
		<div class="posts-list">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', 'card' );
			endwhile;
			?>
		</div>

		<div class="pagination-wrap">
			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 2,
					'prev_text' => '&larr;',
					'next_text' => '&rarr;',
				)
			);
			?>
		</div>
	<?php else : ?>
		<p class="posts-list__empty"><?php esc_html_e( 'Записей не найдено.', 'test-task' ); ?></p>
	<?php endif; ?>
</section>

<?php
get_footer();
