<?php
/*
Plugin Name: BuddyPress Skeleton Component
Plugin URI: http://example.org/my/awesome/bp/component
Description: This BuddyPress component is the greatest thing since sliced bread.
Version: 1.6.2
Revision Date: MMMM DD, YYYY
Requires at least: What WP version, what BuddyPress version? ( Example: WP 3.2.1, BuddyPress 1.5 )
Tested up to: What WP version, what BuddyPress version?
License: (Example: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html)
Author: Dr. Jan Itor
Author URI: http://example.org/some/cool/developer
Network: true
*/

/*************************************************************************************************************
 --- SKELETON COMPONENT V1.6.2 ---

 Contributors: apeatling, jeffsayre, boonebgorges

 This is a bare-bones component that should provide a good starting block to building your own custom BuddyPress
 component.

 It includes some of the functions that will make it easy to get your component registering activity stream
 items, posting notifications, setting up widgets, adding AJAX functionality and also structuring your
 component in a standardized way.

 It is by no means the letter of the law. You can go about writing your component in any style you like, that's
 one of the best (and worst!) features of a PHP based platform.

 I would recommend reading some of the comments littered throughout, as they will provide insight into how
 things tick within BuddyPress.

 You should replace all references to the word 'example' with something more suitable for your component.

 IMPORTANT: DO NOT configure your component so that it has to run in the /plugins/buddypress/ directory. If you
 do this, whenever the user auto-upgrades BuddyPress - your custom component will be deleted automatically. Design
 your component to run in the /wp-content/plugins/ directory
 *************************************************************************************************************/

// Define a constant that can be checked to see if the component is installed or not.
define( 'BP_EXAMPLE_IS_INSTALLED', 1 );

// Define a constant that will hold the current version number of the component
// This can be useful if you need to run update scripts or do compatibility checks in the future
define( 'BP_EXAMPLE_VERSION', '1.6.2' );

// Define a constant that we can use to construct file paths throughout the component
define( 'BP_EXAMPLE_PLUGIN_DIR', dirname( __FILE__ ) );

/* Define a constant that will hold the database version number that can be used for upgrading the DB
 *
 * NOTE: When table defintions change and you need to upgrade,
 * make sure that you increment this constant so that it runs the install function again.
 *
 * Also, if you have errors when testing the component for the first time, make sure that you check to
 * see if the table(s) got created. If not, you'll most likely need to increment this constant as
 * BP_EXAMPLE_DB_VERSION was written to the wp_usermeta table and the install function will not be
 * triggered again unless you increment the version to a number higher than stored in the meta data.
 */
define ( 'BP_EXAMPLE_DB_VERSION', '1' );


class Skeleton {
	/**
	 * Instance of this class.
	 *
	 * @package BuddyPress Skeleton Component
	 * @since    1.?.?
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin
	 * 
	 * @package BuddyPress Skeleton Component
	 * @since 1.X.X
	 */
	private function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_hooks();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @package BuddyPress Skeleton Component
	 * @since 1.X.X
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function start() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function includes() {
		require( $this->includes_dir . 'bp-example-loader.php' );
	}

	/**
	 * Sets some globals for the plugin
	 * 
	 * @package BuddyPress Skeleton Component
	 * @since 1.X.X
	 */
	private function setup_globals() {
		/** BuddyPress Skeleton plugin globals ********************************************/
		$this->version       = '1.X.X';
		$this->domain        = 'bp-example';
		$this->file          = __FILE__;
		$this->basename      = plugin_basename( $this->file );
		$this->plugin_dir    = plugin_dir_path( $this->file );
		$this->plugin_url    = plugin_dir_url( $this->file );
		$this->lang_dir      = trailingslashit( $this->plugin_dir . 'languages' );
		$this->includes_dir  = trailingslashit( $this->plugin_dir . 'includes' );
		$this->includes_url  = trailingslashit( $this->plugin_url . 'includes' );
		$this->plugin_js     = trailingslashit( $this->includes_url . 'js' );
		$this->plugin_css    = trailingslashit( $this->includes_url . 'css' );

		// Utility
		$this->debug = defined( 'BP_SKELETON_DEBUG' ) && BP_SKELETON_DEBUG ? true : false;
	}

	/**
	 * Sets the key hooks to add an action or a filter to
	 * 
	 * @package BuddyPress Skeleton Component
	 * @since 1.X.X
	 */
	private function setup_hooks() {
		//Load the component
		add_action( 'bp_loaded', 'bp_example_load_core_component' );

		add_filter( 'bp_do_register_theme_directory', '__return_true' );

		add_action( 'bp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		if ( $this->debug ) {

			if ( ! defined( 'WP_DEBUG' ) && ! WP_DEBUG )
				define( 'WP_DEBUG', true );

			add_action( 'wp_footer', array( $this, 'debug' ) );
		}
	}

	public function enqueue_scripts() {
		if ( ! bp_is_current_component( 'example' ) )
			return;

		wp_enqueue_script( 'bp-example-js', $this->includes_url . 'js/general.js', array( 'jquery' ), $this->version, true );
	}

	public function debug() {
		$to_dump = apply_filters( 'buddypress_skeleton_component_debug', $this );
		?>
		<div id="buddypress-skeleton-component-debug-tool">
			<pre><?php var_dump( $to_dump ); ?></pre>
		</div>
		<?php
	}
}

// BuddyPress is loaded and initialized, let's start !
function buddypress_skeleton_component() {
	$bp = buddypress();

	if ( empty( $bp->extend ) ) {
		$bp->extend = new StdClass();
	}

	/* Setup your plugin globals */
	$bp->extend->skeleton = Skeleton::start();
}
add_action( 'bp_include', 'buddypress_skeleton_component' );

/* Only load the component if BuddyPress is loaded and initialized. */
/*function bp_example_init() {
	// Because our loader file uses BP_Component, it requires BP 1.5 or greater.
	if ( version_compare( BP_VERSION, '1.3', '>' ) )
		require( BP_EXAMPLE_PLUGIN_DIR . '/includes/bp-example-loader.php' );
}
add_action( 'bp_include', 'bp_example_init' );

/* Put setup procedures to be run when the plugin is activated in the following function */
/*function bp_example_activate() {

}
register_activation_hook( __FILE__, 'bp_example_activate' );

/* On deacativation, clean up anything your component has added. */
/*function bp_example_deactivate() {
	/* You might want to delete any options or tables that your component created. */
/*}
register_deactivation_hook( __FILE__, 'bp_example_deactivate' );*/
?>
