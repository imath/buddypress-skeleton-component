<?php

/********************************************************************************
 * Screen Functions
 *
 * Screen functions are the controllers of BuddyPress. They will execute when their
 * specific URL is caught. They will first save or manipulate data using business
 * functions, then pass on the user to a template file.
 */

class BuddyPress_Skeleton_Screens {

	/**
	 * The constructor
	 *
	 * @package BuddyPress Skeleton Component
	 * @subpackage Screens
	 * @since 1.X.X
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_filters();
		$this->setup_actions();
	}

	public static function manage_screens() {
		$bp = buddypress();

		if ( empty( $bp->extend->skeleton->screens ) ) {
			$bp->extend->skeleton->screens = new self;
		}

		return $bp->extend->skeleton->screens;
	}

	public function setup_globals() {
		$bp = buddypress();

		$this->template     = '';
		$this->template_dir = $bp->extend->skeleton->includes_dir . 'templates';
	}

	private function setup_filters() {
		if ( bp_is_current_component( 'example' ) ) {
			add_filter( 'bp_located_template',   array( $this, 'template_filter' ), 20, 2 );
			add_filter( 'bp_get_template_stack', array( $this, 'add_to_template_stack' ), 10, 1 );
		}
	}

	public function template_filter( $found_template = '', $templates = array() ) {
		$bp = buddypress();

		// Bail if theme has it's own template for content.
		if ( ! empty( $found_template ) )
			return $found_template;

		// Current theme do use theme compat, no need to carry on
		if ( $bp->theme_compat->use_with_current_theme )
			return false;

		// If we're here this means we're probably on the directory in 
		// a Theme that use it's own BuddyPress support. There's a good
		// chance as a BuddyPress directory needs a page that the template
		// loaded is the page.php, so what about filtering the content to 
		// display a message to help the user to build his template ?
		if ( bp_is_directory() )
			add_filter( 'the_content', array( $this, 'template_to_build' ) );

		return apply_filters( 'bp_example_load_template_filter', $found_template );
	}

	public function add_to_template_stack( $templates = array() ) {
		// Adding the plugin's provided template to the end of the stack
		// So that the theme can override it.
		return array_merge( $templates, array( buddypress()->extend->skeleton->includes_dir . 'templates' ) );
	}

	/**
	 * screen_one()
	 *
	 * Sets up and displays the screen output for the sub nav item "example/screen-one"
	 */
	public static function screen_one() {

		do_action( 'bp_example_screen_one' );

		self::load_template( 'example/screen-one', 'screen_one' );
	}

	/**
	 * screen_two()
	 *
	 * Sets up and displays the screen output for the sub nav item "example/screen-two"
	 */
	public static function screen_two() {

		do_action( 'bp_example_screen_two' );

		// We'll only use members/single/plugins
		self::load_template( '', 'screen_two' );
	}

	public static function load_template( $template = '', $screen = '' ) {
		$bp = buddypress();
		/****
		 * Displaying Content
		 */
		$bp->extend->skeleton->screens->template = $template;
		/****
		 * OPTION 1:
		 * You've got a few options for displaying content. Your first option is to bundle template files
		 * with your plugin that will be used to output content.
		 *
		 * In an earlier function bp_example_load_template_filter() we set up a filter on the core BP template
		 * loading function that will make it first look in the plugin directory for template files.
		 * If it doesn't find any matching templates it will look in the active theme directory.
		 *
		 * This example component comes bundled with a template for screen one, so we can load that
		 * template to display what we need. If you copied this template from the plugin into your theme
		 * then it would load that one instead. This allows users to override templates in their theme.
		 */
		if ( buddypress()->theme_compat->use_with_current_theme && ! empty( $template ) ) {
			add_filter( 'bp_get_template_part', array( __CLASS__, 'template_part' ), 10, 3 );
		} else {
			// You can only use this method for users profile pages
			if ( ! bp_is_directory() ) {
				/****
				 * OPTION 2:
				 * If your component is simple, and you just want to insert some HTML into the user's active theme
				 * then you can use the bundle plugin template.
				 *
				 * Or you can use this technique as a fallback if the theme does not support theme compat
				 *
				 * There are two actions you need to hook into. One for the title, and one for the content.
				 * The functions you hook these into should simply output the content you want to display on the
				 * page.
				 *
				 * The follow lines are commented out because we are not using this method for this screen.
				 * You'd want to remove the OPTION 1 parts above and uncomment these lines if you want to use
				 * this option instead.
				 *
				 * Generally, this method of adding content is preferred, as it makes your plugin
				 * work better with a wider variety of themes.
			 	 */
				$bp->extend->skeleton->screens->template = 'members/single/plugins';
				add_action( 'bp_template_title',   "bp_example_{$screen}_title"   );
				add_action( 'bp_template_content', "bp_example_{$screen}_content" );
			}
		}

		/* This is going to look in wp-content/plugins/[plugin-name]/includes/templates/ first */
		bp_core_load_template( apply_filters( "bp_example_template_{$screen}", $bp->extend->skeleton->screens->template ) );
	}

