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
        // Save game endpoint
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
        
        // Load game endpoint
        register_rest_route('cmt/v1', '/load', array(
            'methods' => 'GET',
            'callback' => array($this, 'load_game'),
            'permission_callback' => array($this, 'check_cloud_save_permission')
        ));
        
        // Get leaderboard endpoint
        register_rest_route('cmt/v1', '/leaderboard', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_leaderboard'),
            'permission_callback' => '__return_true' // Public endpoint
        ));
    }
    
    /**
     * Check if user has permission for cloud saves
     */
    public function check_cloud_save_permission() {
        // Cloud saves must be enabled
        if (!get_option('cmt_enable_cloud_saves', false)) {
            return new WP_Error(
                'cloud_saves_disabled',
                'Cloud saves are not enabled on this site.',
                array('status' => 403)
            );
        }
        
        // User must be logged in
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
     * Validate save data structure
     */
    public function validate_save_data($value, $request, $param) {
        // Check required fields exist
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
        
        // Validate data types
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
        
        // Anti-cheat: Basic sanity check
        $max_reasonable_satoshis = $this->calculate_max_possible_earnings($value);
        if ($value['satoshis'] > $max_reasonable_satoshis * 2) {
            // Flag suspicious but allow (for now - you can make this stricter)
            error_log("CMT: Suspicious save data for user " . get_current_user_id() . " - earnings exceed theoretical maximum");
        }
        
        return true;
    }
    
    /**
     * Calculate theoretical maximum earnings (anti-cheat)
     */
    private function calculate_max_possible_earnings($save_data) {
        // Rough calculation: assume 30 days of 24/7 play with max possible production
        $max_days = 30;
        $seconds_per_day = 86400;
        
        // Assume max 10 clicks per second for click power
        $max_click_earnings = $save_data['clickPower'] * $save_data['prestigeMultiplier'] * 10 * $seconds_per_day * $max_days;
        
        // Passive income over 30 days
        $max_passive_earnings = $save_data['passiveIncome'] * $save_data['prestigeMultiplier'] * $seconds_per_day * $max_days;
        
        return $max_click_earnings + $max_passive_earnings;
    }
    
    /**
     * Save game data
     */
    public function save_game($request) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        $save_data = $request->get_param('save_data');
        
        // Calculate rank score (logarithmic + prestige weighted)
        $rank_score = $this->calculate_rank_score(
            $save_data['satoshis'],
            $save_data['prestigeLevel']
        );
        
        $table_name = $wpdb->prefix . 'cmt_saves';
        
        // Prepare data for insertion
        $data = array(
            'user_id' => $user_id,
            'save_data' => wp_json_encode($save_data),
            'base_click_power' => floatval($save_data['clickPower']),
            'base_passive_income' => floatval($save_data['passiveIncome']),
            'prestige_level' => intval($save_data['prestigeLevel']),
            'total_satoshis' => floatval($save_data['satoshis']),
            'rank_score' => floatval($rank_score)
        );
        
        $format = array('%d', '%s', '%f', '%f', '%d', '%f', '%f');
        
        // Insert or update
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM $table_name WHERE user_id = %d",
            $user_id
        ));
        
        if ($existing) {
            // Update existing save
            $result = $wpdb->update(
                $table_name,
                $data,
                array('user_id' => $user_id),
                $format,
                array('%d')
            );
        } else {
            // Insert new save
            $result = $wpdb->insert($table_name, $data, $format);
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
            'message' => 'Game saved successfully.',
            'rank_score' => $rank_score
        );
    }
    
    /**
     * Load game data
     */
    public function load_game($request) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        $table_name = $wpdb->prefix . 'cmt_saves';
        
        $save_data = $wpdb->get_var($wpdb->prepare(
            "SELECT save_data FROM $table_name WHERE user_id = %d",
            $user_id
        ));
        
        if (!$save_data) {
            return array(
                'success' => false,
                'message' => 'No saved game found.',
                'data' => null
            );
        }
        
        $decoded_data = json_decode($save_data, true);
        
        if (!$decoded_data) {
            return new WP_Error(
                'corrupt_save',
                'Save data is corrupted.',
                array('status' => 500)
            );
        }
        
        return array(
            'success' => true,
            'message' => 'Game loaded successfully.',
            'data' => $decoded_data
        );
    }
    
    /**
     * Get leaderboard
     */
    public function get_leaderboard($request) {
        // Check if leaderboard is enabled
        if (!get_option('cmt_enable_leaderboard', false)) {
            return new WP_Error(
                'leaderboard_disabled',
                'Leaderboard is not enabled on this site.',
                array('status' => 403)
            );
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'cmt_saves';
        $limit = get_option('cmt_leaderboard_limit', 10);
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                s.user_id,
                s.total_satoshis,
                s.prestige_level,
                s.rank_score,
                s.last_updated,
                u.display_name
            FROM $table_name s
            LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID
            ORDER BY s.rank_score DESC
            LIMIT %d",
            $limit
        ), ARRAY_A);
        
        if (!$results) {
            return array(
                'success' => true,
                'leaderboard' => array()
            );
        }
        
        // Format leaderboard data
        $leaderboard = array();
        $rank = 1;
        
        foreach ($results as $row) {
            $leaderboard[] = array(
                'rank' => $rank++,
                'username' => sanitize_text_field($row['display_name']),
                'satoshis' => floatval($row['total_satoshis']),
                'prestige_level' => intval($row['prestige_level']),
                'rank_score' => floatval($row['rank_score']),
                'last_updated' => $row['last_updated']
            );
        }
        
        return array(
            'success' => true,
            'leaderboard' => $leaderboard
        );
    }
    
    /**
     * Calculate rank score
     * Uses logarithmic scaling + prestige weighting to prevent raw currency inflation
     */
    private function calculate_rank_score($satoshis, $prestige_level) {
        // Logarithmic base score (prevents inflation)
        $base_score = log10($satoshis + 1) * 1000;
        
        // Prestige bonus (linear bonus for each prestige level)
        $prestige_bonus = $prestige_level * 10000;
        
        return $base_score + $prestige_bonus;
    }
}
