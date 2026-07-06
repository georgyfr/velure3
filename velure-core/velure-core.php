<?php
/**
 * Plugin Name: Velure Core
 * Plugin URI:  https://velure.paris
 * Description: Personnalisation a 100% de la page d'accueil du theme Velure3. Sections, ordre, styles — tout est configurable depuis l'admin.
 * Version:     1.0.0
 * Author:      Velure
 * Author URI:  https://velure.paris
 * License:     GPL-2.0+
 * Text Domain: velure-core
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package VelureCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'VELURE_CORE_VERSION', '1.0.0' );
define( 'VELURE_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'VELURE_CORE_URI', plugin_dir_url( __FILE__ ) );
define( 'VELURE_CORE_FILE', __FILE__ );

/* ═══════════════════════════════════════════════
   BOOTSTRAP
   ═══════════════════════════════════════════════ */
final class Velure_Core {

        private static $instance = null;

        public static function instance() {
                if ( null === self::$instance ) {
                        self::$instance = new self();
                }
                return self::$instance;
        }

        private function __construct() {
                $this->includes();
                $this->hooks();
        }

        private function includes() {
                require_once VELURE_CORE_DIR . 'includes/cpt-testimonial.php';
                require_once VELURE_CORE_DIR . 'includes/acf-page.php';
                require_once VELURE_CORE_DIR . 'includes/acf-fields.php';
                require_once VELURE_CORE_DIR . 'includes/data-helpers.php';
                require_once VELURE_CORE_DIR . 'includes/admin-notices.php';
        }

        private function hooks() {
                add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
                add_action( 'plugins_loaded', array( $this, 'signal_loaded' ), 1 );
                add_filter( 'plugin_action_links_' . plugin_basename( VELURE_CORE_FILE ), array( $this, 'settings_link' ) );
        }

        public function signal_loaded() {
                do_action( 'velure_core_loaded' );
        }

        public function load_textdomain() {
                load_plugin_textdomain( 'velure-core', false, dirname( plugin_basename( VELURE_CORE_FILE ) ) . '/languages' );
        }

        public function settings_link( $links ) {
                $url = admin_url( 'themes.php?page=velure-front-page' );
                $settings = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Page d\'accueil', 'velure-core' ) . '</a>';
                array_unshift( $links, $settings );
                return $links;
        }
}

Velure_Core::instance();