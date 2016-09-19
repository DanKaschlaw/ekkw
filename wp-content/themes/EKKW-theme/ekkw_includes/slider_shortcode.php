<?php
function ekkw_slider_inner_page( $atts ) {
$a = shortcode_atts(array(
'slider-name' => null,
),$atts);

$output = '<div class="slider-inner-page"><div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
	<div class="carousel-inner" role="listbox">';
		if( have_rows(($a['slider-name']), 'options') ){
			$rows_counter = 0;
			while( have_rows(($a['slider-name']), 'options') ): the_row();
				$image = get_sub_field('image');
				$text = get_sub_field('text');
				$active_class = '';
				if($rows_counter == 0){
					$active_class = 'active';
				}
				$output .= '<div class="item '.$active_class.'">
					<img class="img-slider-full-width" src="'.$image.'">
					<div class="container content-slider-container">
						<div>'.$text.'</div>
					</div>
				</div>';
				$rows_counter++;
			endwhile;
		}
				$output .=	'</div>
</div></div>';
	return $output;
}

add_shortcode('ekkw_slider', 'ekkw_slider_inner_page');
