<?php
/**
 * Main bbPress Genesis Extend class, this does the heavy lifting
 *
 * @package  bbPressGenesisExtend
 * @since    0.8.0
 */

if ( !class_exists( 'BBP_Genesis' ) ) :

class BBP_Genesis {

	/** Functions *************************************************************/

	/**
	 * The main bbPress Genesis loader
	 *
	 * @access public
	 * @since 0.8.0
	 */
	public function __construct() {
		$this->setup_actions();
	}

	/**
	 * Setup the Genesis actions
	 *
	 * @access private
	 * @since 0.8.0
	 */
	private function setup_actions() {

		// Register forum sidebar if needed
		$this->register_genesis_forum_sidebar();

		// We hook into 'genesis_before' because it is the most reliable hook
		// available to bbPress in the Genesis page load process.
		add_action( 'genesis_before',     array( $this, 'genesis_post_actions'        ) );
		add_action( 'genesis_before',     array( $this, 'check_genesis_forum_sidebar' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'front_styles'                ) );
		
		// Configure which Genesis layout to apply
		add_filter( 'genesis_pre_get_option_site_layout', array( $this, 'genesis_layout' ) );
		
		// Add Layout and SEO options to Forums
		add_post_type_support( bbp_get_forum_post_type(), 'genesis-layouts' );
		add_post_type_support( bbp_get_forum_post_type(), 'genesis-seo'     );

	}

