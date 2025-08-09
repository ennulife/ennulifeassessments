<?php
require_once('/Applications/MAMP/htdocs/wp-load.php');
global $wpdb;

// Check and create missing tables
$tables = [
    'ennu_assessment_history' => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ennu_assessment_history (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        user_id bigint(20) unsigned NOT NULL,
        assessment_type varchar(50) NOT NULL,
        assessment_data longtext,
        scores longtext,
        completed_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_assessment (user_id, assessment_type),
        KEY completed_at (completed_at)
    ) {$wpdb->get_charset_collate()}",
    
    'ennu_score_cache' => "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ennu_score_cache (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        user_id bigint(20) unsigned NOT NULL,
        assessment_type varchar(50) NOT NULL,
        cache_key varchar(255) NOT NULL,
        score_data longtext,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        expires_at datetime DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY cache_key (cache_key),
        KEY user_assessment (user_id, assessment_type),
        KEY expires_at (expires_at)
    ) {$wpdb->get_charset_collate()}"
];

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

foreach ($tables as $name => $sql) {
    $table_name = $wpdb->prefix . $name;
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        dbDelta($sql);
        echo "Created table: $table_name\n";
    } else {
        echo "Table exists: $table_name\n";
    }
}

echo "\nAll tables verified!\n";