<?php
if( function_exists('acf_add_local_field_group') ):

	acf_add_local_field_group(array (
		'key' => 'group_57d2884f69a3c',
		'title' => 'Event',
		'fields' => array (
			array (
				'key' => 'field_57d2886f569c3',
				'label' => 'Event Date',
				'name' => 'event_date',
				'type' => 'date_picker',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'display_format' => 'd.m.Y',
				'return_format' => 'd.m.Y',
				'first_day' => 1,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_category',
					'operator' => '==',
					'value' => 'category:event',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => 1,
		'description' => '',
	));

	acf_add_local_field_group(array (
		'key' => 'group_57cd55eee3c4e',
		'title' => 'Sliders',
		'fields' => array (
			array (
				'key' => 'field_57cd55ff3535a',
				'label' => 'Home Slider',
				'name' => 'home_slider',
				'type' => 'repeater',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'collapsed' => '',
				'min' => '',
				'max' => '',
				'layout' => 'row',
				'button_label' => 'Add Row',
				'sub_fields' => array (
					array (
						'key' => 'field_57cd56393535b',
						'label' => 'Image Url',
						'name' => 'image_url',
						'type' => 'url',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
					),
					array (
						'key' => 'field_57cd569640378',
						'label' => 'Title Slide',
						'name' => 'title_slide',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array (
						'key' => 'field_57cd56b440379',
						'label' => 'Content Slide',
						'name' => 'content_slide',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array (
						'key' => 'field_57cd56ca4037a',
						'label' => 'Button Link More',
						'name' => 'btn_link_more',
						'type' => 'url',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
					),
					array (
						'key' => 'field_57dd66e7b0646',
						'label' => 'Btn Title',
						'name' => 'btn_title',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
				),
			),
			array (
				'key' => 'field_57dfa67dcb597',
				'label' => 'Inner slider',
				'name' => 'inner_slider',
				'type' => 'repeater',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'collapsed' => '',
				'min' => '',
				'max' => '',
				'layout' => 'row',
				'button_label' => 'Add Row',
				'sub_fields' => array (
					array (
						'key' => 'field_57dfa6a5cb598',
						'label' => 'text',
						'name' => 'text',
						'type' => 'text',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array (
						'key' => 'field_57dfa6aecb599',
						'label' => 'image',
						'name' => 'image',
						'type' => 'url',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
					),
				),
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'options_page',
					'operator' => '==',
					'value' => 'acf-options-home',
				),
			),
			array (
				array (
					'param' => 'options_page',
					'operator' => '==',
					'value' => 'acf-options-home',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => 1,
		'description' => '',
	));

endif;


function acf_add_type_gemeinden() {
	$labels = array(
		"name" => __( 'Gemeinden', 'sage' ),
		"singular_name" => __( 'Gemeinden', 'sage' ),
	);

	$args = array(
		"label" => __( 'Gemeinden', 'sage' ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => false,
		"show_in_menu" => true,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "gemeinden", "with_front" => true ),
		"query_var" => true,

		"supports" => array( "title", "editor", "thumbnail")
	);

	register_post_type( "gemeinden", $args );
}


add_action('init', __NAMESPACE__ . '\\ acf_add_type_gemeinden');
