<?php while (have_posts()) : the_post(); ?>
		<article <?php post_class(); ?>>
			<div class="entry-content">
					<?php // selection_events_by_category($post->post_title);
					the_content();
					?>
		</article>
	</div>

<?php endwhile; ?>
