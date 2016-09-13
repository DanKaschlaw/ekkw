<section class="full-width-bcgr">
	<div class="container">
		<h1>Recent events</h1>
		<?php
		$pc = new WP_Query('cat=5&orderby=date&posts_per_page=10'); ?>
		<?php while ($pc->have_posts()) : $pc->the_post();
		$event_date = get_field('event_date',$post);?>
		<div class="item-post">
			<a class="thumbnail-img clearfix" href ="<?php the_permalink(); ?>">
				<?php the_post_thumbnail(array()); ?>
			</a>
			<div class="container-post-news-content">
				<a class="title" href ="<?php the_permalink(); ?>">
					<?php the_title(); ?></a><br/>
				<span><?php echo $event_date ?></span><br/>
				<div class="content"><?php the_content()?></div>
			</div>
		</div>
		<?php endwhile; ?>
	</div>
</section>
