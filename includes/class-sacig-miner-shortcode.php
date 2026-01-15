<?php
/**
 * Shortcode Handler Class
 *
 * Handles the [sacig_crypto_idle_game] and [sacig_crypto_idle_leaderboard] shortcodes.
 * Renders game UI, manages asset enqueuing, and displays leaderboards.
 *
 * @package Shortcode_Arcade_Crypto_Idle_Game
 * @since 0.4.6
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class SACIG_Miner_Shortcode {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('sacig_crypto_idle_game', array($this, 'render_game'));
        add_shortcode('sacig_crypto_idle_leaderboard', array($this, 'render_leaderboard'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }
    
    /**
     * Enqueue CSS and JS assets
     */
    public function enqueue_assets() {
        // Only enqueue if shortcode is present on the page
        global $post;
        
        if (is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'sacig_crypto_idle_game') || has_shortcode($post->post_content, 'sacig_crypto_idle_leaderboard'))) {
            // Enqueue Google Fonts
            // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Google Fonts handles versioning via URL parameters
            wp_enqueue_style(
    'sacig-google-fonts',
    'https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;600&display=swap',
    array(),
    SACIG_VERSION
);

            // Enqueue game CSS
            wp_enqueue_style(
                'sacig-game-css',
                SACIG_PLUGIN_URL . 'assets/css/sacig-game.css',
                array(),
                SACIG_VERSION
            );
            
            // Only enqueue JS for the game shortcode
            if (has_shortcode($post->post_content, 'sacig_crypto_idle_game')) {
                wp_enqueue_script(
                    'sacig-game-js',
                    SACIG_PLUGIN_URL . 'assets/js/sacig-game.js',
                    array(),
                    SACIG_VERSION,
                    true
                );

                // Pass settings to JavaScript
                $this->localize_script();
            }
        }
    }
    
    /**
     * Localize script with settings and data
     */
    private function localize_script() {
        $cloud_saves_enabled = get_option('sacig_enable_cloud_saves', false);

        $script_data = array(
            'cloudSavesEnabled' => $cloud_saves_enabled,
            'isUserLoggedIn' => is_user_logged_in(),
            'restUrl' => rest_url('sacig/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'userId' => get_current_user_id()
        );

        wp_localize_script('sacig-game-js', 'sacigSettings', $script_data);
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
            'sacig_crypto_idle_game'
        );
        
        // Check if cloud saves are enabled and user is not logged in
        $cloud_saves_enabled = get_option('sacig_enable_cloud_saves', false);
        $show_login_notice = $cloud_saves_enabled && !is_user_logged_in();
        
        // Start output buffering
        ob_start();
        ?>
        
        <?php if ($show_login_notice): ?>
            <div class="sacig-login-notice">
                <p><strong>Note:</strong> Cloud saves are enabled. Please <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>">log in</a> to save your progress.</p>
            </div>
        <?php endif; ?>
        
        <div class="sacig-container">
            <button class="sacig-info-button" onclick="sacigShowModal()">?</button>
            
            <header class="sacig-header">
                <h1 class="sacig-title">Shortcode Arcade Crypto Idle Game</h1>
                <div class="sacig-subtitle">Click. Mine. Prosper.</div>
            </header>

            <div class="sacig-main-game">
                <div class="sacig-game-area">
                    <div class="sacig-stats">
                        <div class="sacig-stat-item">
                            <span class="sacig-stat-label">Satoshis</span>
                            <span class="sacig-stat-value" id="sacig-satoshis">0</span>
                        </div>
                        <div class="sacig-stat-item">
                            <span class="sacig-stat-label">Per Click</span>
                            <span class="sacig-stat-value" id="sacig-clickPower">1</span>
                        </div>
                        <div class="sacig-stat-item">
                            <span class="sacig-stat-label">Per Second</span>
                            <span class="sacig-stat-value" id="sacig-passiveIncome">0</span>
                        </div>
                        <div class="sacig-stat-item">
                            <span class="sacig-stat-label">Miner Rating</span>
                            <span class="sacig-stat-value" id="sacig-rating">1000</span>
                        </div>
                    </div>

                    <div class="sacig-mine-button" id="sacig-mineButton" onclick="sacigMine()">
                        <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="sacig-coinGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#00ffff;stop-opacity:1" />
                                    <stop offset="50%" style="stop-color:#ff00ff;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#ffff00;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            <circle cx="100" cy="100" r="80" fill="url(#sacig-coinGrad)" opacity="0.2"/>
                            <circle cx="100" cy="100" r="75" fill="none" stroke="url(#sacig-coinGrad)" stroke-width="4"/>
                            <path d="M 80 60 L 80 140 M 90 60 L 90 140" stroke="url(#sacig-coinGrad)" stroke-width="3" stroke-linecap="round"/>
                            <path d="M 70 75 L 120 75 C 130 75 135 80 135 90 C 135 100 130 105 120 105 L 70 105 M 70 105 L 125 105 C 135 105 140 110 140 120 C 140 130 135 135 125 135 L 70 135" 
                                  fill="none" stroke="url(#sacig-coinGrad)" stroke-width="4" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>

                <div class="sacig-upgrades">
                    <h2>Upgrades</h2>
                    <div id="sacig-upgradesList"></div>
                </div>
            </div>

            <div class="sacig-prestige-section">
                <div class="sacig-prestige-info">
                    Hard Fork available at 1,000,000 satoshis<br>
                    <span style="font-size: 0.9rem; opacity: 0.7;">Reset with permanent +10% bonus to all production</span>
                </div>
                <button class="sacig-prestige-button" id="sacig-prestigeButton" onclick="sacigPrestige()" disabled>
                    HARD FORK
                </button>
            </div>

            <?php if (!empty($atts['ad_code'])) : ?>
                <div class="sacig-ad-container">
                    <div class="sacig-ad-label">Advertisement</div>
                    <div class="sacig-ad-content">
                        <?php echo wp_kses_post($atts['ad_code']); ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="sacig-ad-container">
                    <div class="sacig-ad-label">Advertisement</div>
                    <div class="sacig-ad-placeholder">
                        728 x 90 Ad Space
                    </div>
                </div>
            <?php endif; ?>

            <footer class="sacig-footer">
                Shortcode Arcade Crypto Idle Game © <?php echo esc_html(gmdate('Y')); ?> | Game auto-saves every 10 seconds
            </footer>
        </div>

        <div class="sacig-save-indicator" id="sacig-saveIndicator">Game Saved</div>

        <!-- Info Modal -->
        <div class="sacig-modal" id="sacig-infoModal">
            <div class="sacig-modal-content">
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
                <button class="sacig-close-modal" onclick="sacigHideModal()">Start Mining!</button>
            </div>
        </div>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render leaderboard
     */
    public function render_leaderboard($atts) {
        // Check if leaderboard is enabled
        if (!get_option('sacig_enable_leaderboard', false)) {
            return '<p class="sacig-leaderboard-disabled">Leaderboard is not enabled.</p>';
        }

        // Parse attributes
        $atts = shortcode_atts(
            array(
                'limit' => get_option('sacig_leaderboard_limit', 10),
            ),
            $atts,
            'sacig_crypto_idle_leaderboard'
        );

        // Get leaderboard data
        global $wpdb;
        $table_name  = $wpdb->prefix . 'sacig_saves';
        $users_table = $wpdb->users;
        $limit       = intval($atts['limit']);

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
        // phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter
        // Table names are safely constructed using $wpdb->prefix and $wpdb->users constants.
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    s.user_id,
                    s.total_satoshis,
                    s.prestige_level,
                    s.rank_score,
                    s.last_updated,
                    u.display_name
                FROM {$table_name} s
                LEFT JOIN {$users_table} u ON s.user_id = u.ID
                ORDER BY s.rank_score DESC
                LIMIT %d",
                $limit
            ),
            ARRAY_A
        );
        // phpcs:enable
        
        // Start output
        ob_start();
        ?>
        <div class="sacig-leaderboard-container">
            <h2 class="sacig-leaderboard-title">🏆 Top Miners</h2>
            
            <?php if (empty($results)): ?>
                <p class="sacig-leaderboard-empty">No players yet. Be the first!</p>
            <?php else: ?>
                <table class="sacig-leaderboard-table">
                    <thead>
                        <tr>
                            <th class="sacig-rank">Rank</th>
                            <th class="sacig-player">Player</th>
                            <th class="sacig-satoshis">Satoshis</th>
                            <th class="sacig-prestige">Prestige</th>
                            <th class="sacig-score">Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        foreach ($results as $row): 
                            $rank_class = '';
                            if ($rank === 1) $rank_class = 'sacig-rank-1';
                            elseif ($rank === 2) $rank_class = 'sacig-rank-2';
                            elseif ($rank === 3) $rank_class = 'sacig-rank-3';
                            
                            $is_current_user = is_user_logged_in() && get_current_user_id() == $row['user_id'];
                        ?>
                        <tr class="<?php echo esc_attr($rank_class); ?> <?php echo $is_current_user ? 'sacig-current-user' : ''; ?>">
                            <td class="sacig-rank">
                                <?php if ($rank <= 3): ?>
                                    <span class="sacig-medal">
                                        <?php echo $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : '🥉'); ?>
                                    </span>
                                <?php else: ?>
                                    <?php echo esc_html($rank); ?>
                                <?php endif; ?>
                            </td>
                            <td class="sacig-player">
                                <?php echo esc_html($row['display_name']); ?>
                                <?php if ($is_current_user): ?>
                                    <span class="sacig-you-badge">You</span>
                                <?php endif; ?>
                            </td>
                            <td class="sacig-satoshis"><?php echo esc_html(number_format($row['total_satoshis'], 2)); ?></td>
                            <td class="sacig-prestige">Level <?php echo esc_html($row['prestige_level']); ?></td>
                            <td class="sacig-score"><?php echo esc_html(number_format($row['rank_score'], 0)); ?></td>
                        </tr>
                        <?php 
                        $rank++;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
        
        return ob_get_clean();
    }
}
