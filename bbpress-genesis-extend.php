<?php
/**
 * Main bbPress Genesis Extend class, this does the heavy lifting
 *
 * @package  bbPressGenesisExtend
 * @since    0.8
 */

if ( !class_exists( 'BBP_Genesis' ) ) :

// If class doesn't exist (bbP 2.1+), let's roll
class BBP_Genesis {

	/** Functions *************************************************************/

	/**
	 * The main bbPress Genesis loader
	 *
	 * @since 0.8
	 */
	public function __construct() {
		$this->setup_actions();
	}

	/**
	 * Setup the Genesis actions
	 *
	 * @since 0.8
	 */
	private function setup_actions() {

		// Register forum sidebar if needed
		$this->register_genesis_forum_sidebar();

		// We hook into 'genesis_before' because it is the most reliable hook
		// available to bbPress in the Genesis page load process.
		add_action( 'genesis_before',           array( $this, 'genesis_post_actions'        ) );
		add_action( 'genesis_before',           array( $this, 'check_genesis_forum_sidebar' ) );
		add_action( 'wp_enqueue_scripts',		array( $this, 'front_styles'				) );
		
		// Configure which Genesis layout to apply
		add_filter( 'genesis_pre_get_option_site_layout', array( $this, 'genesis_layout'    ) );
		
		// Add Layout and SEO options to Forums
		add_post_type_support( 'forum', 'genesis-layouts' );
		add_post_type_support( 'forum', 'genesis-seo'     );

	}

	/**
	 * Tweak problematic Genesis post actions
	 *
	 * @access private
	 * @since 0.8
	 */
	public function genesis_post_actions() {

		/**
		 * If the current theme is a child theme of Genesis that also includes
		 * the template files bbPress needs, we can leave things how they are.
		 */		
		if ( is_bbpress() ) {

			/** Remove Actions ************************************************/

			// Remove genesis breadcrumbs
			remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );

			/**
			 * Remove post info & meta
			 * 
			 * If you moved the info/meta from their default locations, you are
			 * on your own.
			 */
			remove_action( 'genesis_before_post_content', 'genesis_post_info' );
			remove_action( 'genesis_after_post_content',  'genesis_post_meta' );

			/**
			 * Remove Genesis post image and content
			 *
			 * bbPress heavily relies on the_content() so if Genesis is
			 * modifying it unexpectedly, we need to un-unexpect it.
			 */
			remove_action( 'genesis_post_content', 'genesis_do_post_image'   );
			remove_action( 'genesis_post_content', 'genesis_do_post_content' );

			/**
			 * Remove authorbox
			 * 
			 * In some odd cases the Genesis authorbox could appear
			 */
			remove_action( 'genesis_after_post', 'genesis_do_author_box_single' );

			// Remove the navigation after the post loop
			remove_action( 'genesis_after_endwhile', 'genesis_posts_nav' );
			
			// Remove post title from profile pages (as they don't work)
			if ( bbp_is_single_user() || bbp_is_single_user_edit() || bbp_is_user_home() ) {
				remove_action( 'genesis_post_title', 'genesis_do_post_title' );
			}

			/** Add Actions ***************************************************/

			// Re add 'the_content' back onto 'genesis_post_content'
			add_action( 'genesis_post_content', 'the_content' );	
			
		}
	}

	/**
	 * Load optional CSS
	 *
	 * @since 0.8
	 */
	
	public function front_styles() {

		if ( apply_filters( 'bbpge_css', true ) ) {
		    wp_enqueue_style( 'bbpress-genesis-extend', plugins_url('style.css', __FILE__), array(), null, 'all' );
		}

	}

	
	/**
	 * Register forum specific sidebar if enabled
	 *
	 * @since 0.8
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
	 * @since 0.8
	 */
	public function check_genesis_forum_sidebar() {
		
		if ( is_bbpress() && genesis_get_option( 'bbp_forum_sidebar' ) ) {

			// Remove the default Genesis sidebar
			remove_action( 'genesis_sidebar', 'genesis_do_sidebar' );
			
			// If Genesis Simple Sidebar plugin is in place, nuke it
			remove_action( 'genesis_sidebar', 'ss_do_sidebar'      );
			
			// Load up the Genisis-bbPress sidebar
			add_action( 'genesis_sidebar', array( $this, 'load_genesis_forum_sidebar' ) );
		}	

	}
	
	/**
	 * Loads the forum specific sidebar
	 *
	 * @since 0.8
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
	 * If you set a specific layout for a forum, that will be used for that forum and it's topics.
	 * If you set one in the Genesis-bbPress setting, that gets checked next.
	 * Otherwise bbPress will display itself in Genesis default layout.
	 *
	 * @param int $forum_id
	 * @return bool layout to use
	 */
	public function genesis_layout( $forum_id = 0 ) {

		// Bail if no bbPress
		if ( !is_bbpress() )
			return;	

		// Set some defaults
		$forum_id = bbp_get_forum_id( $forum_id );
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
 * @since 0.8
 */
function bbpge_setup() {
	// Instantiate Genesis for bbPress
	new BBP_Genesis();
}
