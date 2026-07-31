<?php

$post_id   = get_the_ID();
$counts    = test_task_get_vote_counts( $post_id );
$user_vote = test_task_get_user_vote( $post_id );
$image_url = test_task_get_card_image_url( $post_id );
$author    = test_task_get_card_author( $post_id );
?>

<article <?php post_class( 'article-card' ); ?> id="post-<?php the_ID(); ?>">
	<a class="article-card__image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( $image_url ) : ?>
			<img src="<?php echo esc_url( $image_url ); ?>" alt="" loading="lazy">
		<?php endif; ?>
	</a>

	<div class="article-card__body">
		<h2 class="article-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<div class="article-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></div>

		<div class="article-card__bottom">
			<p class="article-card__author"><span class="article-card__author-prefix"><?php esc_html_e( 'Автор:', 'test-task' ); ?></span> <span class="article-card__author-name"><?php echo esc_html( $author ); ?></span></p>

			<div class="article-card__votes" data-likes-root data-post-id="<?php echo esc_attr( (string) $post_id ); ?>" data-user-vote="<?php echo esc_attr( (string) $user_vote ); ?>">
				<button type="button" class="article-card__vote article-card__vote--like<?php echo 1 === $user_vote ? ' is-active' : ''; ?>" data-vote-type="like" aria-pressed="<?php echo 1 === $user_vote ? 'true' : 'false'; ?>">
					<span class="article-card__vote-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 4V20M4 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
					</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Нравится', 'test-task' ); ?></span>
				</button>

				<span class="article-card__vote-count" data-like-count><?php echo esc_html( (string) $counts['likes'] ); ?></span>

				<button type="button" class="article-card__vote article-card__vote--dislike<?php echo -1 === $user_vote ? ' is-active' : ''; ?>" data-vote-type="dislike" aria-pressed="<?php echo -1 === $user_vote ? 'true' : 'false'; ?>">
					<span class="article-card__vote-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
					</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Не нравится', 'test-task' ); ?></span>
				</button>
			</div>
		</div>
	</div>
</article>
