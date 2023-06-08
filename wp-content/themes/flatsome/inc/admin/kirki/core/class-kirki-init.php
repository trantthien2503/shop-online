<?php
class Kirki_Init {
	private $control_types = array();
	private static $show_fa_nag = false;
	public function __construct() {
		self::set_url();
		add_action( 'after_setup_theme', array( $this, 'set_url' ) );
		add_action( 'wp_loaded', array( $this, 'add_to_customizer' ), 1 );
		add_filter( 'kirki_control_types', array( $this, 'default_control_types' ) );

		add_action( 'customize_register', array( $this, 'remove_panels' ), 99999 );
		add_action( 'customize_register', array( $this, 'remove_sections' ), 99999 );
		add_action( 'customize_register', array( $this, 'remove_controls' ), 99999 );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_init', array( $this, 'dismiss_nag' ) );

		new Kirki_Values();
		new Kirki_Sections();
	}
	public static function set_url() {
		if ( Kirki_Util::is_plugin() ) {
			return;
		}

		// Get correct URL and path to wp-content.
		$content_url = untrailingslashit( dirname( dirname( get_stylesheet_directory_uri() ) ) );
		$content_dir = wp_normalize_path( untrailingslashit( WP_CONTENT_DIR ) );

		Kirki::$url = str_replace( $content_dir, $content_url, wp_normalize_path( Kirki::$path ) );

		// Apply the kirki_config filter.
		$config = apply_filters( 'kirki_config', array() );
		if ( isset( $config['url_path'] ) ) {
			Kirki::$url = $config['url_path'];
		}

		// Make sure the right protocol is used.
		Kirki::$url = set_url_scheme( Kirki::$url );
	}

	public function default_control_types( $control_types = array() ) {
		$this->control_types = array(
			'checkbox'              => 'Kirki_Control_Checkbox',
			'kirki-background'      => 'Kirki_Control_Background',
			'code_editor'           => 'Kirki_Control_Code',
			'kirki-color'           => 'Kirki_Control_Color',
			'kirki-color-palette'   => 'Kirki_Control_Color_Palette',
			'kirki-custom'          => 'Kirki_Control_Custom',
			'kirki-date'            => 'Kirki_Control_Date',
			'kirki-dashicons'       => 'Kirki_Control_Dashicons',
			'kirki-dimension'       => 'Kirki_Control_Dimension',
			'kirki-dimensions'      => 'Kirki_Control_Dimensions',
			'kirki-editor'          => 'Kirki_Control_Editor',
			'kirki-image'           => 'Kirki_Control_Image',
			'kirki-multicolor'      => 'Kirki_Control_Multicolor',
			'kirki-multicheck'      => 'Kirki_Control_MultiCheck',
			'kirki-number'          => 'Kirki_Control_Number',
			'kirki-palette'         => 'Kirki_Control_Palette',
			'kirki-radio'           => 'Kirki_Control_Radio',
			'kirki-radio-buttonset' => 'Kirki_Control_Radio_ButtonSet',
			'kirki-radio-image'     => 'Kirki_Control_Radio_Image',
			'repeater'              => 'Kirki_Control_Repeater',
			'kirki-select'          => 'Kirki_Control_Select',
			'kirki-slider'          => 'Kirki_Control_Slider',
			'kirki-sortable'        => 'Kirki_Control_Sortable',
			'kirki-spacing'         => 'Kirki_Control_Dimensions',
			'kirki-switch'          => 'Kirki_Control_Switch',
			'kirki-generic'         => 'Kirki_Control_Generic',
			'kirki-toggle'          => 'Kirki_Control_Toggle',
			'kirki-typography'      => 'Kirki_Control_Typography',
			'image'                 => 'Kirki_Control_Image',
			'cropped_image'         => 'Kirki_Control_Cropped_Image',
			'upload'                => 'Kirki_Control_Upload',
		);
		return array_merge( $this->control_types, $control_types );
	}
	public function add_to_customizer() {
		$this->fields_from_filters();
		add_action( 'customize_register', array( $this, 'register_control_types' ) );
		add_action( 'customize_register', array( $this, 'add_panels' ), 97 );
		add_action( 'customize_register', array( $this, 'add_sections' ), 98 );
		add_action( 'customize_register', array( $this, 'add_fields' ), 99 );
	}
	public function register_control_types() {
		global $wp_customize;

		$section_types = apply_filters( 'kirki_section_types', array() );
		foreach ( $section_types as $section_type ) {
			$wp_customize->register_section_type( $section_type );
		}

		$this->control_types = $this->default_control_types();
		if ( ! class_exists( 'WP_Customize_Code_Editor_Control' ) ) {
			unset( $this->control_types['code_editor'] );
		}
		foreach ( $this->control_types as $key => $classname ) {
			if ( ! class_exists( $classname ) ) {
				unset( $this->control_types[ $key ] );
			}
		}

		$skip_control_types = apply_filters(
			'kirki_control_types_exclude',
			array(
				'Kirki_Control_Repeater',
				'WP_Customize_Control',
			)
		);

		foreach ( $this->control_types as $control_type ) {
			if ( ! in_array( $control_type, $skip_control_types, true ) && class_exists( $control_type ) ) {
				$wp_customize->register_control_type( $control_type );
			}
		}
	}
	public function add_panels() {
		if ( ! empty( Kirki::$panels ) ) {
			foreach ( Kirki::$panels as $panel_args ) {
				// Extra checks for nested panels.
				if ( isset( $panel_args['panel'] ) ) {
					if ( isset( Kirki::$panels[ $panel_args['panel'] ] ) ) {
						// Set the type to nested.
						$panel_args['type'] = 'kirki-nested';
					}
				}

				new Kirki_Panel( $panel_args );
			}
		}
	}
	public function add_sections() {
		if ( ! empty( Kirki::$sections ) ) {
			foreach ( Kirki::$sections as $section_args ) {
				// Extra checks for nested sections.
				if ( isset( $section_args['section'] ) ) {
					if ( isset( Kirki::$sections[ $section_args['section'] ] ) ) {
						// Set the type to nested.
						$section_args['type'] = 'kirki-nested';
						// We need to check if the parent section is nested inside a panel.
						$parent_section = Kirki::$sections[ $section_args['section'] ];
						if ( isset( $parent_section['panel'] ) ) {
							$section_args['panel'] = $parent_section['panel'];
						}
					}
				}
				new Kirki_Section( $section_args );
			}
		}
	}

