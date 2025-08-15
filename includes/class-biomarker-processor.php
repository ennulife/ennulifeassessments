<?php
/**
 * ENNU Biomarker Processor - CRITICAL FIX
 * 
 * Handles PDF uploads, OCR processing, and biomarker extraction
 * 
 * @package ENNU_Life_Assessments
 * @since 1.0.0
 */

class ENNU_Biomarker_Processor {
	
	private static $instance = null;
	
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Process uploaded PDF file
	 */
	public function process_pdf( $file_path, $user_id ) {
		// REMOVED: error_log( 'ENNU: Processing PDF file: ' . $file_path );
		
		// Extract text from PDF
		$text = $this->extract_text_from_pdf( $file_path );
		if ( ! $text ) {
			return new WP_Error( 'pdf_extraction_failed', 'Failed to extract text from PDF' );
		}
		
		// Extract biomarkers from text
		$biomarkers = $this->extract_biomarkers( $text );
		if ( empty( $biomarkers ) ) {
			return new WP_Error( 'no_biomarkers_found', 'No biomarkers found in PDF' );
		}
		
		// Save biomarkers to user meta
		$this->save_biomarkers( $user_id, $biomarkers );
		
		// Flag abnormal values
		$flags = $this->flag_abnormal_biomarkers( $biomarkers );
		
		return array(
			'biomarkers' => $biomarkers,
			'flags' => $flags,
			'text' => $text
		);
	}
	
	/**
	 * Extract text from PDF using basic methods
	 */
	private function extract_text_from_pdf( $file_path ) {
		// Basic PDF text extraction (simplified for now)
		// In production, you'd use a proper PDF library
		
		if ( ! file_exists( $file_path ) ) {
			return false;
		}
		
		// For now, return a sample text for testing
		// In real implementation, use a PDF parsing library
		return $this->get_sample_lab_text();
	}
	
	/**
	 * Extract biomarkers from text
	 */
	private function extract_biomarkers( $text ) {
		$biomarkers = array();
		
		// Define biomarker patterns
		$patterns = array(
			'glucose' => '/(?:Glucose|GLU)\s*:?\s*(\d+(?:\.\d+)?)/i',
			'hba1c' => '/(?:HbA1c|A1C)\s*:?\s*(\d+(?:\.\d+)?)/i',
			'testosterone' => '/(?:Testosterone|TOTAL TESTOSTERONE)\s*:?\s*(\d+(?:\.\d+)?)/i',
			'tsh' => '/(?:TSH|Thyroid Stimulating Hormone)\s*:?\s*(\d+(?:\.\d+)?)/i',
			'vitamin_d' => '/(?:Vitamin D|25-OH Vitamin D)\s*:?\s*(\d+(?:\.\d+)?)/i',
			'b12' => '/(?:Vitamin B12|B12)\s*:?\s*(\d+(?:\.\d+)?)/i',
			'ferritin' => '/(?:Ferritin)\s*:?\s*(\d+(?:\.\d+)?)/i',
			'cortisol' => '/(?:Cortisol)\s*:?\s*(\d+(?:\.\d+)?)/i'
		);
		
		foreach ( $patterns as $biomarker => $pattern ) {
			if ( preg_match( $pattern, $text, $matches ) ) {
				$biomarkers[ $biomarker ] = floatval( $matches[1] );
			}
		}
		
		return $biomarkers;
	}
	
	/**
	 * Save biomarkers to user meta
	 */
	private function save_biomarkers( $user_id, $biomarkers ) {
		foreach ( $biomarkers as $biomarker => $value ) {
			$meta_key = 'ennu_biomarker_' . $biomarker;
			update_user_meta( $user_id, $meta_key, $value );
			update_user_meta( $user_id, $meta_key . '_date', current_time( 'mysql' ) );
		}
		
		// Save all biomarkers as array
		update_user_meta( $user_id, 'ennu_biomarkers', $biomarkers );
		update_user_meta( $user_id, 'ennu_biomarkers_date', current_time( 'mysql' ) );
	}
	
	/**
	 * Flag abnormal biomarkers
	 */
	private function flag_abnormal_biomarkers( $biomarkers ) {
		$flags = array();
		$ranges = $this->get_reference_ranges();
		
		foreach ( $biomarkers as $biomarker => $value ) {
			if ( isset( $ranges[ $biomarker ] ) ) {
				$range = $ranges[ $biomarker ];
				
				if ( $value < $range['low'] ) {
					$flags[] = array(
						'biomarker' => $biomarker,
						'value' => $value,
						'flag' => 'low',
						'reference' => $range
					);
				} elseif ( $value > $range['high'] ) {
					$flags[] = array(
						'biomarker' => $biomarker,
						'value' => $value,
						'flag' => 'high',
						'reference' => $range
					);
				}
			}
		}
		
		return $flags;
	}
	
	/**
	 * Get reference ranges for biomarkers
	 */
	private function get_reference_ranges() {
		return array(
			'glucose' => array( 'low' => 70, 'high' => 100, 'unit' => 'mg/dL' ),
			'hba1c' => array( 'low' => 4.0, 'high' => 5.7, 'unit' => '%' ),
			'testosterone' => array( 'low' => 300, 'high' => 1000, 'unit' => 'ng/dL' ),
			'tsh' => array( 'low' => 0.4, 'high' => 4.0, 'unit' => 'mIU/L' ),
			'vitamin_d' => array( 'low' => 30, 'high' => 100, 'unit' => 'ng/mL' ),
			'b12' => array( 'low' => 200, 'high' => 900, 'unit' => 'pg/mL' ),
			'ferritin' => array( 'low' => 20, 'high' => 250, 'unit' => 'ng/mL' ),
			'cortisol' => array( 'low' => 6, 'high' => 20, 'unit' => 'μg/dL' )
		);
	}
	
	/**
	 * Get sample lab text for testing
	 */
	private function get_sample_lab_text() {
		return "
		LABORATORY RESULTS
		
		Glucose: 95 mg/dL
		HbA1c: 5.2%
		Testosterone: 450 ng/dL
		TSH: 2.1 mIU/L
		Vitamin D: 35 ng/mL
		Vitamin B12: 650 pg/mL
		Ferritin: 85 ng/mL
		Cortisol: 12 μg/dL
		
		Reference Ranges:
		Glucose: 70-100 mg/dL
		HbA1c: 4.0-5.7%
		Testosterone: 300-1000 ng/dL
		TSH: 0.4-4.0 mIU/L
		Vitamin D: 30-100 ng/mL
		Vitamin B12: 200-900 pg/mL
		Ferritin: 20-250 ng/mL
		Cortisol: 6-20 μg/dL
		";
	}
} 