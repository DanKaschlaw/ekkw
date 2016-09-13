<section class="full-width-bcgr">
	<div class="container">
		<h1>Recent news</h1>
		<?php $args = array(
			'numberposts' => 10,
			'post_status' =>'publish');

		$recent_posts = wp_get_recent_posts($args);
		foreach( $recent_posts as $recent ){
			echo '<div class="item-post">
				<a class="thumbnail-img clearfix" href ="'. get_permalink($recent['ID']).'">
					'.get_the_post_thumbnail($recent['ID']).'
				</a>
				<div class="container-post-news-content">
					<a class="title" href ="'.get_permalink($recent['ID']).'">'
								. $recent['post_title'].'
					</a><br/>
					<span>'.$recent['post_date'].'</span><br/>
					<div class="content">' . $recent['post_content'] .'</div>
				</div>

			</div>';
		}
		?>
	</div>
</section>
