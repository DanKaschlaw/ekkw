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
	$post_date = get_the_date('F jS, Y');

	$output =
'<div class="item-post">
	<a class="thumbnail-img clearfix" href ="'. get_permalink($current_post).'">
		'.get_the_post_thumbnail($current_post,'thumbnail').'
	</a>
	<div class="container-post-news-content">
		<a class="title" href ="'.get_permalink($current_post).'">'
		. $current_post->post_title.'
		</a><br/>
		<span>'.$post_date.'</span><br/>
		<div class="content">' . $current_post->post_content .'</div>
		<a href="'. get_permalink($current_post).'" class="read-more pull-right">Weiterlesen &rsaquo;</a>
	</div>

</div>';
	return $output;
}

add_shortcode('ekkw-post-news', 'ekkw_post_news');

function ekkw_post_event_home( $atts ) {
	$a = shortcode_atts( array(
		'post' => null,
	), $atts );

	$current_post = get_post( $a['post'] );

	if ( has_category( 'Event', $current_post ) ) {
		$output =
			'<div class="item-post">
				<a class="thumbnail-img clearfix" href ="' . get_permalink( $current_post ) . '">
					' . get_the_post_thumbnail( $current_post, 'thumbnail' ) . '
				</a>
				<div class="container-post-event-content">
					<a class="title" href ="' . get_permalink( $current_post ) . '">'
			. $current_post->post_title . '
					</a><br/>
					<span>' . get_field( 'event_date', $current_post ) . '</span>
				</div>

			</div>';

		return $output;
	}
}
add_shortcode('ekkw-event-home', 'ekkw_post_event_home');

function ekkw_post_event_inner( $atts ) {
	$a = shortcode_atts(array(
		'post' => null,
	),$atts);

	$current_post =  get_post($a['post']);

	$event_date = get_field('event_date',$current_post);
	list($day,$month,$year) = explode('.',$event_date);
	$output =
		'<div class="event-post clearfix">
			<div class="event-date">
				<span class="day">'.$day.'.</span><br/>
				<span class="month">'.$month.'.</span>
				<span class="year">'.$year.'</span>
			</div>

			<a class="thumbnail-img clearfix" href ="'. get_permalink($current_post).'">
				'.get_the_post_thumbnail($current_post,'thumbnail').'
			</a>
			<div class="container-event-content">
				<a class="title" href ="'.get_permalink($current_post).'">'
				. $current_post->post_title.'
				</a><br/>
			<div class="content">' . $current_post->post_content .'</div>
			</div>
			<a href="'. get_permalink($current_post).'" class="read-more">Weiterlesen &rsaquo;</a>

	</div>';
	return $output;
}

add_shortcode('ekkw-event-inner', 'ekkw_post_event_inner');
