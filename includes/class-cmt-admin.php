<?php
/**
 * Admin Settings Class
 * 
 * Handles the admin settings page for Crypto Miner Tycoon (Free Version)
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
        
        add_settings_section(
            'cmt_main_section',
            'Game Settings',
            array($this, 'render_section_description'),
            'crypto-miner-tycoon'
        );
        
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
    
    public function sanitize_checkbox($input) {
        return (bool) $input;
    }
    
    public function sanitize_leaderboard_limit($input) {
        $value = intval($input);
        return max(5, min(100, $value));
    }
    
    public function render_section_description() {
        echo '<p>Configure cloud saves and leaderboard features for Crypto Miner Tycoon.</p>';
    }
    
    public function render_cloud_saves_field() {
        $value = get_option('cmt_enable_cloud_saves', false);
        ?>
        <label>
            <input type="checkbox" name="cmt_enable_cloud_saves" value="1" <?php checked($value, true); ?>>
            Save game progress to WordPress user accounts
        </label>
        <?php
    }
    
    public function render_leaderboard_field() {
        $cloud_enabled = get_option('cmt_enable_cloud_saves', false);
        $value = get_option('cmt_enable_leaderboard', false);
        ?>
        <label>
            <input type="checkbox" name="cmt_enable_leaderboard" value="1"
                <?php checked($value, true); ?>
                <?php disabled(!$cloud_enabled); ?>>
            Display leaderboard on your site
        </label>
        <?php
    }
    
    public function render_leaderboard_limit_field() {
        $value = get_option('cmt_leaderboard_limit', 10);
        ?>
        <input type="number" name="cmt_leaderboard_limit"
               value="<?php echo esc_attr($value); ?>"
               min="5" max="100">
        <?php
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $active_tab = isset($_GET['tab'])
            ? sanitize_text_field(wp_unslash($_GET['tab']))
            : 'general';

        if (
            isset($_GET['settings-updated'], $_GET['_wpnonce']) &&
            wp_verify_nonce(
                sanitize_text_field(wp_unslash($_GET['_wpnonce'])),
                'cmt_settings_nonce'
            )
        ) {
            if (get_option('cmt_enable_cloud_saves')) {
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

            <?php wp_nonce_field('cmt_settings_nonce'); ?>

            <nav class="nav-tab-wrapper">
                <a href="?page=crypto-miner-tycoon&tab=general"
                   class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                   General
                </a>
                <a href="?page=crypto-miner-tycoon&tab=branding"
                   class="nav-tab <?php echo $active_tab === 'branding' ? 'nav-tab-active' : ''; ?>">
                   Branding <span class="cmt-tab-badge">PRO</span>
                </a>
            </nav>

            <?php
            if ($active_tab === 'general') {
                $this->render_general_tab();
            } else {
                $this->render_branding_tab_locked();
            }
            ?>
        </div>
        <?php
    }
    
    private function render_general_tab() {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('cmt_settings_group');
            do_settings_sections('crypto-miner-tycoon');
            submit_button();
            ?>
        </form>

        <?php if (get_option('cmt_enable_cloud_saves')): ?>
            <div class="cmt-info-box">
                <?php
                global $wpdb;
                $table_name = esc_sql($wpdb->prefix . 'cmt_saves');
                $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
                ?>
                <p><strong>Total Saved Games:</strong> <?php echo esc_html($count); ?></p>
            </div>
        <?php endif;
    }
    
    private function render_branding_tab_locked() {
        echo '<p><strong>Upgrade to Pro to unlock this feature.</strong></p>';
    }
    
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
            KEY rank_score (rank_score),
            KEY last_updated (last_updated)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_crypto-miner-tycoon') {
            return;
        }
        
        wp_enqueue_style(
            'cmt-admin-css',
            CMT_PLUGIN_URL . 'assets/css/cmt-admin.css',
            array(),
            CMT_VERSION
        );
    }
}
