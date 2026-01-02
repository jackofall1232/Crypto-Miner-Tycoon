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
        
        // Get active tab
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        
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
            
            <!-- Tab Navigation -->
            <nav class="nav-tab-wrapper cmt-tab-wrapper">
                <a href="?page=crypto-miner-tycoon&tab=general" 
                   class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-generic"></span> General
                </a>
                <a href="?page=crypto-miner-tycoon&tab=branding" 
                   class="nav-tab <?php echo $active_tab === 'branding' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-art"></span> Branding <span class="cmt-tab-badge">PRO</span>
                </a>
                <a href="?page=crypto-miner-tycoon&tab=contests" 
                   class="nav-tab <?php echo $active_tab === 'contests' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-awards"></span> Contests <span class="cmt-tab-badge">PRO</span>
                </a>
                <a href="?page=crypto-miner-tycoon&tab=login" 
                   class="nav-tab <?php echo $active_tab === 'login' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-lock"></span> Login Pages <span class="cmt-tab-badge">PRO</span>
                </a>
            </nav>
            
            <!-- Tab Content -->
            <div class="cmt-tab-content">
                <?php
                switch ($active_tab) {
                    case 'general':
                        $this->render_general_tab();
                        break;
                    case 'branding':
                        $this->render_branding_tab_locked();
                        break;
                    case 'contests':
                        $this->render_contests_tab_locked();
                        break;
                    case 'login':
                        $this->render_login_tab_locked();
                        break;
                    default:
                        $this->render_general_tab();
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render General tab (FREE - Fully Functional)
     */
    private function render_general_tab() {
        ?>
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
                
                <!-- Upgrade CTA Box -->
                <div class="cmt-sidebar-box cmt-upgrade-box">
                    <h3>🚀 Upgrade to Pro</h3>
                    <p>Unlock powerful features:</p>
                    <ul>
                        <li>✨ Custom Branding</li>
                        <li>🏆 Timed Contests</li>
                        <li>🔐 Custom Login Pages</li>
                        <li>🎨 White Label Options</li>
                    </ul>
                    <a href="https://shortcodearcade.com/crypto-miner-tycoon-pro" target="_blank" class="button button-primary button-large cmt-upgrade-button">
                        Get Pro - Only $10
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render Branding tab (PRO - Locked)
     */
    private function render_branding_tab_locked() {
        ?>
        <div class="cmt-admin-container">
            <div class="cmt-admin-main">
                <div class="cmt-pro-placeholder">
                    <div class="cmt-pro-icon">🎨</div>
                    <h2>Custom Branding</h2>
                    <p>Make the game truly yours with full white-label customization.</p>
                    
                    <div class="cmt-feature-list">
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Custom Coin/Token Image:</strong> Replace Bitcoin with your own logo or token
                            </div>
                        </div>
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Color Theme Customization:</strong> Set primary, secondary, and accent colors
                            </div>
                        </div>
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Custom Game Title & Subtitle:</strong> Personalize the game's branding
                            </div>
                        </div>
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Currency Name Override:</strong> Change "Satoshis" to your own currency name
                            </div>
                        </div>
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Footer Customization:</strong> Add your own branding to the game footer
                            </div>
                        </div>
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Dynamic CSS Theming:</strong> All changes apply instantly without code
                            </div>
                        </div>
                    </div>
                    
                    <a href="https://shortcodearcade.com/crypto-miner-tycoon-pro" target="_blank" class="button button-primary button-hero cmt-upgrade-cta">
                        <span class="dashicons dashicons-unlock"></span> Unlock Branding Features - Get Pro
                    </a>
                </div>
            </div>
            
            <div class="cmt-admin-sidebar">
                <div class="cmt-sidebar-box cmt-pro-box">
                    <h3>🎨 Why Custom Branding?</h3>
                    <p>Perfect for:</p>
                    <ul>
                        <li>Crypto projects launching tokens</li>
                        <li>Agencies building for clients</li>
                        <li>Community engagement tools</li>
                        <li>Educational platforms</li>
                        <li>White-label solutions</li>
                    </ul>
                </div>
                
                <div class="cmt-sidebar-box">
                    <h3>💡 Pro Features Include</h3>
                    <ul>
                        <li>✅ Everything in Free</li>
                        <li>✅ Custom Branding</li>
                        <li>✅ Timed Contests</li>
                        <li>✅ Custom Login Pages</li>
                        <li>✅ Priority Support</li>
                    </ul>
                    <p style="margin-top: 15px; font-weight: 600;">Only $10 - Lifetime License</p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render Contests tab (PRO - Locked)
     */
    private function render_contests_tab_locked() {
        ?>
        <div class="cmt-admin-container">
            <div class="cmt-admin-main">
                <div class="cmt-pro-placeholder">
                    <div class="cmt-pro-icon">🏆</div>
                    <h2>Timed Contests</h2>
                    <p>Run competitions with leaderboards, prizes, and automatic winner selection.</p>
                    
                    <div class="cmt-feature-list">
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Monthly/Weekly Contests:</strong> Create recurring competitions automatically
                            </div>
                        </div>
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Custom Contest Periods:</strong> Set your own start and end dates
                            </div>
                        </div>
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Prize Management:</strong> Define rewards for 1st, 2nd, 3rd place and beyond
                            </div>
                        </div>
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Automatic Winner Selection:</strong> System ranks players when contest ends
                            </div>
                        </div>
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Email Notifications:</strong> Winners get notified automatically
                            </div>
                        </div>
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Contest Archive:</strong> View historical results and past winners
                            </div>
                        </div>
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Achievement Badges:</strong> Reward players with unlockable achievements
                            </div>
                        </div>
                    </div>
                    
                    <a href="https://shortcodearcade.com/crypto-miner-tycoon-pro" target="_blank" class="button button-primary button-hero cmt-upgrade-cta">
                        <span class="dashicons dashicons-unlock"></span> Unlock Contest Features - Get Pro
                    </a>
                </div>
            </div>
            
            <div class="cmt-admin-sidebar">
                <div class="cmt-sidebar-box cmt-pro-box">
                    <h3>🎮 Boost Engagement</h3>
                    <p>Contests dramatically increase:</p>
                    <ul>
                        <li>Player retention</li>
                        <li>Daily active users</li>
                        <li>Community interaction</li>
                        <li>Time spent on site</li>
                        <li>Social sharing</li>
                    </ul>
                </div>
                
                <div class="cmt-sidebar-box">
                    <h3>💰 Use Cases</h3>
                    <ul>
                        <li>Monthly tournaments</li>
                        <li>Holiday competitions</li>
                        <li>Community challenges</li>
                        <li>Prize giveaways</li>
                        <li>Marketing campaigns</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render Login Pages tab (PRO - Locked)
     */
    private function render_login_tab_locked() {
        ?>
        <div class="cmt-admin-container">
            <div class="cmt-admin-main">
                <div class="cmt-pro-placeholder">
                    <div class="cmt-pro-icon">🔐</div>
                    <h2>Custom Login Pages</h2>
                    <p>Create branded login and registration pages that match your game's aesthetic.</p>
                    
                    <div class="cmt-feature-list">
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Custom Login Page:</strong> Replace default WordPress login with branded version
                            </div>
                        </div>
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Branded Registration:</strong> Custom signup page matching game theme
                            </div>
                        </div>
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Email Verification:</strong> Optional double opt-in for security
                            </div>
                        </div>
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Social Login:</strong> Google, Facebook, Twitter integration
                            </div>
                        </div>
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Custom Redirects:</strong> Send users to game after login
                            </div>
                        </div>
                        <div class="cmt-feature-item">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <div>
                                <strong>Password Recovery:</strong> Branded password reset flow
                            </div>
                        </div>
                    </div>
                    
                    <a href="https://shortcodearcade.com/crypto-miner-tycoon-pro" target="_blank" class="button button-primary button-hero cmt-upgrade-cta">
                        <span class="dashicons dashicons-unlock"></span> Unlock Login Features - Get Pro
                    </a>
                </div>
            </div>
            
            <div class="cmt-admin-sidebar">
                <div class="cmt-sidebar-box cmt-pro-box">
                    <h3>🎨 Seamless Experience</h3>
                    <p>Benefits of custom login:</p>
                    <ul>
                        <li>Professional appearance</li>
                        <li>Brand consistency</li>
                        <li>Higher conversion rates</li>
                        <li>Better user experience</li>
                        <li>Reduced bounce rates</li>
                    </ul>
                </div>
                
                <div class="cmt-sidebar-box">
                    <h3>🔒 Security First</h3>
                    <p>Custom pages include:</p>
                    <ul>
                        <li>reCAPTCHA support</li>
                        <li>Email verification</li>
                        <li>Rate limiting</li>
                        <li>Secure authentication</li>
                    </ul>
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
