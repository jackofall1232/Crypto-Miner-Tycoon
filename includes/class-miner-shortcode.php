<?php
/**
 * Shortcode Handler Class
 * 
 * Handles the [crypto_miner_tycoon] shortcode
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class CMT_Miner_Shortcode {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('crypto_miner_tycoon', array($this, 'render_game'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }
    
    /**
     * Enqueue CSS and JS assets
     */
    public function enqueue_assets() {
        // Only enqueue if shortcode is present on the page
        global $post;
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'crypto_miner_tycoon')) {
            // Enqueue Google Fonts
            wp_enqueue_style(
                'cmt-google-fonts',
                'https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;600&display=swap',
                array(),
                null
            );
            
            // Enqueue game CSS
            wp_enqueue_style(
                'cmt-game-css',
                CMT_PLUGIN_URL . 'assets/css/game.css',
                array(),
                CMT_VERSION
            );
            
            // Enqueue game JS
            wp_enqueue_script(
                'cmt-game-js',
                CMT_PLUGIN_URL . 'assets/js/game.js',
                array(),
                CMT_VERSION,
                true
            );
            
            // Pass settings to JavaScript
            $this->localize_script();
        }
    }
    
    /**
     * Localize script with settings and data
     */
    private function localize_script() {
        $cloud_saves_enabled = get_option('cmt_enable_cloud_saves', false);
        
        $script_data = array(
            'cloudSavesEnabled' => $cloud_saves_enabled,
            'isUserLoggedIn' => is_user_logged_in(),
            'restUrl' => rest_url('cmt/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'userId' => get_current_user_id()
        );
        
        wp_localize_script('cmt-game-js', 'cmtSettings', $script_data);
    }
    
    /**
     * Render the game
     */
    public function render_game($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(
            array(
                'ad_code' => '', // Allow custom ad code via shortcode attribute
            ),
            $atts,
            'crypto_miner_tycoon'
        );
        
        // Check if cloud saves are enabled and user is not logged in
        $cloud_saves_enabled = get_option('cmt_enable_cloud_saves', false);
        $show_login_notice = $cloud_saves_enabled && !is_user_logged_in();
        
        // Start output buffering
        ob_start();
        ?>
        
        <?php if ($show_login_notice): ?>
            <div class="cmt-login-notice">
                <p><strong>Note:</strong> Cloud saves are enabled. Please <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>">log in</a> to save your progress.</p>
            </div>
        <?php endif; ?>
        
        <div class="cmt-container">
            <button class="cmt-info-button" onclick="cmtShowModal()">?</button>
            
            <header class="cmt-header">
                <h1 class="cmt-title">Crypto Miner Tycoon</h1>
                <div class="cmt-subtitle">Click. Mine. Prosper.</div>
            </header>

            <div class="cmt-main-game">
                <div class="cmt-game-area">
                    <div class="cmt-stats">
                        <div class="cmt-stat-item">
                            <span class="cmt-stat-label">Satoshis</span>
                            <span class="cmt-stat-value" id="cmt-satoshis">0</span>
                        </div>
                        <div class="cmt-stat-item">
                            <span class="cmt-stat-label">Per Click</span>
                            <span class="cmt-stat-value" id="cmt-clickPower">1</span>
                        </div>
                        <div class="cmt-stat-item">
                            <span class="cmt-stat-label">Per Second</span>
                            <span class="cmt-stat-value" id="cmt-passiveIncome">0</span>
                        </div>
                        <div class="cmt-stat-item">
                            <span class="cmt-stat-label">Miner Rating</span>
                            <span class="cmt-stat-value" id="cmt-rating">1000</span>
                        </div>
                    </div>

                    <div class="cmt-mine-button" id="cmt-mineButton" onclick="cmtMine()">
                        <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="cmt-coinGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#00ffff;stop-opacity:1" />
                                    <stop offset="50%" style="stop-color:#ff00ff;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#ffff00;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            <circle cx="100" cy="100" r="80" fill="url(#cmt-coinGrad)" opacity="0.2"/>
                            <circle cx="100" cy="100" r="75" fill="none" stroke="url(#cmt-coinGrad)" stroke-width="4"/>
                            <path d="M 80 60 L 80 140 M 90 60 L 90 140" stroke="url(#cmt-coinGrad)" stroke-width="3" stroke-linecap="round"/>
                            <path d="M 70 75 L 120 75 C 130 75 135 80 135 90 C 135 100 130 105 120 105 L 70 105 M 70 105 L 125 105 C 135 105 140 110 140 120 C 140 130 135 135 125 135 L 70 135" 
                                  fill="none" stroke="url(#cmt-coinGrad)" stroke-width="4" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>

                <div class="cmt-upgrades">
                    <h2>Upgrades</h2>
                    <div id="cmt-upgradesList"></div>
                </div>
            </div>

            <div class="cmt-prestige-section">
                <div class="cmt-prestige-info">
                    Hard Fork available at 1,000,000 satoshis<br>
                    <span style="font-size: 0.9rem; opacity: 0.7;">Reset with permanent +10% bonus to all production</span>
                </div>
                <button class="cmt-prestige-button" id="cmt-prestigeButton" onclick="cmtPrestige()" disabled>
                    HARD FORK
                </button>
            </div>

            <?php if (!empty($atts['ad_code'])) : ?>
                <div class="cmt-ad-container">
                    <div class="cmt-ad-label">Advertisement</div>
                    <div class="cmt-ad-content">
                        <?php echo wp_kses_post($atts['ad_code']); ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="cmt-ad-container">
                    <div class="cmt-ad-label">Advertisement</div>
                    <div class="cmt-ad-placeholder">
                        728 x 90 Ad Space
                    </div>
                </div>
            <?php endif; ?>

            <footer class="cmt-footer">
                Crypto Miner Tycoon © <?php echo esc_html(date('Y')); ?> | Game auto-saves every 10 seconds
            </footer>
        </div>

        <div class="cmt-save-indicator" id="cmt-saveIndicator">Game Saved</div>

        <!-- Info Modal -->
        <div class="cmt-modal" id="cmt-infoModal">
            <div class="cmt-modal-content">
                <h2>How to Play</h2>
                <p><strong>Goal:</strong> Build the ultimate crypto mining empire!</p>
                <p><strong>Click the Bitcoin:</strong> Earn satoshis manually by clicking the glowing Bitcoin symbol.</p>
                <p><strong>Buy Upgrades:</strong> Spend satoshis on upgrades to increase your mining power and automate your income.</p>
                <p><strong>Elo Rating System:</strong> As you progress, your miner rating increases. Higher ratings unlock more powerful upgrades, but they also cost more based on the difficulty curve.</p>
                <p><strong>Hard Fork (Prestige):</strong> Once you reach 1,000,000 satoshis, you can perform a "Hard Fork" to reset your progress with a permanent +10% production bonus. This multiplier stacks!</p>
                <p><strong>Strategy:</strong> Balance between manual clicking upgrades and passive income generators for optimal growth.</p>
                <?php if ($cloud_saves_enabled && is_user_logged_in()): ?>
                    <p><strong>Cloud Saves:</strong> Your progress is automatically saved to the cloud!</p>
                <?php endif; ?>
                <button class="cmt-close-modal" onclick="cmtHideModal()">Start Mining!</button>
            </div>
        </div>
        
        <?php
        return ob_get_clean();
    }
}
