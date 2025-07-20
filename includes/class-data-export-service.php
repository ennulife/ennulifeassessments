<?php
/**
 * ENNU Data Export Service
 * Extracted from monolithic Enhanced Admin class
 *
 * @package ENNU_Life
 * @version 62.1.67
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Data_Export_Service {
    
    private $user_manager;
    private $analytics_service;
    
    public function __construct() {
        $this->user_manager = new ENNU_User_Manager();
        $this->analytics_service = new ENNU_Analytics_Service();
    }
    
    /**
     * Export user data to CSV
     */
    public function export_user_data( $user_ids = array(), $format = 'csv' ) {
        if ( empty( $user_ids ) ) {
            $user_ids = $this->get_all_active_user_ids();
        }
        
        $data = array();
        $headers = array(
            'User ID', 'Username', 'Email', 'Registration Date',
            'Total Assessments', 'Completion Rate', 'Health Goals',
            'ENNU Life Score', 'Mind Pillar', 'Body Pillar', 'Lifestyle Pillar', 'Aesthetics Pillar'
        );
        
        foreach ( $user_ids as $user_id ) {
            $user = get_user_by( 'id', $user_id );
            if ( ! $user ) continue;
            
            $stats = $this->user_manager->get_user_stats( $user_id );
            $global_data = $this->user_manager->get_user_global_data( $user_id );
            $scores = $this->get_user_scores( $user_id );
            
            $data[] = array(
                $user_id,
                $user->user_login,
                $user->user_email,
                $user->user_registered,
                $stats['total_assessments'],
                $stats['completion_rate'] . '%',
                isset( $global_data['ennu_global_health_goals'] ) ? implode( '; ', $global_data['ennu_global_health_goals'] ) : '',
                $scores['life_score'] ?? 0,
                $scores['mind_pillar'] ?? 0,
                $scores['body_pillar'] ?? 0,
                $scores['lifestyle_pillar'] ?? 0,
                $scores['aesthetics_pillar'] ?? 0
            );
        }
        
        return $this->format_export_data( $headers, $data, $format );
    }
    
    /**
     * Export system analytics
     */
    public function export_analytics( $format = 'csv' ) {
        $report = $this->analytics_service->generate_report();
        
        $headers = array( 'Metric', 'Value', 'Description' );
        $data = array();
        
        $data[] = array( 'Total Users', $report['summary']['total_users'], 'Total registered users' );
        $data[] = array( 'Active Users', $report['summary']['active_users'], 'Users with at least one assessment' );
        $data[] = array( 'Engagement Rate', $report['summary']['engagement_rate'] . '%', 'Percentage of active users' );
        $data[] = array( 'Monthly Assessments', $report['summary']['monthly_assessments'], 'Assessments completed this month' );
        
        foreach ( $report['detailed_stats']['completion_rates'] as $assessment => $rate ) {
            $data[] = array( ucwords( str_replace( '_', ' ', $assessment ) ) . ' Completion', $rate . '%', 'Completion rate for ' . $assessment );
        }
        
        return $this->format_export_data( $headers, $data, $format );
    }
    
    /**
     * Export assessment data
     */
    public function export_assessment_data( $assessment_type = '', $format = 'csv' ) {
        global $wpdb;
        
        $where_clause = '';
        $params = array();
        
        if ( ! empty( $assessment_type ) ) {
            $where_clause = 'AND um.meta_key = %s';
            $params[] = 'ennu_' . $assessment_type . '_calculated_score';
        } else {
            $where_clause = 'AND um.meta_key LIKE %s';
            $params[] = 'ennu_%_calculated_score';
        }
        
        $query = $wpdb->prepare(
            "SELECT u.ID, u.user_login, u.user_email, u.user_registered,
                    um.meta_key, um.meta_value as score,
                    REPLACE(REPLACE(um.meta_key, 'ennu_', ''), '_calculated_score', '') as assessment_type
             FROM {$wpdb->usermeta} um 
             JOIN {$wpdb->users} u ON um.user_id = u.ID 
             WHERE um.meta_value != '' {$where_clause}
             ORDER BY u.ID, um.meta_key",
            $params
        );
        
        $results = $wpdb->get_results( $query );
        
        $headers = array( 'User ID', 'Username', 'Email', 'Registration Date', 'Assessment Type', 'Score' );
        $data = array();
        
        foreach ( $results as $result ) {
            $data[] = array(
                $result->ID,
                $result->user_login,
                $result->user_email,
                $result->user_registered,
                ucwords( str_replace( '_', ' ', $result->assessment_type ) ),
                $result->score
            );
        }
        
        return $this->format_export_data( $headers, $data, $format );
    }
    
    /**
     * Get all active user IDs
     */
    private function get_all_active_user_ids() {
        global $wpdb;
        
        $user_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT DISTINCT user_id FROM {$wpdb->usermeta} 
             WHERE meta_key LIKE %s AND meta_value != ''",
            'ennu_%_calculated_score'
        ) );
        
        return array_map( 'intval', $user_ids );
    }
    
    /**
     * Get user scores
     */
    private function get_user_scores( $user_id ) {
        return array(
            'life_score' => get_user_meta( $user_id, 'ennu_life_score', true ),
            'mind_pillar' => get_user_meta( $user_id, 'ennu_mind_pillar_score', true ),
            'body_pillar' => get_user_meta( $user_id, 'ennu_body_pillar_score', true ),
            'lifestyle_pillar' => get_user_meta( $user_id, 'ennu_lifestyle_pillar_score', true ),
            'aesthetics_pillar' => get_user_meta( $user_id, 'ennu_aesthetics_pillar_score', true )
        );
    }
    
    /**
     * Format export data
     */
    private function format_export_data( $headers, $data, $format ) {
        switch ( $format ) {
            case 'json':
                return $this->format_as_json( $headers, $data );
            case 'xml':
                return $this->format_as_xml( $headers, $data );
            case 'csv':
            default:
                return $this->format_as_csv( $headers, $data );
        }
    }
    
    /**
     * Format as CSV
     */
    private function format_as_csv( $headers, $data ) {
        $output = fopen( 'php://temp', 'r+' );
        
        fputcsv( $output, $headers );
        
        foreach ( $data as $row ) {
            fputcsv( $output, $row );
        }
        
        rewind( $output );
        $csv_content = stream_get_contents( $output );
        fclose( $output );
        
        return array(
            'content' => $csv_content,
            'filename' => 'ennu-export-' . date( 'Y-m-d-H-i-s' ) . '.csv',
            'mime_type' => 'text/csv'
        );
    }
    
    /**
     * Format as JSON
     */
    private function format_as_json( $headers, $data ) {
        $formatted_data = array();
        
        foreach ( $data as $row ) {
            $formatted_data[] = array_combine( $headers, $row );
        }
        
        return array(
            'content' => wp_json_encode( $formatted_data, JSON_PRETTY_PRINT ),
            'filename' => 'ennu-export-' . date( 'Y-m-d-H-i-s' ) . '.json',
            'mime_type' => 'application/json'
        );
    }
    
    /**
     * Format as XML
     */
    private function format_as_xml( $headers, $data ) {
        $xml = new SimpleXMLElement( '<export/>' );
        $xml->addAttribute( 'generated', date( 'Y-m-d H:i:s' ) );
        
        foreach ( $data as $row ) {
            $record = $xml->addChild( 'record' );
            foreach ( $headers as $index => $header ) {
                $field = $record->addChild( 'field', htmlspecialchars( $row[ $index ] ?? '' ) );
                $field->addAttribute( 'name', $header );
            }
        }
        
        return array(
            'content' => $xml->asXML(),
            'filename' => 'ennu-export-' . date( 'Y-m-d-H-i-s' ) . '.xml',
            'mime_type' => 'application/xml'
        );
    }
}
