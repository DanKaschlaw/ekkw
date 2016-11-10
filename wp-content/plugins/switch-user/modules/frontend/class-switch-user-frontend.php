<?php

// If this file is called directly, abort.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Switch_User_Frontend
 * Class responsible to create the Front-end Stuff
 *
 * @package           Switch_User_Frontend
 * @since             2.0
 *
 */

if ( ! class_exists( 'Switch_User_Frontend' ) ) {

	class Switch_User_Frontend {

		/**
		 * The Core object
		 *
		 * @since    2.0
		 * @access   public
		 * @var      Switch_User    $core	The core class
		 */
		private $core;

		/**
		 * The Module Indentify
		 *
		 * @since    2.0
		 * @access   public
		 * @var      Switch_User    $core	The core class
		 */
		const MODULE_SLUG = "frontend";

		/**
		 * Define the core functionality of the plugin.
		 *
		 * @since    2.0
		 * @param    array		$core	The Core object
		 * @param    array		$tag	The Core Tag
		 */
		public function __construct( Switch_User $core ) {

			$this->core = $core;

		}

		/**
		 * Register all the hooks for this module
		 *
		 * @since    2.0
		 * @access   private
		 */
		private function define_hooks() {

			// Front-end Scripts
			$this->add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Render HTML
			$this->add_action( 'wp_footer', array( $this, 'render_html' ) );

			// AJAX
			$this->add_action( 'wp_ajax_switch_user_change_user', array( $this, 'change_user' ) );
			$this->add_action( 'wp_ajax_nopriv_switch_user_change_user', array( $this, 'change_user' ) );

		}

		/**
		 * Add a new action to the collection to be registered with WordPress.
		 *
		 * @since    2.0
		 * @see    Switch_User->add_action
		 */
		private function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {

			if ( $this->core != null ) {
				$this->core->add_action( $hook, $callback, $priority, $accepted_args );
			} else {
				if ( WP_DEBUG ) {
					trigger_error( __( 'Core was not passed in "Switch_User_Frontend".' ), E_USER_WARNING );
				}
			}

		}

		/**
		 * Add a new filter to the collection to be registered with WordPress.
		 *
		 * @since    2.0
		 * @see    Switch_User->add_filter
		 */
		private function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {

			if ( $this->core != null ) {
				$this->core->add_filter( $hook, $callback, $priority, $accepted_args );
			} else {
				if ( WP_DEBUG ) {
					trigger_error( __( 'Core was not passed in "Switch_User_Frontend".' ), E_USER_WARNING );
				}
			}

		}

		/**
		 * By safety, only works in WP_DEBUG or if already logged
		 *
		 * @since    2.0
		 */
		private function check_can_render() {
			if ( WP_DEBUG || is_user_logged_in() ) {
				return true;
			}

			return false;
		}

		/**
		 * Front-end scripts
		 *
		 * @since    2.0
		 */
		public function enqueue_scripts() {

			if ( $this->check_can_render() ) {

				wp_enqueue_style( SWITCH_USER_TEXTDOMAIN . '_css_main', plugins_url( 'css/main.css', __FILE__ ), array(), SWITCH_USER_SCRIPTS_VERSION, 'all' );
				wp_enqueue_script( SWITCH_USER_TEXTDOMAIN . '_js_main', plugins_url( 'js/main.js', __FILE__ ), array( 'jquery' ), SWITCH_USER_SCRIPTS_VERSION, true );
				
				$su_localize_string = array(
					'ajaxurl' 	=> admin_url( 'admin-ajax.php' ),
					'messages'	=> array(
						'change_success'	=> __( "Current user successfully changed.", SWITCH_USER_TEXTDOMAIN ),
						'change_error'		=> __( "Oops... error: please try again.", SWITCH_USER_TEXTDOMAIN ),
						'connection_error'	=> __( "There was a connection error, please try again.", SWITCH_USER_TEXTDOMAIN ),
					),
				);

				wp_localize_script( SWITCH_USER_TEXTDOMAIN . '_js_main', 'SU', $su_localize_string );

			}

		}

		/**
		 * Render the HTML for Switcher
		 *
		 * @since    2.0
		 */
		public function render_html() {

			if ( $this->check_can_render() ) {
				
				$users = get_users( array(
					'order_by' => 'login',
				) );

				if ( ! empty( $users ) ) : ?>

					<div id="su-wrapper">
						<span class="su-wrapper-toggle" title="<?php _e( 'Click to expand/contract', SWITCH_USER_TEXTDOMAIN ) ?>"></span>
						<h1><?php _e( 'Switch User:', SWITCH_USER_TEXTDOMAIN ) ?></h1>
						<hr>
						<ul>
							<?php
								$current_user_id = get_current_user_id();

								foreach ( $users as $user ) {
								    if ( $user->ID == $current_user_id ) {
								        echo '<li class="current-user" data-user-id="' . $user->ID . '" title="' . __( "You are logged as this user", SWITCH_USER_TEXTDOMAIN ) . '">' . $user->user_login . '</li>';
								    } else {
								        echo '<li class="js-su-user" data-user-id="' . $user->ID . '" title="' . __( "Click to login as this user", SWITCH_USER_TEXTDOMAIN ) . '">' . $user->user_login . '</li>';
								    }
								}
							?>
						</ul>
						<?php wp_nonce_field( SWITCH_USER_CHANGE_USER_NONCE, 'su-change-user-security' ); ?>
					</div>
				
				<?php endif;
			}

		}

		/**
		 * Change Logged User
		 *
		 * @since    2.0
		 */
		public function change_user() {

			// Checa a referência (wp_nonce)
			check_ajax_referer( SWITCH_USER_CHANGE_USER_NONCE, 'su_nonce' );

			$return = array(
				"status" => "error",
			);

			if ( $this->check_can_render() ) {

				if ( isset( $_POST['user_id'] ) && $_POST['user_id'] != '' ) {

					$user = get_user_by( 'ID', intval( $_POST['user_id'] ) );

					if ( $user ) {
					    wp_set_auth_cookie( intval( $_POST['user_id'] ) );
					    $return = array(
					    	"status" => "ok",
					    );
					}

				} else {

				    $return = array(
				    	"status" => "error",
				    	"msg" => __( "Invalid user ID.", SWITCH_USER_TEXTDOMAIN ),
				    );

				}

			} else {

			    $return = array(
			    	"status" => "error",
			    	"msg" => __( "You are not allowed to do that.", SWITCH_USER_TEXTDOMAIN ),
			    );

			}

			wp_send_json( $return );

		}

		/**
		 * Run the plugin.
		 *
		 * @since    2.0
		 */
		public function run() {

			define( 'SWITCH_USER_CHANGE_USER_NONCE', 'su-change-user-nonce' );

			$this->define_hooks();

		}

	}
}