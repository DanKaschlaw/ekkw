<?php
/**
 * Created by PhpStorm.
 * User: diana
 * Date: 08.09.16
 * Time: 15:47
 */
function ekkw_post_news( $atts ) {
	$a = shortcode_atts(array(
		'post' => null,
	),$atts);

	$current_post =  get_post($a['post']);


	$output =
'<div class="item-post">
	<a class="thumbnail-img clearfix" href ="'. get_permalink($current_post).'">
		'.get_the_post_thumbnail($current_post,'thumbnail').'
	</a>
	<div class="container-post-news-content">
		<a class="title" href ="'.get_permalink($current_post).'">'
		. $current_post->post_title.'
		</a><br/>
		<span>'.$current_post->post_date.'</span><br/>
		<div class="content">' . $current_post->post_content .'</div>
	</div>

</div>';
	return $output;
}

add_shortcode('ekkw-post-news', 'ekkw_post_news');

function ekkw_post_event( $atts ) {
	$a = shortcode_atts(array(
		'post' => null,
	),$atts);

	$current_post =  get_post($a['post']);


	$output =
		'<div class="item-post">
	<a class="thumbnail-img clearfix" href ="'. get_permalink($current_post).'">
		'.get_the_post_thumbnail($current_post,'thumbnail').'
	</a>
	<div class="container-post-event-content">
		<a class="title" href ="'.get_permalink($current_post).'">'
			. $current_post->post_title.'
		</a><br/>
		<span>'.$current_post->post_date.'</span>
	</div>

</div>';
	return $output;
}

add_shortcode('ekkw-post-event', 'ekkw_post_event');
