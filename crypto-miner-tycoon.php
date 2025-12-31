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
            <!-- GAME HTML GOES HERE -->
        </div>
        <?php
        return ob_get_clean();
    }
}

new Crypto_Miner_Tycoon();
