<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
	<div class="site-header__inner">
		<a class="site-header__title" href="<?php echo esc_url( home_url( '/' ) ); ?>">Header</a>
	</div>

	<nav class="site-nav" aria-label="<?php esc_attr_e( 'Основная навигация', 'test-task' ); ?>">
		<a class="site-nav__link" href="<?php echo esc_url( home_url( '/' ) ); ?>#articles"><?php esc_html_e( 'Главная', 'test-task' ); ?></a>
		<a class="site-nav__link" href="<?php echo esc_url( home_url( '/' ) ); ?>#articles"><?php esc_html_e( 'Статьи', 'test-task' ); ?></a>
		<a class="site-nav__link" href="<?php echo esc_url( home_url( '/' ) ); ?>#articles"><?php esc_html_e( 'Новости', 'test-task' ); ?></a>
	</nav>
</header>

<main class="site-main">