	public function add_fields() {
		global $wp_customize;
		foreach ( Kirki::$fields as $args ) {

			// Create the settings.
			new Kirki_Settings( $args );

			// Check if we're on the customizer.
			// If we are, then we will create the controls, add the scripts needed for the customizer
			// and any other tweaks that this field may require.
			if ( $wp_customize ) {

				// Create the control.
				new Kirki_Control( $args );

			}
		}
	}
	private function fields_from_filters() {
		$fields = apply_filters( 'kirki_controls', array() );
		$fields = apply_filters( 'kirki_fields', $fields );

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				Kirki::add_field( 'global', $field );
			}
		}
	}

	public static function is_plugin() {
		return Kirki_Util::is_plugin();
	}
	public static function get_variables() {

		// Log error for developers.
		_doing_it_wrong( __METHOD__, esc_html__( 'We detected you\'re using Kirki_Init::get_variables(). Please use Kirki_Util::get_variables() instead.', 'kirki' ), '3.0.10' );
		return Kirki_Util::get_variables();
	}
	public function remove_panels( $wp_customize ) {
		foreach ( Kirki::$panels_to_remove as $panel ) {
			$wp_customize->remove_panel( $panel );
		}
	}
	public function remove_sections( $wp_customize ) {
		foreach ( Kirki::$sections_to_remove as $section ) {
			$wp_customize->remove_section( $section );
		}
	}

	public function remove_controls( $wp_customize ) {
		foreach ( Kirki::$controls_to_remove as $control ) {
			$wp_customize->remove_control( $control );
		}
	}
	public function admin_notices() {

		// No need for a nag if we don't need to recommend installing the FA plugin.
		if ( ! self::$show_fa_nag ) {
			return;
		}

		// No need for a nag if FA plugin is already installed.
		if ( defined( 'FONTAWESOME_DIR_PATH' ) ) {
			return;
		}

		// No need for a nag if current user can't install plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// No need for a nag if user has dismissed it.
		$dismissed = get_user_meta( get_current_user_id(), 'kirki_fa_nag_dismissed', true );
		if ( true === $dismissed || 1 === $dismissed || '1' === $dismissed ) {
			return;
		}

		?>
		<div class="notice notice-info is-dismissible">
			<p>
				<?php esc_html_e( 'Your theme uses a Font Awesome field for icons. To avoid issues with missing icons on your frontend we recommend you install the official Font Awesome plugin.', 'kirki' ); ?>
			</p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=font-awesome&TB_iframe=true&width=600&height=550' ) ); ?>"><?php esc_html_e( 'Install Plugin', 'kirki' ); ?></a>
				<a class="button button-secondary" href="<?php echo esc_url( wp_nonce_url( admin_url( '?dismiss-nag=font-awesome-kirki' ), 'kirki-dismiss-nag', 'nonce' ) ); ?>"><?php esc_html_e( 'Don\'t show this again', 'kirki' ); ?></a>
			</p>
		</div>
		<?php
	}
	public function dismiss_nag() {
		if ( isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'kirki-dismiss-nag' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( get_current_user_id() && isset( $_GET['dismiss-nag'] ) && 'font-awesome-kirki' === $_GET['dismiss-nag'] ) {
				update_user_meta( get_current_user_id(), 'kirki_fa_nag_dismissed', true );
			}
		}
	}
	protected static function maybe_show_fontawesome_nag( $args ) {

		// If we already know we want it, skip check.
		if ( self::$show_fa_nag ) {
			return;
		}

		// Check if the field is fontawesome.
		if ( isset( $args['type'] ) && in_array( $args['type'], array( 'fontawesome', 'kirki-fontawesome' ), true ) ) {

			// Skip check if theme has disabled FA enqueueing via a filter.
			if ( ! apply_filters( 'kirki_load_fontawesome', true ) ) {
				return;
			}

			// If we got this far, we need to show the nag.
			self::$show_fa_nag = true;
		}
	}
}
