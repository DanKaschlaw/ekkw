<?php
function ekkw_contact_person( $atts ) {
	$a = shortcode_atts(array(
	'gender' => null,
	'last-name' => null,
	'first-name' => null,
	'position' => null,
	'area' => null,
	'address' => null,
	'email' => null,
	'phone' => null,
	'fax' => null,
	'photo' => null,
	'free-text' => null
	),$atts);

$output =
	'<div class="contact-person clearfix">

		<div class="thumbnail-img clearfix">
			<img src="'.($a['photo']).'">
		</div>
		<div class="name">
			<span class="lastname">'.($a['last-name']).'</span>
			<span class="firstname">'.($a['first-name']).'</span>
		</div>
		<div class="row address-and-phone">
			<div class="col-xs-12 col-sm-6">
				<div class="area">'.($a['area']).'</div>
				<div class="address">'.($a['address']).'</div>
			</div>
			<div class="col-xs-12 col-sm-6">
				<div class="phone">Telefone: '.($a['phone']).'</div>
				<div class="fax"> Telefax: '.($a['fax']).'</div>
			</div>
		</div>
		<div class="email">'.($a['email']).'</div>
		<div class="text">'.($a['free-text']).'</div>

	</div>';
return $output;
}

add_shortcode('ekkw-contact-person', 'ekkw_contact_person');
