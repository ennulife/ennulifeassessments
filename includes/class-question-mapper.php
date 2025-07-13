<?php
/**
 * ENNU Life Question Mapper
 * 
 * Maps form question IDs (e.g., hair_q1) to their semantic meaning
 * for use in the scoring system (e.g., 'gender').
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Question_Mapper {
    
    /**
     * Maps a simple form question ID to its semantic key for a given assessment.
     */
    public static function get_semantic_key($assessment_type, $question_key) {
        $assessment_prefix = str_replace('_assessment', '', $assessment_type);
        $questions_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessment-questions.php';
        
        if ( !file_exists($questions_file) ) {
            return null;
        }

        $all_questions = require $questions_file;
        $assessment_questions = $all_questions[$assessment_type] ?? array();

        foreach($assessment_questions as $index => $question_def) {
            $simple_id = $assessment_prefix . '_q' . ($index + 1);
            if ($simple_id === $question_key) {
                // Return the semantic key, which is the key used in the scoring config
                return self::get_key_from_question_def($question_def);
            }
        }
        
        return null;
    }

    /**
     * Maps a semantic key back to its simple form question ID for a given assessment.
     */
    public static function get_simple_id_from_semantic_key($assessment_type, $semantic_key) {
        $assessment_prefix = str_replace('_assessment', '', $assessment_type);
        $questions_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessment-questions.php';
        
        if ( !file_exists($questions_file) ) {
            return null;
        }

        $all_questions = require $questions_file;
        $assessment_questions = $all_questions[$assessment_type] ?? array();

        foreach($assessment_questions as $index => $question_def) {
            $current_semantic_key = self::get_key_from_question_def($question_def);
            if ($current_semantic_key === $semantic_key) {
                return $assessment_prefix . '_q' . ($index + 1);
            }
        }
        
        return null;
    }

    /**
     * Helper to determine the semantic key from a question definition.
     * For now, this is simple but could be expanded.
     */
    private static function get_key_from_question_def($question_def) {
        if (isset($question_def['scoring_key'])) {
            return $question_def['scoring_key'];
        }
        if (isset($question_def['global_key'])) {
            return $question_def['global_key'];
        }
        // Fallback or more complex logic could go here.
        // For now, we assume a simple mapping will be added to configs.
        return null;
    }
}
