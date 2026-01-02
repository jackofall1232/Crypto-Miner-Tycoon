<?php
/**
 * Cloud Save Handler Class
 * 
 * Handles REST API endpoints for saving/loading game data
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class CMT_Cloud_Save {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route('cmt/v1', '/save', array(
            'methods' => 'POST',
            'callback' => array($this, 'save_game'),
            'permission_callback' => array($this, 'check_cloud_save_permission'),
            'args' => array(
                'save_data' => array(
                    'required' => true,
                    'type' => 'object',
                    'validate_callback' => array($this, 'validate_save_data')
                )
            )
        ));
        
        register_rest_route('cmt/v1', '/load', array(
            'methods' => 'GET',
            'callback' => array($this, 'load_game'),
            'permission_callback' => array($this, 'check_cloud_save_permission')
        ));
        
        register_rest_route('cmt/v1', '/leaderboard', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_leaderboard'),
            'permission_callback' => '__return_true'
        ));
    }
    
    /**
     * Permission check
     */
    public function check_cloud_save_permission() {
        if (!get_option('cmt_enable_cloud_saves', false)) {
            return new WP_Error(
                'cloud_saves_disabled',
                'Cloud saves are not enabled on this site.',
                array('status' => 403)
            );
        }
        
        if (!is_user_logged_in()) {
            return new WP_Error(
                'not_logged_in',
                'You must be logged in to use cloud saves.',
                array('status' => 401)
            );
        }
        
        return true;
    }
    
    /**
     * Validate save data
     */
    public function validate_save_data($value, $request, $param) {
        $required_fields = array(
            'satoshis',
            'clickPower',
            'passiveIncome',
            'rating',
            'prestigeLevel',
            'prestigeMultiplier',
            'upgrades'
        );
        
        foreach ($required_fields as $field) {
            if (!isset($value[$field])) {
                return new WP_Error(
                    'invalid_save_data',
                    "Missing required field: $field",
                    array('status' => 400)
                );
            }
        }
        
        if (!is_numeric($value['satoshis']) || $value['satoshis'] < 0) {
            return new WP_Error('invalid_save_data', 'Invalid satoshis value', array('status' => 400));
        }
        
        if (!is_numeric($value['clickPower']) || $value['clickPower'] < 1) {
            return new WP_Error('invalid_save_data', 'Invalid clickPower value', array('status' => 400));
        }
        
        if (!is_numeric($value['passiveIncome']) || $value['passiveIncome'] < 0) {
            return new WP_Error('invalid_save_data', 'Invalid passiveIncome value', array('status' => 400));
        }
        
        if (!is_int($value['prestigeLevel']) || $value['prestigeLevel'] < 0) {
            return new WP_Error('invalid_save_data', 'Invalid prestigeLevel value', array('status' => 400));
        }
        
        if (!is_array($value['upgrades'])) {
            return new WP_Error('invalid_save_data', 'Upgrades must be an object', array('status' => 400));
        }
        
        // Anti-cheat sanity check (debug only)
        $max_reasonable = $this->calculate_max_possible_earnings($value);
        if ($value['satoshis'] > $max_reasonable * 2 && defined('WP_DEBUG') && WP_DEBUG) {
            error_log(
                sprintf(
                    'CMT debug: Suspicious save data for user %d (satoshis: %f)',
                    get_current_user_id(),
                    $value['satoshis']
                )
            );
        }
        
        return true;
    }
    
    /**
     * Anti-cheat calculation
     */
    private function calculate_max_possible_earnings($save_data) {
        $seconds_per_day = 86400;
        $days = 30;
        
        $click = $save_data['clickPower'] * $save_data['prestigeMultiplier'] * 10 * $seconds_per_day * $days;
        $passive = $save_data['passiveIncome'] * $save_data['prestigeMultiplier'] * $seconds_per_day * $days;
        
        return $click + $passive;
    }
    
    /**
     * Save game
     */
    public function save_game($request) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        $save_data = $request->get_param('save_data');
        
        $rank_score = $this->calculate_rank_score(
            $save_data['satoshis'],
            $save_data['prestigeLevel']
        );
        
        $table_name = esc_sql($wpdb->prefix . 'cmt_saves');
        
        $data = array(
            'user_id' => $user_id,
            'save_data' => wp_json_encode($save_data),
            'base_click_power' => (float) $save_data['clickPower'],
            'base_passive_income' => (float) $save_data['passiveIncome'],
            'prestige_level' => (int) $save_data['prestigeLevel'],
            'total_satoshis' => (float) $save_data['satoshis'],
            'rank_score' => (float) $rank_score
        );
        
        $formats = array('%d', '%s', '%f', '%f', '%d', '%f', '%f');
        
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT user_id FROM {$table_name} WHERE user_id = %d",
                $user_id
            )
        );
        
        if ($existing) {
            $result = $wpdb->update(
                $table_name,
                $data,
                array('user_id' => $user_id),
                $formats,
                array('%d')
            );
        } else {
            $result = $wpdb->insert($table_name, $data, $formats);
        }
        
        if ($result === false) {
            return new WP_Error(
                'save_failed',
                'Failed to save game data.',
                array('status' => 500)
            );
        }
        
        return array(
            'success' => true,
            'rank_score' => $rank_score
        );
    }
    
    /**
     * Load game
     */
    public function load_game($request) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        $table_name = esc_sql($wpdb->prefix . 'cmt_saves');
        
        $save_data = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT save_data FROM {$table_name} WHERE user_id = %d",
                $user_id
            )
        );
        
        if (!$save_data) {
            return array(
                'success' => false,
                'data' => null
            );
        }
        
        $decoded = json_decode($save_data, true);
        
        if (!$decoded) {
            return new WP_Error(
                'corrupt_save',
                'Save data is corrupted.',
                array('status' => 500)
            );
        }
        
        return array(
            'success' => true,
            'data' => $decoded
        );
    }
    
    /**
     * Get leaderboard
     */
    public function get_leaderboard($request) {
        if (!get_option('cmt_enable_leaderboard', false)) {
            return new WP_Error(
                'leaderboard_disabled',
                'Leaderboard is not enabled.',
                array('status' => 403)
            );
        }
        
        global $wpdb;
        
        $table = esc_sql($wpdb->prefix . 'cmt_saves');
        $users = esc_sql($wpdb->users);
        $limit = (int) get_option('cmt_leaderboard_limit', 10);
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    s.user_id,
                    s.total_satoshis,
                    s.prestige_level,
                    s.rank_score,
                    s.last_updated,
                    u.display_name
                FROM {$table} s
                LEFT JOIN {$users} u ON s.user_id = u.ID
                ORDER BY s.rank_score DESC
                LIMIT %d",
                $limit
            ),
            ARRAY_A
        );
        
        $leaderboard = array();
        $rank = 1;
        
        foreach ((array) $results as $row) {
            $leaderboard[] = array(
                'rank' => $rank++,
                'username' => sanitize_text_field($row['display_name']),
                'satoshis' => (float) $row['total_satoshis'],
                'prestige_level' => (int) $row['prestige_level'],
                'rank_score' => (float) $row['rank_score'],
                'last_updated' => $row['last_updated']
            );
        }
        
        return array(
            'success' => true,
            'leaderboard' => $leaderboard
        );
    }
    
    /**
     * Rank score calculation
     */
    private function calculate_rank_score($satoshis, $prestige_level) {
        $base = log10($satoshis + 1) * 1000;
        $prestige = $prestige_level * 10000;
        return $base + $prestige;
    }
}