	public static function template_part( $templates, $slug, $name ) {
		if ( $slug != 'members/single/plugins' ) {
	        return $templates;
		}
	    return array( buddypress()->extend->skeleton->screens->template . '.php' );
	}

	private function setup_actions() {
		add_action( 'bp_screens', array( $this, 'directory_setup' ) );
		add_action( 'bp_setup_theme_compat', array( $this, 'use_theme_compat' ) );
	}

	/**
	 * If your component uses a top-level directory, this function will catch the requests and load
	 * the index page.
	 *
	 * @package BuddyPress_Template_Pack
	 * @since 1.6
	 */
	public function directory_setup() {
		if ( bp_is_example_component() && !bp_current_action() && !bp_current_item() ) {
			// This wrapper function sets the $bp->is_directory flag to true, which help other
			// content to display content properly on your directory.
			bp_update_is_directory( true, 'example' );

			// Add an action so that plugins can add content or modify behavior
			do_action( 'bp_example_directory_setup' );

			self::load_template( 'example/index', 'directory' );
		}
	}

	public function use_theme_compat() {
		if ( ! bp_displayed_user_id() && bp_is_current_component( 'example' ) ) {

			add_action( 'bp_template_include_reset_dummy_post_data', array( $this, 'directory_dummy_post' ) );
			add_filter( 'bp_replace_the_content',                    array( $this, 'directory_content'    ) );

		}
	}

	/** Directory *************************************************************/

	/**
	 * Update the global $post with directory data
	 *
	 * @package BuddyPress_Template_Pack
	 * @subpackage Screens
	 * @since 1.X.X
	 *
	 * @uses bp_theme_compat_reset_post() to reset the post data
	 */
	public function directory_dummy_post() {

		bp_theme_compat_reset_post( array(
			'ID'             => 0,
			'post_title'     => __( 'High Fives Directory', 'bp-example' ),
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => 'example_directory',
			'post_status'    => 'publish',
			'is_page'        => true,
			'comment_status' => 'closed'
		) );
	}

	/**
	 * Filter the_content with the Example directory template part
	 *
	 * @package BuddyPress_Template_Pack
	 * @subpackage Screens
	 * @since 1.X.X
	 *
	 * @uses bp_buffer_template_part()
	 */
	public function directory_content() {		
		bp_buffer_template_part( apply_filters( 'bp_example_directory_content', 'example/index' ) );
	}

	public function template_to_build( $content ) {
		if ( ! current_user_can( 'edit_theme_options' ) )
			return $content;

		$templates_folder = str_replace( WP_CONTENT_DIR . '/', '', buddypress()->extend->skeleton->includes_dir . 'templates/example' );

		$templates = array( 'index.php', 'example-loop.php' );

		$message  = '<p>' . __( 'Hi Buddy!', 'bp-example' ) . '</p>';
		$message .= '<p>' . __( 'You are using a theme that requires to build a specific template for this plugin.', 'bp-example' ) .'</p>' ;
		$message .= '<p>' . __( 'As BuddyPress Standalone themes are using very different markups, it is difficult for the plugin to display the best way.', 'bp-example' ) .'</p>' ;
		$message .= '<p>' . __( 'We advise you to contact the theme support so that he can help you to build the template for your theme.', 'bp-example' ) .'</p>' ;
		$message .= '<p>' . sprintf( __( 'The template of the plugin are localized in the %s folder', 'bp-example' ), '<strong>' . $templates_folder . '</strong>' ) . '</p>' ;
		$message .= '<p>' . __( 'In your theme, you need to create the folder <strong>example</strong> and copy in it these templates:', 'bp-example' ) . '</p>' ;

		$message .= '<ul>';
		foreach ( $templates as $template ) {
			$message .= '<li>' . $template . '</li>';
		}
		$message .= '</ul>';

		$message .= '<p>' . __( 'Once done, edit the markup to fit to your theme.', 'bp-example' ) . '</p>' ;

		return $message;
	}
	
}
add_action( 'bp_init', array( 'BuddyPress_Skeleton_Screens', 'manage_screens' ) );

/***
 * The second argument of each of the above add_action() calls is a function that will
 * display the corresponding information. The functions are presented below:
 */
function bp_example_screen_one_title() {
	_e( 'Screen One', 'bp-example' );
}

