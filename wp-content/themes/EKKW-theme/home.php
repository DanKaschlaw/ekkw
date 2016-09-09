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
<section class="descr-top-news full-width-bcgr">

</section>
<!--<section class="full-width-bcgr news">
	<div class="container">
		<h2 class="text-center">Meldungen</h2>
		<?php /*$args = array(
			'numberposts' => 3,
		    'post_status' =>'publish',
			'category' => 0
			);

		$recent_posts = wp_get_recent_posts($args);
		foreach( $recent_posts as $recent ){
			$post_thumbnail = get_the_post_thumbnail($recent["ID"],'thumbnail');
			echo '<div class="item-post col-xs-12 col-sm-6 col-md-4">
					<div class="img-item-post"><a href="' . get_permalink($recent["ID"]) .'">'.$post_thumbnail .'</a></div>
					<h4>'.$recent["post_title"].'</h4>
					<div class="date-item-post">'.$recent["post_date"].'</div>
					<div class="descr-item-post">'.$recent["post_content"].'</div>
				</div>';
			}
		*/?>
	</div>
	<div class="btn-more-posts text-center">Alle Meldungen</div>
</section>
<section class="news full-width-bcgr">
	<div class="container">
		<h2 class="text-center">Nachste Veranstaltungen</h2>
		<?php /*$args = array(
			'numberposts' => 4,
			'post_status' =>'publish',
			'category' => 'event');

		$recent_posts = wp_get_recent_posts($args);
		foreach( $recent_posts as $recent ){
			$post_thumbnail = get_the_post_thumbnail($recent["ID"],'thumbnail');
			echo '<div class="item-post col-xs-12 col-sm-6 col-md-3">
					<div class="img-item-post"><a href="' . get_permalink($recent["ID"]) .'">'.$post_thumbnail .'</a></div>
					<h4>'.$recent["post_title"].'</h4>
					<div class="date-item-post">'.$recent["post_date"].'</div>
				</div>';
		}
		*/?>
	</div>
	<div class="btn-more-posts text-center">Alle Veranstaltungen</div>
</section>-->
<?php while (have_posts()) : the_post(); ?>
	<?php the_content(); ?>
<?php endwhile; ?>