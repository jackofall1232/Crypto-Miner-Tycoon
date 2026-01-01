<?php
/**
 * Admin Settings Class
 * 
 * Handles the admin settings page for Crypto Miner Tycoon
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class CMT_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_options_page(
            'Crypto Miner Tycoon Settings',
            'Crypto Miner Tycoon',
            'manage_options',
            'crypto-miner-tycoon',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // Register settings group
        register_setting('cmt_settings_group', 'cmt_enable_cloud_saves', array(
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => array($this, 'sanitize_checkbox')
        ));
        
        register_setting('cmt_settings_group', 'cmt_enable_leaderboard', array(
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => array($this, 'sanitize_checkbox')
        ));
        
        register_setting('cmt_settings_group', 'cmt_leaderboard_limit', array(
            'type' => 'integer',
            'default' => 10,
            'sanitize_callback' => array($this, 'sanitize_leaderboard_limit')
        ));
        
        // Add settings section
        add_settings_section(
            'cmt_main_section',
            'Game Settings',
            array($this, 'render_section_description'),
            'crypto-miner-tycoon'
        );
        
        // Add settings fields
        add_settings_field(
            'cmt_enable_cloud_saves',
            'Enable Cloud Saves',
            array($this, 'render_cloud_saves_field'),
            'crypto-miner-tycoon',
            'cmt_main_section'
        );
        
        add_settings_field(
            'cmt_enable_leaderboard',
            'Enable Leaderboard',
            array($this, 'render_leaderboard_field'),
            'crypto-miner-tycoon',
            'cmt_main_section'
        );
        
        add_settings_field(
            'cmt_leaderboard_limit',
            'Leaderboard Size',
            array($this, 'render_leaderboard_limit_field'),
            'crypto-miner-tycoon',
            'cmt_main_section'
        );
    }
    
    /**
     * Sanitize checkbox
     */
    public function sanitize_checkbox($input) {
        return (bool) $input;
    }
    
    /**
     * Sanitize leaderboard limit
     */
    public function sanitize_leaderboard_limit($input) {
        $value = intval($input);
        return max(5, min(100, $value)); // Between 5 and 100
    }
    
    /**
     * Render section description
     */
    public function render_section_description() {
        echo '<p>Configure cloud saves and leaderboard features for Crypto Miner Tycoon.</p>';
    }
    
    /**
     * Render cloud saves field
     */
    public function render_cloud_saves_field() {
        $value = get_option('cmt_enable_cloud_saves', false);
        ?>
        <label>
            <input type="checkbox" name="cmt_enable_cloud_saves" value="1" <?php checked($value, true); ?>>
            Save game progress to WordPress user accounts
        </label>
        <p class="description">
            <strong>Requires:</strong> Users must be logged in to play. Game saves will be stored in your WordPress database.
        </p>
        <?php
    }
    
    /**
     * Render leaderboard field
     */
    public function render_leaderboard_field() {
        $cloud_enabled = get_option('cmt_enable_cloud_saves', false);
        $value = get_option('cmt_enable_leaderboard', false);
        $disabled = !$cloud_enabled;
        ?>
        <label>
            <input type="checkbox" name="cmt_enable_leaderboard" value="1" 
                <?php checked($value, true); ?> 
                <?php disabled($disabled); ?>>
            Display leaderboard on your site
        </label>
        <p class="description">
            <?php if ($disabled): ?>
                <span class="cmt-warning">⚠️ Cloud Saves must be enabled first</span><br>
            <?php endif; ?>
            Use shortcode: <code>[crypto_miner_leaderboard]</code>
        </p>
        <?php
    }
    
    /**
     * Render leaderboard limit field
     */
    public function render_leaderboard_limit_field() {
        $value = get_option('cmt_leaderboard_limit', 10);
        ?>
        <input type="number" name="cmt_leaderboard_limit" value="<?php echo esc_attr($value); ?>" 
            min="5" max="100" step="1">
        <p class="description">Number of top players to display (5-100)</p>
        <?php
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Check if settings were saved
        if (isset($_GET['settings-updated'])) {
            // Check if database table needs to be created
            $cloud_enabled = get_option('cmt_enable_cloud_saves', false);
            if ($cloud_enabled) {
                $this->maybe_create_table();
            }
            
            add_settings_error(
                'cmt_messages',
                'cmt_message',
                'Settings Saved',
                'updated'
            );
        }
        
        settings_errors('cmt_messages');
        ?>
        <div class="wrap cmt-admin-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="cmt-admin-container">
                <div class="cmt-admin-main">
                    <form action="options.php" method="post">
                        <?php
                        settings_fields('cmt_settings_group');
                        do_settings_sections('crypto-miner-tycoon');
                        submit_button('Save Settings');
                        ?>
                    </form>
                    
                    <div class="cmt-info-box">
                        <h3>📋 Shortcodes</h3>
                        <p><strong>Game:</strong> <code>[crypto_miner_tycoon]</code></p>
                        <?php if (get_option('cmt_enable_leaderboard')): ?>
                            <p><strong>Leaderboard:</strong> <code>[crypto_miner_leaderboard]</code></p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (get_option('cmt_enable_cloud_saves')): ?>
                        <div class="cmt-info-box">
                            <h3>☁️ Cloud Saves Status</h3>
                            <?php
                            global $wpdb;
                            $table_name = $wpdb->prefix . 'cmt_saves';
                            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                            ?>
                            <p><strong>Total Saved Games:</strong> <?php echo esc_html($count); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="cmt-admin-sidebar">
                    <div class="cmt-sidebar-box">
                        <h3>ℹ️ About</h3>
                        <p><strong>Crypto Miner Tycoon</strong></p>
                        <p>Version: <?php echo esc_html(CMT_VERSION); ?></p>
                        <p>An idle clicker game with Elo-balanced progression.</p>
                    </div>
                    
                    <div class="cmt-sidebar-box">
                        <h3>📚 Documentation</h3>
                        <ul>
                            <li><strong>Local Saves:</strong> Uses browser localStorage (default)</li>
                            <li><strong>Cloud Saves:</strong> Requires user login, stores in WordPress DB</li>
                            <li><strong>Leaderboard:</strong> Shows top players with prestige-weighted scoring</li>
                        </ul>
                    </div>
                    
                    <div class="cmt-sidebar-box">
                        <h3>⚠️ Important Notes</h3>
                        <ul>
                            <li>Cloud saves require users to be logged in</li>
                            <li>Leaderboards require cloud saves to be enabled</li>
                            <li>All data is stored in your WordPress database</li>
                            <li>Player data is private to your site only</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Maybe create database table for cloud saves
     */
    private function maybe_create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cmt_saves';
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
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our settings page
        if ($hook !== 'settings_page_crypto-miner-tycoon') {
            return;
        }
        
        wp_enqueue_style(
            'cmt-admin-css',
            CMT_PLUGIN_URL . 'assets/css/cmt-admin.css',
            array(),
            CMT_VERSION
        );
        
        wp_enqueue_script(
            'cmt-admin-js',
            CMT_PLUGIN_URL . 'assets/js/cmt-admin.js',
            array('jquery'),
            CMT_VERSION,
            true
        );
    }
}
