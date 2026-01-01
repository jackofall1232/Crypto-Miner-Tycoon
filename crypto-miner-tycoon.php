<?php
/**
 * Plugin Name: Crypto Miner Tycoon
 * Plugin URI: https://cryptonerd.com
 * Description: An engaging crypto-themed idle clicker game with Elo-balanced progression. Use shortcode [crypto_miner_tycoon] to display the game.
 * Version: 1.0.0
 * Author: Joe
 * Author URI: https://cryptonerd.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: crypto-miner-tycoon
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CMT_VERSION', '1.0.0');
define('CMT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CMT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CMT_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class Crypto_Miner_Tycoon {
    
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
        // Load shortcode class
        require_once CMT_PLUGIN_DIR . 'includes/class-miner-shortcode.php';
        
        // Initialize shortcode
        new CMT_Miner_Shortcode();
        
        // Activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
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
}

// Initialize plugin
function crypto_miner_tycoon() {
    return Crypto_Miner_Tycoon::get_instance();
}

// Start the plugin
crypto_miner_tycoon();
