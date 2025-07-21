<?php
/**
 * Assessment Definition: Health Optimization Assessment
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

return array(
    'title' => 'Health Optimization Assessment',
    'assessment_engine' => 'qualitative',
    'questions' => array(
        'symptom_q1' => array(
            'title' => 'Please select any symptoms you are experiencing related to Heart Health.',
            'type' => 'multiselect',
            'options' => array(
                'Chest Pain' => 'Chest Pain',
                'Shortness of Breath' => 'Shortness of Breath',
                'Palpitations' => 'Palpitations',
                'Lightheadedness' => 'Lightheadedness',
                'Swelling' => 'Swelling',
                'Poor Exercise Tolerance' => 'Poor Exercise Tolerance',
            ),
            'required' => false,
        ),
        'symptom_q1_severity' => array(
            'title' => 'How severe are your Heart Health symptoms?',
            'type' => 'radio',
            'options' => array( 'Mild' => 'Mild', 'Moderate' => 'Moderate', 'Severe' => 'Severe' ),
            'required' => false,
        ),
        'symptom_q1_frequency' => array(
            'title' => 'How often do you experience these symptoms?',
            'type' => 'radio',
            'options' => array( 'Daily' => 'Daily', 'Weekly' => 'Weekly', 'Monthly' => 'Monthly' ),
            'required' => false,
        ),
        'symptom_q2' => array(
            'title' => 'Please select any symptoms you are experiencing related to Cognitive Health.',
            'type' => 'multiselect',
            'options' => array(
                'Brain Fog' => 'Brain Fog',
                'Memory Loss' => 'Memory Loss',
                'Poor Concentration' => 'Poor Concentration',
            ),
            'required' => false,
        ),
        'symptom_q2_severity' => array(
            'title' => 'How severe are your Cognitive Health symptoms?',
            'type' => 'radio',
            'options' => array( 'Mild' => 'Mild', 'Moderate' => 'Moderate', 'Severe' => 'Severe' ),
            'required' => false,
        ),
        'symptom_q2_frequency' => array(
            'title' => 'How often do you experience these symptoms?',
            'type' => 'radio',
            'options' => array( 'Daily' => 'Daily', 'Weekly' => 'Weekly', 'Monthly' => 'Monthly' ),
            'required' => false,
        ),
    ),
); 