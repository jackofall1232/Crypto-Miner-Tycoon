<?php
/**
 * Plugin Name: Crypto Miner Tycoon
 * Description: Neon-styled idle crypto mining clicker game.
 * Version: 1.0.0
 * Author: Hobo Joe
 * License: GPLv3
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Crypto_Miner_Tycoon {

    public function __construct() {
        add_shortcode( 'crypto_miner_tycoon', [ $this, 'render_game' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function enqueue_assets() {

        wp_register_style(
            'crypto-miner-tycoon-css',
            plugins_url( 'assets/css/game.css', __FILE__ ),
            [],
            '1.0.0'
        );

        wp_register_script(
            'crypto-miner-tycoon-js',
            plugins_url( 'assets/js/game.js', __FILE__ ),
            [],
            '1.0.0',
            true
        );
    }

    public function render_game() {
        wp_enqueue_style( 'crypto-miner-tycoon-css' );
        wp_enqueue_script( 'crypto-miner-tycoon-js' );

        ob_start();
        ?>
        <div id="crypto-miner-tycoon-root">
            <!-- GAME HTML -->
            <button class="info-button" onclick="CMT.showModal()">?</button>

            <header>
                <h1>Crypto Miner Tycoon</h1>
                <div class="subtitle">Click. Mine. Prosper.</div>
            </header>

            <div class="main-game">
                <div class="game-area">
                    <div class="stats">
                        <div class="stat-item">
                            <span class="stat-label">Satoshis</span>
                            <span class="stat-value" id="satoshis">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Per Click</span>
                            <span class="stat-value" id="clickPower">1</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Per Second</span>
                            <span class="stat-value" id="passiveIncome">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Miner Rating</span>
                            <span class="stat-value" id="rating">1000</span>
                        </div>
                    </div>

                    <div class="mine-button" id="mineButton" onclick="CMT.mine()">
                        <svg viewBox="0 0 200 200">
                            <defs>
                                <linearGradient id="coinGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#00ffff"/>
                                    <stop offset="50%" stop-color="#ff00ff"/>
                                    <stop offset="100%" stop-color="#ffff00"/>
                                </linearGradient>
                            </defs>
                            <circle cx="100" cy="100" r="80" fill="url(#coinGrad)" opacity="0.2"/>
                            <circle cx="100" cy="100" r="75" fill="none" stroke="url(#coinGrad)" stroke-width="4"/>
                        </svg>
                    </div>
                </div>

                <div class="upgrades">
                    <h2>Upgrades</h2>
                    <div id="upgradesList"></div>
                </div>
            </div>

            <div class="prestige-section">
                <div class="prestige-info">
                    Hard Fork available at 1,000,000 satoshis
                </div>
                <button class="prestige-button" id="prestigeButton" onclick="CMT.prestige()" disabled>
                    HARD FORK
                </button>
            </div>

            <footer>
                Crypto Miner Tycoon © 2024
            </footer>

            <div class="modal" id="infoModal">
                <div class="modal-content">
                    <h2>How to Play</h2>
                    <p>Click to mine. Buy upgrades. Prestige to grow faster.</p>
                    <button class="close-modal" onclick="CMT.hideModal()">Start Mining</button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

new Crypto_Miner_Tycoon();
