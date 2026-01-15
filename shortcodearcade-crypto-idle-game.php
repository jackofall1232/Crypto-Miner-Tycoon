<?php
/**
 * Plugin Name: Shortcode Arcade Crypto Idle Game
 * Plugin URI: https://github.com/jackofall1232/shortcodearcade-crypto-idle-game
 * Description: A crypto-themed idle clicker game with Elo-balanced progression, prestige mechanics, and optional leaderboards. Use the [crypto_miner] shortcode to display the game.
 * Version: 0.4.3
 * Author: Shortcode Arcade
 * Author URI: https://shortcodearcade.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: shortcodearcade-crypto-idle-game
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SACIG_VERSION', '0.4.3');
define('SACIG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SACIG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SACIG_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class SACIG_Bootstrap {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize plugin
     */
    private function init() {
        // Load required files
        $this->load_dependencies();
        
        // Initialize components
        $this->init_components();
        
        // Activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once SACIG_PLUGIN_DIR . 'includes/class-miner-shortcode.php';
        require_once SACIG_PLUGIN_DIR . 'includes/class-cmt-admin.php';
        require_once SACIG_PLUGIN_DIR . 'includes/class-cmt-cloud-save.php';
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        // Initialize shortcode handler
        new SACIG_Miner_Shortcode();

        // Initialize admin (only in admin area)
        if (is_admin()) {
            new SACIG_Admin();
        }

        // Initialize cloud save REST API
        new SACIG_Cloud_Save();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database table if cloud saves are enabled
        $this->maybe_create_table();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Maybe create database table for cloud saves
     */
    private function maybe_create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sacig_saves';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            user_id bigint(20) UNSIGNED NOT NULL,
            save_data longtext NOT NULL,
            base_click_power decimal(20,6) DEFAULT 1,
            base_passive_income decimal(20,6) DEFAULT 0,
            prestige_level int DEFAULT 0,
            total_satoshis decimal(30,6) DEFAULT 0,
            rank_score decimal(30,6) DEFAULT 0,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id),
            KEY rank_score (rank_score DESC),
            KEY last_updated (last_updated)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Initialize plugin
function sacig_bootstrap() {
    return SACIG_Bootstrap::get_instance();
}

// Start the plugin
sacig_bootstrap();
