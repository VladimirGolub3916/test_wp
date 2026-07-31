<?php
get_header();
?>

<section class="single-post-page">
	<div class="single-post-page__inner">
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class( 'single-post' ); ?> id="post-<?php the_ID(); ?>">
				<?php $categories = get_the_category(); ?>
				<?php if ( ! empty( $categories ) ) : ?>
					<p class="single-post__category">
						<a href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>">
							<?php echo esc_html( $categories[0]->name ); ?>
						</a>
					</p>
				<?php endif; ?>

				<header class="single-post__header">
					<h1 class="single-post__title"><?php the_title(); ?></h1>
					<ul class="single-post__meta">
						<li><?php esc_html_e( 'Автор:', 'test-task' ); ?> <?php the_author(); ?></li>
						<li><?php echo esc_html( get_the_date() ); ?></li>
						<li>
							<a href="<?php echo esc_url( get_comments_link() ); ?>">
								<?php comments_number( esc_html__( 'Комментариев нет', 'test-task' ), esc_html__( '1 комментарий', 'test-task' ), esc_html__( '% комментариев', 'test-task' ) ); ?>
							</a>
						</li>
					</ul>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="single-post__image">
						<?php the_post_thumbnail( 'large' ); ?>
					</div>
				<?php endif; ?>

				<div class="single-post__content">
					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; ?>
	</div>
</section>

<?php get_footer();
