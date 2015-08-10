<?php
/*
Plugin Name: FitWP Simple Settings Page
Plugin URI: http://fitwp.com
Description: Create simple settings page
Author: FitWP
Author URI: http://fitwp.com
Version: 0.1

Supports: text, textarea, checkbox, checkbox list, radio, select, post
*/

if ( !class_exists( 'FITSSP' ) )
{
	class FITSSP
	{
		/**
		 * Page hook
		 * @var string
		 */
		public $page_hook;

		/**
		 * Page options
		 * @var array
		 */
		public $page;

		/**
		 * Option name in database
		 * @var string
		 */
		public $option;

		/**
		 * Construct option page from $option
		 *
		 * @param array $page Array of options
		 *
		 * @return FITSSP
		 */
		function __construct( $page )
		{
			$this->page = $page;
			$this->normalize();

			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
		}

		/**
		 * Add menu
		 *
		 * @return void
		 */
		function admin_menu()
		{
			$page = $this->page;
			if ( !$page['parent'] )
				$this->page_hook = add_menu_page( $page['menu_title'], $page['menu_title'], $page['capability'], $page['id'], array( $this, 'show' ), $page['menu_icon'], $page['position'] );
			else
				$this->page_hook = add_submenu_page( $page['parent'], $page['page_title'], $page['menu_title'], $page['capability'], $page['id'], array( $this, 'show' ) );
		}

		/**
		 * Display notices
		 *
		 * @return void
		 */
		function admin_notices()
		{
			$screen = get_current_screen();
			if ( $screen->id == $this->page_hook )
				settings_errors( $this->page['id'] );
		}

		/**
		 * Register option and show sections, fields
		 *
		 * @return void
		 */
		function admin_init()
		{
			register_setting( $this->option, $this->option, array( $this, 'sanitize' ) );

			$option = get_option( $this->option, array() );
			foreach ( $this->page['sections'] as $section )
			{
				add_settings_section( $section['id'], $section['title'], array( $this, 'show_section_description' ), $this->page['id'] );

				foreach ( $section['fields'] as $field )
				{
					$value = isset( $option[$field['id']] ) ? $option[$field['id']] : $field['default'];
					$callback = method_exists( $this, 'show_field_' . $field['type'] ) ? 'show_field_' . $field['type'] : 'show_field';

					add_settings_field(
						$field['id'],
						$field['label'],
						array( $this, $callback ),
						$this->page['id'],
						$section['id'],
						array(
							'field' => $field,
							'value' => $value,
						)
					);
				}
			}
		}

		/**
		 * Save data from page
		 *
		 * @param array $option Submitted page settings
		 *
		 * @return array
		 */
		function sanitize( $option )
		{
			// Bypass if not in current page
			if ( empty( $_POST['option_page'] ) || $_POST['option_page'] != $this->option )
				return $option;

			// Reset
			if ( isset( $_POST['reset'] ) )
			{
				add_settings_error( $this->page['id'], 'reset', __( 'Settings Reset', 'fitssp' ), 'updated' );
				return array();
			}

			// Save
			add_settings_error( $this->page['id'], 'updated', __( 'Settings Updated', 'fitssp' ), 'updated' );

			$option = apply_filters( 'fitssp_option', $option );
			return $option;
		}

		/**
		 * Output the main admin page
		 *
		 * @return void
		 */
		function show()
		{
			?>
			<div class="wrap">
				<form method="post" action="options.php">
					<h2><?php echo $this->page['page_title']; ?></h2>
					<?php settings_fields( $this->option ); ?>
					<?php do_settings_sections( $this->page['id'] ); ?>
					<p class="submit">
						<?php submit_button( __( 'Save Settings', 'fitssp' ), 'primary', 'submit', false ); ?>
						<?php submit_button( __( 'Reset Settings', 'fitssp' ), 'secondary', 'reset', false, array( 'onclick' => 'return confirm(\'' . esc_js( __( 'This action can not be undone. Are you sure you want to reset?', 'fitssp' ) ) . '\');' ) ); ?>
					</p>
				</form>
			</div>
			<?php
		}

		/**
		 * Show section description
		 *
		 * @param string $wp_section Current section
		 *
		 * @return void
		 */
		function show_section_description( $wp_section )
		{
			foreach ( $this->page['sections'] as $section )
			{
				if ( $section['id'] == $wp_section['id'] )
				{
					echo '<p>' . $section['desc'] . '</p>';
					return;
				}
			}
		}

		/**
		 * Show custom field
		 *
		 * @param array $args Array ('field', 'value')
		 *
		 * @return void
		 */
		function show_field( $args )
		{
			do_action( 'fitssp_show_field', $args );
			do_action( "fitssp_show_field_{$args['field']['type']}", $args );
		}

		/**
		 * Show text field
		 *
		 * @param array $args Array ('field', 'value')
		 *
		 * @return void
		 */
		function show_field_text( $args )
		{
			printf(
				'<input type="text" class="regular-text" name="%s" value="%s">%s',
				$args['field']['name'],
				esc_attr( $args['value'] ),
				$args['field']['desc'] ? '<p class="description">' . $args['field']['desc'] . '</p>' : ''
			);
		}

		/**
		 * Show textarea
		 *
		 * @param array $args Array ('field', 'value')
		 *
		 * @return void
		 */
		function show_field_textarea( $args )
		{
			printf(
				'<textarea class="large-text" cols="50" rows="7" name="%s">%s</textarea>%s',
				$args['field']['name'],
				esc_textarea( $args['value'] ),
				$args['field']['desc'] ? '<p class="description">' . $args['field']['desc'] . '</p>' : ''
			);
		}

		/**
		 * Show select box
		 *
		 * @param array $args Array ('field', 'value')
		 *
		 * @return void
		 */
		function show_field_select( $args )
		{
			$html = '<select name="' . $args['field']['name'] . '">';
			foreach ( $args['field']['options'] as $value => $label )
			{
				$html .= sprintf( '<option value="%s"%s>%s</option>', $value, selected( $value, $args['value'], false ), $label );
			}
			$html .= '</select>';
			$html .= $args['field']['desc'] ? '<p class="description">' . $args['field']['desc'] . '</p>' : '';
			echo $html;
		}

		/**
		 * Show radio box
		 *
		 * @param array $args Array ('field', 'value')
		 *
		 * @return void
		 */
		function show_field_radio( $args )
		{
			$html = array();
			foreach ( $args['field']['options'] as $value => $label )
			{
				$html[] = sprintf(
					'<label><input type="radio" name="%s" value="%s"%s> %s</label>',
					$args['field']['name'],
					$value,
					checked( $value, $args['value'], false ),
					$label
				);
			}
			$html = implode( ' ', $html );
			$html .= $args['field']['desc'] ? '<p class="description">' . $args['field']['desc'] . '</p>' : '';
			echo $html;
		}

		/**
		 * Show check box
		 *
		 * @param array $args Array ('field', 'value')
		 *
		 * @return void
		 */
		function show_field_checkbox( $args )
		{
			printf(
				'<input type="checkbox" name="%s" value="1"%s>%s',
				$args['field']['name'],
				checked( $args['value'], 1, false ),
				$args['field']['desc'] ? '<p class="description">' . $args['field']['desc'] . '</p>' : ''
			);
		}

		/**
		 * Show check box list
		 *
		 * @param array $args Array ('field', 'value')
		 *
		 * @return void
		 */
		function show_field_checkbox_list( $args )
		{
			$html = array();
			foreach ( $args['field']['options'] as $value => $label )
			{
				$html[] = sprintf(
					'<label><input type="checkbox" name="%s" value="%s"%s> %s</label>',
					$args['field']['name'],
					$value,
					checked( in_array( $value, (array) $args['value'] ), 1, false ),
					$label
				);
			}
			$html = implode( '<br>', $html );
			$html .= $args['field']['desc'] ? '<p class="description">' . $args['field']['desc'] . '</p>' : '';

			echo $html;
		}

		/**
		 * Show post
		 *
		 * @param array $args Array ('field', 'value')
		 *
		 * @return void
		 */
		function show_field_post( $args )
		{
			$posts = get_posts( array(
				'post_type'   => $args['field']['post_type'],
				'numberposts' => -1,
				'orderby'     => 'title',
				'order'       => 'ASC',
			) );
			if ( empty( $posts ) )
				return;

			$html = '<select name="' . $args['field']['name'] . '">';
			foreach ( $posts as $p )
			{
				$html .= sprintf( '<option value="%s"%s>%s</option>', $p->ID, selected( in_array( $p->ID, (array) $args['value'] ), true, false ), $p->post_title );
			}
			$html .= '</select>';
			$html .= $args['field']['desc'] ? '<p class="description">' . $args['field']['desc'] . '</p>' : '';
			echo $html;
		}

		/**
		 * Normalize page options
		 *
		 * @return void
		 */
		function normalize()
		{
			$this->option = $this->page['option'];
			$this->page = wp_parse_args( $this->page, array(
				'page_title' => '',
				'menu_title' => '',
				'capability' => 'edit_theme_options',
				'menu_icon'  => '',
				'position'   => null,
				'parent'     => '', // ID of parent page. Optional.
			) );

			// Normalize sections
			foreach ( $this->page['sections'] as &$section )
			{
				$section = wp_parse_args( $section, array(
					'title' => '',
					'desc'  => '',
				) );

				// Normalize fields
				foreach ( $section['fields'] as &$field )
				{
					$field = wp_parse_args( $field, array(
						'default' => '',
						'desc'    => '',
					) );
					$field['name'] = sprintf(
						'%s[%s]%s',
						$this->option,
						$field['id'],
						'checkbox_list' == $field['type'] ? '[]' : ''
					);
				}
			}
		}
	}

	add_action( 'init', 'fitssp_register_settings_pages' );

	/**
	 * Register settings pages
	 *
	 * @return void
	 */
	function fitssp_register_settings_pages()
	{
		if ( !is_admin() )
			return;

		$pages = apply_filters( 'fitssp_settings_pages', array() );
		foreach ( $pages as $page )
		{
			new FITSSP( $page );
		}
	}
}
