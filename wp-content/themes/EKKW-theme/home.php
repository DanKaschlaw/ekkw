<?php
/**
 * Template Name: Home
 */
?>
<section class = "top-news full-width-bcgr">
		<div id="carousel-example-generic" class="carousel slide" data-ride="carousel" data-interval="50000000">
			<div class="carousel-inner" role="listbox">
				<?php if( have_rows('home_slider', 'options') ): ?>
					<?php
					$rows_counter = 0;
					while( have_rows('home_slider', 'options') ): the_row();
						$image = get_sub_field('image_url');
						$content = get_sub_field('content_slide');
						$title = get_sub_field('title_slide');
						$href = get_sub_field('btn_link_more');
						?>
						<div class="item <?php echo  $rows_counter == 0? 'active' : '' ?>">
							<img class="img-slider-full-width" src="<?php echo $image ?>">
							<div class="container content-slider-container">
								<h2><?php echo $title ?> </h2>
								<div> <?php echo $content ?></div>
								<div class="btn-more"><a  href="<?php echo $href;?>">More</a></div>
							</div>
						</div>
						<?php
						$rows_counter++;
					endwhile; ?>

				<?php endif; ?>
			</div>
		</div>
</section>
<?php while (have_posts()) : the_post(); ?>
	<?php the_content(); ?>
<?php endwhile; ?>