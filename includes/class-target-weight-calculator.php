<?php
/**
 * Target Weight Calculator
 *
 * Calculates target weight based on weight loss assessment answers
 * Uses current weight from wl_q1 and weight loss goal from wl_q2
 *
 * @package ENNU_Life
 * @since 64.3.4
 */

class ENNU_Target_Weight_Calculator {

	/**
	 * Calculate target weight based on assessment answers
	 *
	 * @param int $user_id User ID
	 * @return array|false Target weight data or false if calculation not possible
	 */
	public static function calculate_target_weight( $user_id ) {
		
		// Get weight loss assessment answers
		$current_weight_data = get_user_meta( $user_id, 'ennu_weight-loss_wl_q1', true );
		$weight_loss_goal = get_user_meta( $user_id, 'ennu_weight-loss_wl_q2', true );
		
		// Check if we have the required data
		if ( empty( $current_weight_data ) || empty( $weight_loss_goal ) ) {
			return false;
		}
		
		// Parse current weight from height_weight data
		$current_weight = self::extract_weight_from_data( $current_weight_data );
		if ( $current_weight === false ) {
			return false;
		}
		
		// Calculate target weight based on goal
		$target_weight = self::calculate_target_from_goal( $current_weight, $weight_loss_goal );
		if ( $target_weight === false ) {
			return false;
		}
		
		// Calculate weight loss amount
		$weight_loss_amount = $current_weight - $target_weight;
		
		return array(
			'current_weight' => $current_weight,
			'target_weight' => $target_weight,
			'weight_loss_amount' => $weight_loss_amount,
			'goal_type' => $weight_loss_goal,
			'calculation_date' => current_time( 'mysql' ),
		);
	}
	
	/**
	 * Extract weight from height_weight data
	 *
	 * @param mixed $height_weight_data Height/weight data from assessment
	 * @return float|false Weight in pounds or false if not found
	 */
	private static function extract_weight_from_data( $height_weight_data ) {
		
		// Handle different data formats
		if ( is_array( $height_weight_data ) ) {
			// Direct array format
			if ( isset( $height_weight_data['weight'] ) ) {
				return floatval( $height_weight_data['weight'] );
			}
			// Serialized format
			if ( isset( $height_weight_data['data'] ) && is_string( $height_weight_data['data'] ) ) {
				$data = maybe_unserialize( $height_weight_data['data'] );
				if ( is_array( $data ) && isset( $data['weight'] ) ) {
					return floatval( $data['weight'] );
				}
			}
		} elseif ( is_string( $height_weight_data ) ) {
			// Serialized string format
			$data = maybe_unserialize( $height_weight_data );
			if ( is_array( $data ) && isset( $data['weight'] ) ) {
				return floatval( $data['weight'] );
			}
		}
		
		return false;
	}
	
	/**
	 * Calculate target weight based on weight loss goal
	 *
	 * @param float $current_weight Current weight in pounds
	 * @param string $weight_loss_goal Weight loss goal from assessment
	 * @return float|false Target weight in pounds or false if invalid goal
	 */
	private static function calculate_target_from_goal( $current_weight, $weight_loss_goal ) {
		
		switch ( $weight_loss_goal ) {
			case 'lose_10_20':
				// Calculate average of 10-20 lbs loss (15 lbs)
				$weight_loss = 15;
				break;
				
			case 'lose_20_50':
				// Calculate average of 20-50 lbs loss (35 lbs)
				$weight_loss = 35;
				break;
				
			case 'lose_50_plus':
				// Calculate 50 lbs loss (minimum for 50+ category)
				$weight_loss = 50;
				break;
				
			case 'maintain':
				// No weight loss for maintenance
				$weight_loss = 0;
				break;
				
			default:
				return false;
		}
		
		// Calculate target weight
		$target_weight = $current_weight - $weight_loss;
		
		// Ensure target weight is reasonable (not negative or too low)
		if ( $target_weight < 80 ) {
			// Set minimum reasonable target weight
			$target_weight = 80;
		}
		
		return round( $target_weight, 1 );
	}
	
	/**
	 * Store target weight as biomarker data
	 *
	 * @param int $user_id User ID
	 * @param array $target_data Target weight calculation data
	 * @return bool Success status
	 */
	public static function store_target_weight( $user_id, $target_data ) {
		
		if ( empty( $target_data ) || ! is_array( $target_data ) ) {
			return false;
		}
		
		// Get existing biomarker data
		$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		if ( ! is_array( $biomarker_data ) ) {
			$biomarker_data = array();
		}
		
		// Add target weight to biomarker data
		$biomarker_data['weight'] = array(
			'current_value' => $target_data['current_weight'],
			'target_value' => $target_data['target_weight'],
			'unit' => 'lbs',
			'last_updated' => current_time( 'mysql' ),
			'source' => 'weight_loss_assessment',
			'notes' => sprintf( 
				'Target calculated from %s goal. Weight loss needed: %.1f lbs', 
				self::get_goal_description( $target_data['goal_type'] ),
				$target_data['weight_loss_amount']
			),
		);
		
		// Store updated biomarker data
		$result = update_user_meta( $user_id, 'ennu_biomarker_data', $biomarker_data );
		
		// Also store in auto-sync data for consistency
		$auto_sync_data = get_user_meta( $user_id, 'ennu_user_biomarkers', true );
		if ( ! is_array( $auto_sync_data ) ) {
			$auto_sync_data = array();
		}
		
		$auto_sync_data['weight'] = array(
			'value' => $target_data['current_weight'],
			'target' => $target_data['target_weight'],
			'unit' => 'lbs',
			'date' => current_time( 'mysql' ),
			'source' => 'weight_loss_assessment_target',
			'notes' => 'Target weight calculated from weight loss assessment',
		);
		
		update_user_meta( $user_id, 'ennu_user_biomarkers', $auto_sync_data );
		
		return $result;
	}
	
	/**
	 * Get human-readable goal description
	 *
	 * @param string $goal_type Goal type from assessment
	 * @return string Human-readable description
	 */
	private static function get_goal_description( $goal_type ) {
		$descriptions = array(
			'lose_10_20' => '10-20 lbs loss',
			'lose_20_50' => '20-50 lbs loss',
			'lose_50_plus' => '50+ lbs loss',
			'maintain' => 'weight maintenance',
		);
		
		return isset( $descriptions[ $goal_type ] ) ? $descriptions[ $goal_type ] : 'unknown goal';
	}
	
	/**
	 * Trigger target weight calculation when weight loss assessment is completed
	 *
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 */
	public static function trigger_calculation_on_assessment_completion( $user_id, $assessment_type ) {
		
		if ( $assessment_type !== 'weight-loss' ) {
			return;
		}
		
		// Calculate target weight
		$target_data = self::calculate_target_weight( $user_id );
		
		if ( $target_data ) {
			// Store the target weight
			self::store_target_weight( $user_id, $target_data );
			
			// Log the calculation
			error_log( "ENNU Target Weight: Calculated target weight for user {$user_id}: " . 
				"Current: {$target_data['current_weight']} lbs, " .
				"Target: {$target_data['target_weight']} lbs, " .
				"Goal: {$target_data['goal_type']}" );
		}
	}
} 