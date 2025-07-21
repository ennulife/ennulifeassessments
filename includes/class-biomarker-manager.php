<?php
/**
 * ENNU Life Biomarker Data Manager
 * Handles lab data import, storage, and doctor recommendations
 *
 * @package ENNU_Life
 * @version 62.1.67
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Biomarker_Manager {
    
    public static function import_lab_results( $user_id, $lab_data ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'insufficient_permissions', 'Insufficient permissions to import lab data' );
        }
        
        $validated_data = self::validate_lab_data( $lab_data );
        
        if ( is_wp_error( $validated_data ) ) {
            return $validated_data;
        }
        
        update_user_meta( $user_id, 'ennu_biomarker_data', $validated_data );
        update_user_meta( $user_id, 'ennu_lab_import_date', current_time( 'mysql' ) );
        
        ENNU_Assessment_Scoring::calculate_and_save_all_user_scores( $user_id, true );
        
        error_log( 'ENNU Biomarker Manager: Imported lab results for user ' . $user_id );
        
        return array(
            'success' => true,
            'biomarkers_imported' => count( $validated_data ),
            'import_date' => current_time( 'mysql' )
        );
    }
    
    public static function add_doctor_recommendations( $user_id, $recommendations ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'insufficient_permissions', 'Insufficient permissions to add recommendations' );
        }
        
        $existing_recommendations = get_user_meta( $user_id, 'ennu_doctor_recommendations', true );
        if ( ! is_array( $existing_recommendations ) ) {
            $existing_recommendations = array();
        }
        
        $new_recommendation = array(
            'date' => current_time( 'mysql' ),
            'doctor_id' => get_current_user_id(),
            'recommendations' => $recommendations,
            'biomarker_targets' => $recommendations['biomarker_targets'] ?? array(),
            'lifestyle_advice' => $recommendations['lifestyle_advice'] ?? '',
            'follow_up_date' => $recommendations['follow_up_date'] ?? null
        );
        
        $existing_recommendations[] = $new_recommendation;
        
        update_user_meta( $user_id, 'ennu_doctor_recommendations', $existing_recommendations );
        
        return array(
            'success' => true,
            'recommendation_id' => count( $existing_recommendations ) - 1
        );
    }
    
    public static function get_user_biomarkers( $user_id ) {
        return get_user_meta( $user_id, 'ennu_biomarker_data', true );
    }
    
    public static function get_doctor_recommendations( $user_id ) {
        return get_user_meta( $user_id, 'ennu_doctor_recommendations', true );
    }
    
    private static function validate_lab_data( $lab_data ) {
        if ( ! is_array( $lab_data ) ) {
            return new WP_Error( 'invalid_format', 'Lab data must be an array' );
        }
        
        $validated_data = array();
        
        foreach ( $lab_data as $biomarker_name => $biomarker_info ) {
            if ( ! is_array( $biomarker_info ) || ! isset( $biomarker_info['value'] ) ) {
                continue;
            }
            
            $validated_data[$biomarker_name] = array(
                'value' => floatval( $biomarker_info['value'] ),
                'unit' => sanitize_text_field( $biomarker_info['unit'] ?? '' ),
                'reference_range' => sanitize_text_field( $biomarker_info['reference_range'] ?? '' ),
                'test_date' => sanitize_text_field( $biomarker_info['test_date'] ?? current_time( 'mysql' ) ),
                'lab_name' => sanitize_text_field( $biomarker_info['lab_name'] ?? '' )
            );
        }
        
        return $validated_data;
    }
    
    public static function calculate_new_life_score_projection( $user_id ) {
        $current_biomarkers = self::get_user_biomarkers( $user_id );
        $doctor_recommendations = self::get_doctor_recommendations( $user_id );
        
        if ( empty( $current_biomarkers ) || empty( $doctor_recommendations ) ) {
            return null;
        }
        
        $latest_recommendations = end( $doctor_recommendations );
        $biomarker_targets = $latest_recommendations['biomarker_targets'] ?? array();
        
        $projected_biomarkers = $current_biomarkers;
        foreach ( $biomarker_targets as $biomarker_name => $target_value ) {
            if ( isset( $projected_biomarkers[$biomarker_name] ) ) {
                $projected_biomarkers[$biomarker_name]['value'] = $target_value;
            }
        }
        
        if ( class_exists( 'ENNU_Objective_Engine' ) ) {
            $current_scores = get_user_meta( $user_id, 'ennu_average_pillar_scores', true );
            
            $objective_engine = new ENNU_Objective_Engine( $projected_biomarkers );
            $projected_scores = $objective_engine->apply_biomarker_actuality_adjustments( $current_scores );
            
            $projected_life_score = array_sum( $projected_scores ) / count( $projected_scores );
            
            return array(
                'projected_pillar_scores' => $projected_scores,
                'projected_life_score' => $projected_life_score,
                'improvement_potential' => $projected_life_score - (get_user_meta( $user_id, 'ennu_life_score', true ) ?? 0)
            );
        }
        
        return null;
    }
    
    public static function get_biomarker_recommendations( $user_id ) {
        $user_symptoms = ENNU_Assessment_Scoring::get_symptom_data_for_user( $user_id );
        $biomarker_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/biomarker-map.php';
        
        if ( ! file_exists( $biomarker_map_file ) ) {
            return array();
        }
        
        $biomarker_map = require $biomarker_map_file;
        $recommended_biomarkers = array();
        
        $all_symptoms = array();
        foreach ( $user_symptoms as $assessment_type => $symptoms ) {
            if ( is_array( $symptoms ) ) {
                $all_symptoms = array_merge( $all_symptoms, $symptoms );
            }
        }
        
        $symptom_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/symptom-map.php';
        if ( file_exists( $symptom_map_file ) ) {
            $symptom_map = require $symptom_map_file;
            
            foreach ( $all_symptoms as $symptom ) {
                if ( isset( $symptom_map[$symptom] ) ) {
                    foreach ( $symptom_map[$symptom] as $category => $weight_data ) {
                        if ( isset( $biomarker_map[$category] ) ) {
                            $recommended_biomarkers = array_merge( $recommended_biomarkers, $biomarker_map[$category] );
                        }
                    }
                }
            }
        }
        
        return array_unique( $recommended_biomarkers );
    }
}
