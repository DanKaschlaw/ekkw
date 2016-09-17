<?php
function ekkw_slider_inner_page( $atts ) {
$a = shortcode_atts(array(
'image' => null,
'title'=>null,
'text' => null,
'btn-title'=>null,
'link'=>null,
),$atts);

$output =
'<div id="carousel-example-generic" class="carousel slide" data-ride="carousel" data-interval="50000000">
			<div class="carousel-inner" role="listbox">
				<div class="item">
					<img class="img-slider" src="'.($a['image']).'">
					<div class="container content-slider-container">
						<h2>'.($a['title']).'</h2>
						<div>'.($a['text']).'</div>
						<div class="btn-more"><a  href="'.($a['link']).'">'.($a['btn-title']).'</a></div>
					</div>
				</div>
			</div>
		</div>';
return $output;
}

add_shortcode('slider', 'ekkw_slider_inner_page');
