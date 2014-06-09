<?php
/**
 * Main bbPress Genesis Extend settings class
 *
 * Registers a few bbPress-Genesis specific options on the Genesis Setting page.
 * 
 * @package  bbPressGenesisExtend
 * @since    0.8.0
 */
class bbpge_settings {
	
	/**
	 * construct ALL THE THINGS
	 *
	 * @since 0.8.0
	 */
	function __construct() {	
		// Option default values
		add_filter( 'genesis_theme_settings_defaults',  array( $this, 'options_defaults'      ) );
		
		// Saniztize options
		add_action( 'genesis_settings_sanitizer_init',  array( $this, 'sanitization_filters'  ) );

		// Register settings
		add_action( 'genesis_theme_settings_metaboxes', array( $this, 'register_settings_box' ) );
	}

	/**
	 * Set defaults
	 *
	 * @since 0.8.0
	 * @param array $defaults
	 * @return array new defaults
	 */
	function options_defaults( $defaults ) {
		$defaults['bbp_forum_sidebar'] = '';
		$defaults['bbp_forum_layout']  = 'genesis-default';
		$defaults['bbp_forum_desc']    = '';
		return $defaults;
	}

	/**
	 * Set sanitizations
	 *
	 * @since 0.8.0
	 */
	function sanitization_filters() {
		// bbp_forum_layout
		genesis_add_option_filter( 'no_html', GENESIS_SETTINGS_FIELD,  array( 'bbp_forum_layout'  ) );

		// bbp_forum_sidebar
		genesis_add_option_filter( 'one_zero', GENESIS_SETTINGS_FIELD, array( 'bbp_forum_sidebar' ) );
		
		// bbp_forum_desc
		genesis_add_option_filter( 'one_zero', GENESIS_SETTINGS_FIELD, array( 'bbp_forum_desc'    ) );
	}

	/**
	 * Register the settings metabox
	 *
	 * @since 0.8.0
	 * @param $_genesis_theme_settings_pagehook
	 */
	function register_settings_box( $_genesis_theme_settings_pagehook ) {
		add_meta_box( 'bbpress-genesis-options', 'bbPress', array( $this, 'settings_box' ), $_genesis_theme_settings_pagehook, 'main', 'high' );
	}

	/**
	 * Render the settings metabox
	 *
	 * @since 0.8.0
	 */
	function settings_box() {	
		?>
		<p>
			<label for="bbp_forum_layout"><?php _e( 'Forum Layout: ', 'bbpress-genesis-extend' ); ?></label>
			<select name="<?php echo GENESIS_SETTINGS_FIELD; ?>[bbp_forum_layout]" id="bbp_forum_layout">
				<option value="genesis-default" <?php selected( genesis_get_option( 'bbp_forum_layout' ), 'genesis-default' ); ?>><?php _e( 'Genesis default', 'bbpress-genesis-extend' ); ?></option> 
				<?php
				foreach ( genesis_get_layouts() as $id => $data ) {	
					echo '<option value="' . esc_attr( $id ) . '" ' . selected( genesis_get_option( 'bbp_forum_layout' ), esc_attr( $id ) ) . '>' . esc_attr( $data['label'] ) . '</option>';
				}
				?>
			</select>
		</p>
		<p>
			<input type="checkbox" id="bbp_forum_sidebar" name="<?php echo GENESIS_SETTINGS_FIELD; ?>[bbp_forum_sidebar]" value="1" <?php checked( genesis_get_option( 'bbp_forum_sidebar' ) ); ?> />
			<label for="bbp_forum_sidebar"><?php _e( 'Register a forum specific sidebar that will be used on all forum pages', 'bbpress-genesis-extend' ); ?></label>
		</p>
		<p>
			<input type="checkbox" id="bbp_forum_desc" name="<?php echo GENESIS_SETTINGS_FIELD; ?>[bbp_forum_desc]" value="1" <?php checked( genesis_get_option( 'bbp_forum_desc' ) ); ?> />
			<label for="bbp_forum_desc"><?php _e( 'Remove forum and topic descriptions. E.g. "This forum contains [&hellip;]" notices.', 'bbpress-genesis-extend' ); ?></label>
		</p>
		<?php
	}
}

new bbpge_settings();