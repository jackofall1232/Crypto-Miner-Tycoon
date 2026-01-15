<?php
/**
 * Cloud Save Handler Class
 *
 * Handles REST API endpoints for saving/loading game data.
 *
 * @package Crypto_Miner_Tycoon
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cloud save REST handler.
 */
class SACIG_Cloud_Save {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_routes() {

		// Save game endpoint.
		register_rest_route(
			'sacig/v1',
			'/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'save_game' ),
				'permission_callback' => array( $this, 'check_cloud_save_permission' ),
				'args'                => array(
					'save_data' => array(
						'required'          => true,
						'type'              => 'object',
						'validate_callback' => array( $this, 'validate_save_data' ),
					),
				),
			)
		);

		// Load game endpoint.
		register_rest_route(
			'sacig/v1',
			'/load',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'load_game' ),
				'permission_callback' => array( $this, 'check_cloud_save_permission' ),
			)
		);

		// Get leaderboard endpoint (public).
		register_rest_route(
			'sacig/v1',
			'/leaderboard',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_leaderboard' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Check if user has permission for cloud saves.
	 *
	 * @return true|WP_Error
	 */
	public function check_cloud_save_permission() {

		// Cloud saves must be enabled.
		$enabled = (bool) get_option( 'sacig_enable_cloud_saves', false );
		if ( ! $enabled ) {
			return new WP_Error(
				'cloud_saves_disabled',
				'Cloud saves are not enabled on this site.',
				array( 'status' => 403 )
			);
		}

		// User must be logged in.
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'not_logged_in',
				'You must be logged in to use cloud saves.',
				array( 'status' => 401 )
			);
		}

		return true;
	}

	/**
	 * Validate save data structure.
	 *
	 * @param mixed           $value   Value of the parameter.
	 * @param WP_REST_Request $request Request object.
	 * @param string          $param   Parameter name.
	 * @return true|WP_Error
	 */
	public function validate_save_data( $value, $request, $param ) {

		if ( ! is_array( $value ) ) {
			return new WP_Error(
				'invalid_save_data',
				'Save data must be an object.',
				array( 'status' => 400 )
			);
		}

		// Check required fields exist.
		$required_fields = array(
			'satoshis',
			'clickPower',
			'passiveIncome',
			'rating',
			'prestigeLevel',
			'prestigeMultiplier',
			'upgrades',
		);

		foreach ( $required_fields as $field ) {
			if ( ! array_key_exists( $field, $value ) ) {
				return new WP_Error(
					'invalid_save_data',
					'Missing required field: ' . sanitize_key( $field ),
					array( 'status' => 400 )
				);
			}
		}

		// Validate data types.
		if ( ! is_numeric( $value['satoshis'] ) || $value['satoshis'] < 0 ) {
			return new WP_Error( 'invalid_save_data', 'Invalid satoshis value', array( 'status' => 400 ) );
		}

		if ( ! is_numeric( $value['clickPower'] ) || $value['clickPower'] < 1 ) {
			return new WP_Error( 'invalid_save_data', 'Invalid clickPower value', array( 'status' => 400 ) );
		}

		if ( ! is_numeric( $value['passiveIncome'] ) || $value['passiveIncome'] < 0 ) {
			return new WP_Error( 'invalid_save_data', 'Invalid passiveIncome value', array( 'status' => 400 ) );
		}

		// REST request values commonly arrive as numeric strings; accept numeric and then validate bounds.
		if ( ! is_numeric( $value['prestigeLevel'] ) || (int) $value['prestigeLevel'] < 0 ) {
			return new WP_Error( 'invalid_save_data', 'Invalid prestigeLevel value', array( 'status' => 400 ) );
		}

		if ( ! is_array( $value['upgrades'] ) ) {
			return new WP_Error( 'invalid_save_data', 'Upgrades must be an object', array( 'status' => 400 ) );
		}

		// Anti-cheat: basic sanity check (log only during debug).
		$max_reasonable_satoshis = $this->calculate_max_possible_earnings( $value );
		if ( is_numeric( $max_reasonable_satoshis ) && $max_reasonable_satoshis > 0 ) {
			if ( (float) $value['satoshis'] > ( (float) $max_reasonable_satoshis * 2 ) ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug-only anti-cheat monitoring.
					error_log( 'SACIG: Suspicious save data for user ' . get_current_user_id() . ' - earnings exceed theoretical maximum' );
				}
			}
		}

		return true;
	}

	/**
	 * Calculate theoretical maximum earnings (anti-cheat).
	 *
	 * @param array $save_data Save data array.
	 * @return float
	 */
	private function calculate_max_possible_earnings( $save_data ) {

		// Rough calculation: assume 30 days of 24/7 play with max possible production.
		$max_days        = 30;
		$seconds_per_day = 86400;

		$click_power         = isset( $save_data['clickPower'] ) ? (float) $save_data['clickPower'] : 0.0;
		$prestige_multiplier = isset( $save_data['prestigeMultiplier'] ) ? (float) $save_data['prestigeMultiplier'] : 1.0;
		$passive_income      = isset( $save_data['passiveIncome'] ) ? (float) $save_data['passiveIncome'] : 0.0;

		// Assume max 10 clicks per second for click power.
		$max_click_earnings = $click_power * $prestige_multiplier * 10 * $seconds_per_day * $max_days;

		// Passive income over 30 days.
		$max_passive_earnings = $passive_income * $prestige_multiplier * $seconds_per_day * $max_days;

		return (float) ( $max_click_earnings + $max_passive_earnings );
	}

	/**
	 * Save game data.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return array|WP_Error
	 */
	public function save_game( $request ) {
		global $wpdb;

		$user_id   = get_current_user_id();
		$save_data = $request->get_param( 'save_data' );

		if ( ! is_array( $save_data ) ) {
			return new WP_Error(
				'invalid_save_data',
				'Save data must be an object.',
				array( 'status' => 400 )
			);
		}

		// Calculate rank score (logarithmic + prestige weighted).
		$rank_score = $this->calculate_rank_score(
			(float) $save_data['satoshis'],
			(int) $save_data['prestigeLevel']
		);

		$table_name = $wpdb->prefix . 'sacig_saves';

		$encoded = wp_json_encode( $save_data );
		if ( false === $encoded ) {
			return new WP_Error(
				'encode_failed',
				'Failed to encode save data.',
				array( 'status' => 500 )
			);
		}

		// Prepare data for insertion.
		$data = array(
			'user_id'            => $user_id,
			'save_data'          => $encoded,
			'base_click_power'   => (float) $save_data['clickPower'],
			'base_passive_income'=> (float) $save_data['passiveIncome'],
			'prestige_level'     => (int) $save_data['prestigeLevel'],
			'total_satoshis'     => (float) $save_data['satoshis'],
			'rank_score'         => (float) $rank_score,
		);

		$format = array( '%d', '%s', '%f', '%f', '%d', '%f', '%f' );

		// Check if user already has a save.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		// Table name is safely constructed using $wpdb->prefix constant.
		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT user_id FROM {$table_name} WHERE user_id = %d",
				$user_id
			)
		);
		// phpcs:enable

		if ( $existing ) {
			// Update existing save.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table update
			$result = $wpdb->update(
				$table_name,
				$data,
				array( 'user_id' => $user_id ),
				$format,
				array( '%d' )
			);
		} else {
			// Insert new save.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table insert
			$result = $wpdb->insert( $table_name, $data, $format );
		}

		if ( false === $result ) {
			return new WP_Error(
				'save_failed',
				'Failed to save game data.',
				array( 'status' => 500 )
			);
		}

		return array(
			'success'    => true,
			'message'    => 'Game saved successfully.',
			'rank_score' => (float) $rank_score,
		);
	}

	/**
	 * Load game data.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return array|WP_Error
	 */
	public function load_game( $request ) {
		global $wpdb;

		$user_id    = get_current_user_id();
		$table_name = $wpdb->prefix . 'sacig_saves';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		// Table name is safely constructed using $wpdb->prefix constant.
		$save_data = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT save_data FROM {$table_name} WHERE user_id = %d",
				$user_id
			)
		);
		// phpcs:enable

		if ( ! $save_data ) {
			return array(
				'success' => false,
				'message' => 'No saved game found.',
				'data'    => null,
			);
		}

		$decoded_data = json_decode( $save_data, true );

		if ( ! is_array( $decoded_data ) ) {
			return new WP_Error(
				'corrupt_save',
				'Save data is corrupted.',
				array( 'status' => 500 )
			);
		}

		return array(
			'success' => true,
			'message' => 'Game loaded successfully.',
			'data'    => $decoded_data,
		);
	}

	/**
	 * Get leaderboard.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return array|WP_Error
	 */
	public function get_leaderboard( $request ) {

		// Check if leaderboard is enabled.
		$enabled = (bool) get_option( 'sacig_enable_leaderboard', false );
		if ( ! $enabled ) {
			return new WP_Error(
				'leaderboard_disabled',
				'Leaderboard is not enabled on this site.',
				array( 'status' => 403 )
			);
		}

		global $wpdb;

		$limit = (int) get_option( 'sacig_leaderboard_limit', 10 );
		if ( $limit < 1 ) {
			$limit = 10;
		}
		if ( $limit > 100 ) {
			$limit = 100;
		}

		$table_name  = $wpdb->prefix . 'sacig_saves';
		$users_table = $wpdb->users;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
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

		if ( empty( $results ) ) {
			return array(
				'success'     => true,
				'leaderboard' => array(),
			);
		}

		// Format leaderboard data.
		$leaderboard = array();
		$rank        = 1;

		foreach ( $results as $row ) {
			$leaderboard[] = array(
				'rank'          => $rank++,
				'username'      => isset( $row['display_name'] ) ? sanitize_text_field( $row['display_name'] ) : '',
				'satoshis'      => isset( $row['total_satoshis'] ) ? (float) $row['total_satoshis'] : 0.0,
				'prestige_level'=> isset( $row['prestige_level'] ) ? (int) $row['prestige_level'] : 0,
				'rank_score'    => isset( $row['rank_score'] ) ? (float) $row['rank_score'] : 0.0,
				'last_updated'  => isset( $row['last_updated'] ) ? sanitize_text_field( $row['last_updated'] ) : '',
			);
		}

		return array(
			'success'     => true,
			'leaderboard' => $leaderboard,
		);
	}

	/**
	 * Calculate rank score.
	 *
	 * Uses logarithmic scaling + prestige weighting to prevent raw currency inflation.
	 *
	 * @param float $satoshis       Total satoshis.
	 * @param int   $prestige_level Prestige level.
	 * @return float
	 */
	private function calculate_rank_score( $satoshis, $prestige_level ) {

		$satoshis       = (float) $satoshis;
		$prestige_level = (int) $prestige_level;

		// Logarithmic base score (prevents inflation).
		$base_score = log10( $satoshis + 1 ) * 1000;

		// Prestige bonus (linear bonus for each prestige level).
		$prestige_bonus = $prestige_level * 10000;

		return (float) ( $base_score + $prestige_bonus );
	}
}
