<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="search-field"><?php esc_html_e( 'Поиск', 'test-task' ); ?></label>
	<input type="search" id="search-field" class="search-form__field" placeholder="<?php esc_attr_e( 'Поиск…', 'test-task' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
	<button type="submit" class="search-form__submit"><?php esc_html_e( 'Найти', 'test-task' ); ?></button>
</form>