function bp_example_screen_one_content() {
	$bp = buddypress();

	$high_fives = bp_example_get_highfives_for_user( $bp->displayed_user->id );

	/**
	 * For security reasons, we MUST use the wp_nonce_url() function on any actions.
	 * This will stop naughty people from tricking users into performing actions without their
	 * knowledge or intent.
	 */
	$send_link = wp_nonce_url( $bp->displayed_user->domain . $bp->current_component . '/screen-one/send-h5', 'bp_example_send_high_five' );
?>
	<h4><?php _e( 'Welcome to Screen One', 'bp-example' ) ?></h4>
	<p><?php printf( __( 'Send %s a <a href="%s" title="Send high-five!">high-five!</a>', 'bp-example' ), $bp->displayed_user->fullname, $send_link ) ?></p>

	<?php if ( $high_fives ) : ?>
		<h4><?php _e( 'Received High Fives!', 'bp-example' ) ?></h4>

		<table id="high-fives">
			<?php foreach ( $high_fives as $user_id ) : ?>
			<tr>
				<td width="1%"><?php echo bp_core_fetch_avatar( array( 'item_id' => $user_id, 'width' => 25, 'height' => 25 ) ) ?></td>
				<td>&nbsp; <?php echo bp_core_get_userlink( $user_id ) ?></td>
 			</tr>
			<?php endforeach; ?>
		</table>
	<?php endif; ?>
<?php
}


function bp_example_screen_two_title() {
	_e( 'Screen Two', 'bp-example' );
}

function bp_example_screen_two_content() {
	$bp = buddypress(); ?>

	<h4><?php _e( 'Welcome to Screen Two', 'bp-example' ) ?></h4>

	<?php
		$accept_link = '<a href="' . wp_nonce_url( $bp->loggedin_user->domain . $bp->example->slug . '/screen-two/accept', 'bp_example_accept_terms' ) . '">' . __( 'Accept', 'bp-example' ) . '</a>';
		$reject_link = '<a href="' . wp_nonce_url( $bp->loggedin_user->domain . $bp->example->slug . '/screen-two/reject', 'bp_example_reject_terms' ) . '">' . __( 'Reject', 'bp-example' ) . '</a>';
	?>

	<p><?php printf( __( 'You must %s or %s the terms of use policy.', 'bp-example' ), $accept_link, $reject_link ) ?></p>
<?php
}

/**
 * The following screen functions are called when the Settings subpanel for this component is viewed
 */
function bp_example_screen_settings_menu() {
	global $bp, $current_user, $bp_settings_updated, $pass_error;

	if ( isset( $_POST['submit'] ) ) {
		/* Check the nonce */
		check_admin_referer('bp-example-admin');

		$bp_settings_updated = true;

		/**
		 * This is when the user has hit the save button on their settings.
		 * The best place to store these settings is in wp_usermeta.
		 */
		update_user_meta( $bp->loggedin_user->id, 'bp-example-option-one', attribute_escape( $_POST['bp-example-option-one'] ) );
	}

	add_action( 'bp_template_content_header', 'bp_example_screen_settings_menu_header' );
	add_action( 'bp_template_title', 'bp_example_screen_settings_menu_title' );
	add_action( 'bp_template_content', 'bp_example_screen_settings_menu_content' );

	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

	function bp_example_screen_settings_menu_header() {
		_e( 'Example Settings Header', 'bp-example' );
	}

	function bp_example_screen_settings_menu_title() {
		_e( 'Example Settings', 'bp-example' );
	}

	function bp_example_screen_settings_menu_content() {
		global $bp, $bp_settings_updated; ?>

		<?php if ( $bp_settings_updated ) { ?>
			<div id="message" class="updated fade">
				<p><?php _e( 'Changes Saved.', 'bp-example' ) ?></p>
			</div>
		<?php } ?>

		<form action="<?php echo $bp->loggedin_user->domain . 'settings/example-admin'; ?>" name="bp-example-admin-form" id="account-delete-form" class="bp-example-admin-form" method="post">

			<input type="checkbox" name="bp-example-option-one" id="bp-example-option-one" value="1"<?php if ( '1' == get_user_meta( $bp->loggedin_user->id, 'bp-example-option-one', true ) ) : ?> checked="checked"<?php endif; ?> /> <?php _e( 'Do you love clicking checkboxes?', 'bp-example' ); ?>
			<p class="submit">
				<input type="submit" value="<?php _e( 'Save Settings', 'bp-example' ) ?> &raquo;" id="submit" name="submit" />
			</p>

			<?php
			/* This is very important, don't leave it out. */
			wp_nonce_field( 'bp-example-admin' );
			?>

		</form>
	<?php
	}