	/**
	 * Tweak problematic Genesis post actions
	 *
	 * @access public
	 * @since 0.8.0
	 */
	public function genesis_post_actions() {

		/**
		 * If the current theme is a child theme of Genesis that also includes
		 * the template files bbPress needs, we can leave things how they are.
		 */		
		if ( is_bbpress() ) {

			/** Remove Actions ************************************************/

			/**
			 * Remove genesis breadcrumbs
			 *
			 * bbPress packs its own breadcrumbs, so we don't need the G version.
			 */
			remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );

			/**
			 * Remove post info & meta
			 * 
			 * If you moved the info/meta from their default locations, you are
			 * on your own.
			 */
			remove_action( 'genesis_before_post_content', 'genesis_post_info'     );
			remove_action( 'genesis_after_post_content',  'genesis_post_meta'     );
			remove_action( 'genesis_entry_header',        'genesis_post_info', 12 );
			remove_action( 'genesis_entry_footer',        'genesis_post_meta'     );

			/**
			 * Remove Genesis post image and content
			 *
			 * bbPress heavily relies on the_content() so if Genesis is
			 * modifying it unexpectedly, we need to un-unexpect it.
			 */
			remove_action( 'genesis_post_content',  'genesis_do_post_image'     );
			remove_action( 'genesis_post_content',  'genesis_do_post_content'   );
			remove_action( 'genesis_entry_content', 'genesis_do_post_image',  8 );
			remove_action( 'genesis_entry_content', 'genesis_do_post_content'   );

			/**
			 * Remove authorbox
			 * 
			 * In some odd cases the Genesis authorbox could appear
			 */
			remove_action( 'genesis_after_post',   'genesis_do_author_box_single' );
			remove_action( 'genesis_entry_footer', 'genesis_do_author_box_single' );

			/**
			 * Remove the navigation
			 *
			 * Make sure the Genesis navigation doesn't try to show after the loop.
			 */
			remove_action( 'genesis_after_endwhile', 'genesis_posts_nav' );

			/**
			 * Remove Genesis profile fields
			 * 
			 * In some use cases the Genesis fields were showing (incorrectly)
			 * on the bbPress profile edit pages, so we remove them just in case.
			 */
			if ( bbp_is_single_user_edit() ) {
				remove_action( 'show_user_profile', 'genesis_user_options_fields' );
				remove_action( 'show_user_profile', 'genesis_user_layout_fields'  );
				remove_action( 'show_user_profile', 'genesis_user_seo_fields'     );
				remove_action( 'show_user_profile', 'genesis_user_archive_fields' );
			}
			
			/** Add Actions ***************************************************/

			/**
			 * Re-add the_content back
			 *
			 * bbPress doesn't play nice with the Genesis formatted content, so
			 * we remove it above and reapply the normal version bbPress expects.
			 */
			add_action( 'genesis_post_content',  'the_content' );
			add_action( 'genesis_entry_content', 'the_content' );

			/** Filters *******************************************************/	
			
			/**
			 * Remove forum/topic descriptions
			 *
			 * Many people, myself included, are not a fan of the bbPress
			 * descriptions, e.g. "This forum contains 2 topics and 4 replies".
			 * So we provided an simple option in the settings to remove them.
			 */
			if ( genesis_get_option( 'bbp_forum_desc' ) ) {
				add_filter( 'bbp_get_single_forum_description', '__return_false' );
				add_filter( 'bbp_get_single_topic_description', '__return_false' );
			}
		}
	}

	/**
	 * Load optional CSS
	 *
	 * This has been deprecated and will not run if you are running > 0.8.4.
	 *
	 * @access public
	 * @since 0.8.0
	 */
	
	public function front_styles() {

		if ( get_option( 'bbpge_version') == false ) {
			if ( apply_filters( 'bbpge_css', true ) ) {
				wp_enqueue_style( 'bbpress-genesis-extend', plugins_url('style.css', __FILE__), array(), null, 'all' );
			}	
		}
	}

	/**
	 * Register forum specific sidebar if enabled
	 *
	 * @access public
	 * @since 0.8.0
	 */
	public function register_genesis_forum_sidebar() {

		if ( genesis_get_option( 'bbp_forum_sidebar' ) ) {
			genesis_register_sidebar( array( 
				'id'          => 'sidebar-genesis-bbpress', 
				'name'        => __( 'Forum Sidebar', 'bbpress-genesis-extend' ), 
				'description' => __( 'This is the primary sidebar used on the forums.', 'bbpress-genesis-extend' )
				) 
			);
		}
	}
	
	/**
	 * Setup forum specific sidebar on bbPress pages if enabled
	 *
	 * @access public
	 * @since 0.8
	 */
	public function check_genesis_forum_sidebar() {
		
		if ( is_bbpress() && genesis_get_option( 'bbp_forum_sidebar' ) ) {

			// Remove the default Genesis sidebar
			remove_action( 'genesis_sidebar', 'genesis_do_sidebar'     );
			
			// If Genesis Simple Sidebar plugin is in place, nuke it
			remove_action( 'genesis_sidebar', 'ss_do_sidebar'          );

			// Genesis Connect WooCommerce sidebar
			remove_action( 'genesis_sidebar', 'gencwooc_ss_do_sidebar' );
			
			// Load up the Genisis-bbPress sidebar
			add_action( 'genesis_sidebar', array( $this, 'load_genesis_forum_sidebar' ) );
		}	
	}
	
	/**
	 * Loads the forum specific sidebar
	 *
	 * @access public
	 * @since 0.8.0
	 */
	public function load_genesis_forum_sidebar() {
	
		// Throw up placeholder content if the sidebar is active but empty
		if ( ! dynamic_sidebar( 'sidebar-genesis-bbpress' ) ) {
			echo '<div class="widget widget_text"><div class="widget-wrap">';
				echo '<h4 class="widgettitle">';
					__( 'Forum Sidebar Widget Area', 'bbpress-genesis-extend' );
				echo '</h4>';
				echo '<div class="textwidget"><p>';
					printf( __( 'This is the Forum Sidebar Widget Area. You can add content to this area by visiting your <a href="%s">Widgets Panel</a> and adding new widgets to this area.', 'bbpress-genesis-extend' ), admin_url( 'widgets.php' ) );
				echo '</p></div>';
			echo '</div></div>';
		}
	}
	
	/**
	 * Genesis bbPress layout control
	 * 
	 * If you set a specific layout for a forum, that will be used for that forum
	 * and it's topics. If you set one in the Genesis-bbPress setting, that gets
	 * checked next. Otherwise bbPress will display itself in Genesis default layout.
	 *
	 * @access public
	 * @since 0.8.0
	 * @param string $layout
	 * @return bool layout to use
	 */
	public function genesis_layout( $layout ) {

		// Bail if no bbPress
		if ( !is_bbpress() )
			return $layout;	

		// Set some defaults
		$forum_id = bbp_get_forum_id();
		// For some reason, if we use the cached version, weird things seem to happen.
		// This needs more investigation, for now we pass false as a work around.
		$retval   = genesis_get_option( 'site_layout', null, false ); 
		$parent   = false;

		// Check and see if a layout has been set for the parent forum
		if ( !empty( $forum_id ) ) {
			$parent = esc_attr( get_post_meta( $forum_id, '_genesis_layout' , true ) );

			if ( !empty( $parent ) ) {
				return apply_filters( 'bbp_genesis_layout', $parent );
			}
		}
			
		// Second, see if a layout has been defined in the bbPress Genesis settings
		if ( empty( $parent ) || ( genesis_get_option( 'bbp_forum_layout' ) !== 'genesis-default' ) ) {
			$retval = genesis_get_option( 'bbp_forum_layout' );
		}

		// Filter the return value
		return apply_filters( 'bbp_genesis_layout', $retval, $forum_id, $parent );
	}
	
}
endif;

/**
 * Loads Genesis helper inside bbPress global class
 *
 * @since 0.8.0
 */
function bbpge_setup() {
	// Instantiate Genesis for bbPress
	new BBP_Genesis();
}