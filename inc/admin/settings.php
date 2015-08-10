<?php
add_filter( 'fitssp_settings_pages', 'firechat_settings_page' );

/**
 * Add settings page
 *
 * @param array $pages List of settings page
 * @return array
 */
function firechat_settings_page( $pages )
{
	$pages[] = array(
		'id'         => 'chat-settings',
		'option'     => 'firechat',
		'page_title' => __( 'Firechat', 'firechat' ),
		'menu_title' => __( 'Firechat', 'firechat' ),
		'parent'     => 'options-general.php',

		'sections'   => array(
			array(
				'id'     => 'firebase', // Section id
				'title'  => 'Firebase Settings', // Section title
				'desc'   => '', // Section description

				'fields' => array(
					array(
						'id'    => 'url',
						'label' => __( 'Firebase app URL', 'firechat' ),
						'type'  => 'text',
					),
					array(
						'id'    => 'secret',
						'label' => __( 'Firebase app secret', 'firechat' ),
						'type'  => 'text',
					),
				),
			),
		),
	);

	return $pages;
}
