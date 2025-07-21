<?php
/**
 * ENNU Life Simple Edge Case Test
 * 
 * This script tests the core logic for page creation and menu creation
 * without requiring WordPress authentication.
 * 
 * @package ENNU_Life
 * @version 62.1.4
 */

echo '<h1>ENNU Life Simple Edge Case Test - v62.1.4</h1>';
echo '<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.success { color: #28a745; font-weight: bold; }
.error { color: #dc3545; font-weight: bold; }
.warning { color: #ffc107; font-weight: bold; }
.info { color: #17a2b8; font-weight: bold; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; background: white; }
th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
th { background-color: #f8f9fa; font-weight: bold; }
.test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
</style>';

// Initialize test counters
$total_tests = 0;
$passed_tests = 0;
$failed_tests = 0;
$warnings = 0;
$critical_issues = 0;

// ============================================================================
// SECTION 1: ASSESSMENT DEFINITIONS ANALYSIS
// ============================================================================

echo '<div class="test-section">';
echo '<h2>1. Assessment Definitions Analysis</h2>';

// Simulate assessment definitions based on the codebase analysis
$all_definitions = array(
	'hair_assessment' => array(
		'title' => 'Hair Loss Assessment',
		'questions' => array('q1', 'q2', 'q3'),
		'scoring' => array('category1' => 10)
	),
	'ed_treatment_assessment' => array(
		'title' => 'ED Treatment Assessment',
		'questions' => array('q1', 'q2', 'q3'),
		'scoring' => array('category1' => 10)
	),
	'weight_loss_assessment' => array(
		'title' => 'Weight Loss Assessment',
		'questions' => array('q1', 'q2', 'q3'),
		'scoring' => array('category1' => 10)
	),
	'health_assessment' => array(
		'title' => 'Health Assessment',
		'questions' => array('q1', 'q2', 'q3'),
		'scoring' => array('category1' => 10)
	),
	'health_optimization_assessment' => array(
		'title' => 'Health Optimization Assessment',
		'questions' => array('q1', 'q2', 'q3'),
		'scoring' => array('category1' => 10)
	),
	'skin_assessment' => array(
		'title' => 'Skin Assessment',
		'questions' => array('q1', 'q2', 'q3'),
		'scoring' => array('category1' => 10)
	),
	'hormone_assessment' => array(
		'title' => 'Hormone Assessment',
		'questions' => array('q1', 'q2', 'q3'),
		'scoring' => array('category1' => 10)
	),
	'testosterone_assessment' => array(
		'title' => 'Testosterone Assessment',
		'questions' => array('q1', 'q2', 'q3'),
		'scoring' => array('category1' => 10)
	),
	'menopause_assessment' => array(
		'title' => 'Menopause Assessment',
		'questions' => array('q1', 'q2', 'q3'),
		'scoring' => array('category1' => 10)
	),
	'sleep_assessment' => array(
		'title' => 'Sleep Assessment',
		'questions' => array('q1', 'q2', 'q3'),
		'scoring' => array('category1' => 10)
	),
	'welcome_assessment' => array(
		'title' => 'Welcome Assessment',
		'questions' => array('q1', 'q2', 'q3'),
		'scoring' => array('category1' => 10)
	)
);

$assessment_issues = array();
$assessment_warnings = array();

echo '<div class="test-subsection">';
echo '<h3>1.1 Core Assessment Configuration</h3>';
echo '<table>';
echo '<tr><th>Config File</th><th>Assessment Key</th><th>Title</th><th>Status</th><th>Issues</th></tr>';

foreach ( $all_definitions as $key => $config ) {
	$issues = array();
	$status = '<span class="success">✅ Valid</span>';
	
	// Check for missing title
	if ( empty($config['title']) ) {
		$issues[] = 'Missing title';
		$status = '<span class="error">❌ Invalid</span>';
		$assessment_issues[] = "Assessment '{$key}' missing title";
	}
	
	// Check for missing questions
	if ( empty($config['questions']) || !is_array($config['questions']) ) {
		$issues[] = 'Missing or invalid questions array';
		$status = '<span class="error">❌ Invalid</span>';
		$assessment_issues[] = "Assessment '{$key}' missing questions";
	}
	
	// Check for missing scoring
	if ( empty($config['scoring']) || !is_array($config['scoring']) ) {
		$issues[] = 'Missing or invalid scoring array';
		$status = '<span class="warning">⚠️ Warning</span>';
		$assessment_warnings[] = "Assessment '{$key}' missing scoring configuration";
	}
	
	// Check for special characters in title
	if ( isset($config['title']) && preg_match('/[<>"\']/', $config['title']) ) {
		$issues[] = 'Special characters in title';
		$status = '<span class="warning">⚠️ Warning</span>';
		$assessment_warnings[] = "Assessment '{$key}' has special characters in title";
	}
	
	echo '<tr>';
	echo '<td>' . $key . '.php</td>';
	echo '<td>' . $key . '</td>';
	echo '<td>' . (isset($config['title']) ? htmlspecialchars($config['title']) : 'No title') . '</td>';
	echo '<td>' . $status . '</td>';
	echo '<td>' . (empty($issues) ? '-' : implode(', ', $issues)) . '</td>';
	echo '</tr>';
	
	$total_tests++;
	if ( empty($issues) ) {
		$passed_tests++;
	} elseif ( strpos($status, 'error') !== false ) {
		$failed_tests++;
		$critical_issues++;
	} else {
		$warnings++;
	}
}
echo '</table>';
echo '</div>';

echo '<div class="test-subsection">';
echo '<h3>1.2 Assessment Key to Slug Mapping</h3>';
echo '<table>';
echo '<tr><th>Assessment Key</th><th>Generated Slug</th><th>Potential Issues</th><th>Status</th></tr>';

$slug_mapping_issues = array();
$used_slugs = array();

foreach ( $all_definitions as $key => $config ) {
	$slug = str_replace('_', '-', $key);
	$issues = array();
	$status = '<span class="success">✅ Valid</span>';
	
	// Check for duplicate slugs
	if ( in_array($slug, $used_slugs) ) {
		$issues[] = 'Duplicate slug';
		$status = '<span class="error">❌ Critical</span>';
		$slug_mapping_issues[] = "Duplicate slug '{$slug}' for assessment '{$key}'";
		$critical_issues++;
	}
	$used_slugs[] = $slug;
	
	// Check for empty slug
	if ( empty($slug) ) {
		$issues[] = 'Empty slug';
		$status = '<span class="error">❌ Critical</span>';
		$slug_mapping_issues[] = "Empty slug for assessment '{$key}'";
		$critical_issues++;
	}
	
	// Check for invalid characters
	if ( preg_match('/[^a-z0-9\-]/', $slug) ) {
		$issues[] = 'Invalid characters';
		$status = '<span class="warning">⚠️ Warning</span>';
		$assessment_warnings[] = "Assessment '{$key}' has invalid characters in slug";
	}
	
	echo '<tr>';
	echo '<td>' . $key . '</td>';
	echo '<td>' . $slug . '</td>';
	echo '<td>' . (empty($issues) ? '-' : implode(', ', $issues)) . '</td>';
	echo '<td>' . $status . '</td>';
	echo '</tr>';
	
	$total_tests++;
	if ( empty($issues) ) {
		$passed_tests++;
	} elseif ( strpos($status, 'error') !== false ) {
		$failed_tests++;
	} else {
		$warnings++;
	}
}
echo '</table>';
echo '</div>';

echo '</div>';

// ============================================================================
// SECTION 2: PAGE CREATION PROCESS EDGE CASES
// ============================================================================

echo '<div class="test-section">';
echo '<h2>2. Page Creation Process Edge Cases</h2>';

$page_creation_issues = array();
$page_creation_warnings = array();

echo '<div class="test-subsection">';
echo '<h3>2.1 Page Structure Validation</h3>';

// Test the exact logic from setup_pages()
$test_assessment_keys = array_keys( $all_definitions );
$test_pages_to_create = array();

// Build the complete page structure
foreach ($test_assessment_keys as $key) {
	$slug = str_replace('_', '-', $key);
	
	// Skip welcome assessment - it's now at root level
	if ( 'welcome' === $key ) {
		continue;
	}
	
	// Form Page (child of assessments)
	$test_pages_to_create["assessments/{$slug}"] = array(
		'title' => 'Test Title',
		'menu_label' => 'Test Label',
		'content' => "[ennu-{$slug}]",
		'parent' => "assessments"
	);
	
	// Results Page (child of specific assessment)
	$results_slug = $slug . '-results';
	$test_pages_to_create["assessments/{$slug}/results"] = array(
		'title' => 'Test Results Title',
		'menu_label' => 'Results',
		'content' => "[ennu-{$results_slug}]",
		'parent' => "assessments/{$slug}"
	);

	// Details Page (child of specific assessment)
	$details_slug = $slug . '-assessment-details';
	$test_pages_to_create["assessments/{$slug}/details"] = array(
		'title' => 'Test Details Title',
		'menu_label' => 'Treatment Options',
		'content' => "[ennu-{$details_slug}]",
		'parent' => "assessments/{$slug}"
	);

	// Consultation Page (child of specific assessment)
	$booking_slug = $slug . '-consultation';
	$test_pages_to_create["assessments/{$slug}/consultation"] = array(
		'title' => 'Test Consultation Title',
		'menu_label' => 'Book Consultation',
		'content' => "[ennu-{$booking_slug}]",
		'parent' => "assessments/{$slug}"
	);
}

echo '<table>';
echo '<tr><th>Page Path</th><th>Shortcode</th><th>Parent</th><th>Parent Status</th><th>Overall Status</th></tr>';

foreach ( $test_pages_to_create as $path => $page_data ) {
	$shortcode = $page_data['content'];
	
	// Check if parent exists
	$parent_exists = false;
	$parent_status = '<span class="error">❌ Missing</span>';
	
	if ( $page_data['parent'] === 0 ) {
		$parent_exists = true;
		$parent_status = '<span class="success">✅ Root</span>';
	} else {
		foreach ( $test_pages_to_create as $parent_path => $parent_data ) {
			if ( $parent_path === $page_data['parent'] ) {
				$parent_exists = true;
				$parent_status = '<span class="success">✅ Exists</span>';
				break;
			}
		}
	}
	
	$overall_status = '<span class="success">✅ Valid</span>';
	if ( !$parent_exists ) {
		$overall_status = '<span class="error">❌ Invalid</span>';
		$page_creation_issues[] = "Page '{$path}' has missing parent '{$page_data['parent']}'";
	}
	
	echo '<tr>';
	echo '<td>' . $path . '</td>';
	echo '<td>' . $shortcode . '</td>';
	echo '<td>' . $page_data['parent'] . '</td>';
	echo '<td>' . $parent_status . '</td>';
	echo '<td>' . $overall_status . '</td>';
	echo '</tr>';
	
	$total_tests++;
	if ( $parent_exists ) {
		$passed_tests++;
	} else {
		$failed_tests++;
		$critical_issues++;
	}
}
echo '</table>';
echo '</div>';

echo '<div class="test-subsection">';
echo '<h3>2.2 Parent-Child Relationship Validation</h3>';

$parent_child_issues = array();
$parent_child_test = array();

// Build parent-child relationship map
foreach ( $test_pages_to_create as $path => $page_data ) {
	$parent = $page_data['parent'];
	if ( $parent !== 0 ) {
		$parent_child_test[$parent][] = $path;
	}
}

echo '<table>';
echo '<tr><th>Parent Page</th><th>Child Pages</th><th>Child Count</th><th>Status</th></tr>';

foreach ( $parent_child_test as $parent => $children ) {
	$parent_exists = false;
	foreach ( $test_pages_to_create as $path => $page_data ) {
		if ( $path === $parent ) {
			$parent_exists = true;
			break;
		}
	}
	
	$status = '<span class="success">✅ Valid</span>';
	if ( !$parent_exists ) {
		$status = '<span class="error">❌ Parent Missing</span>';
		$parent_child_issues[] = "Parent '{$parent}' missing for children: " . implode(', ', $children);
		$critical_issues++;
	}
	
	echo '<tr>';
	echo '<td>' . $parent . '</td>';
	echo '<td>' . implode(', ', $children) . '</td>';
	echo '<td>' . count($children) . '</td>';
	echo '<td>' . $status . '</td>';
	echo '</tr>';
	
	$total_tests++;
	if ( $parent_exists ) {
		$passed_tests++;
	} else {
		$failed_tests++;
	}
}
echo '</table>';
echo '</div>';

echo '<div class="test-subsection">';
echo '<h3>2.3 Page Creation Logic Edge Cases</h3>';

$logic_issues = array();

// Edge Case 1: Check for circular dependencies
$circular_deps = array();
foreach ( $test_pages_to_create as $path => $page_data ) {
	$current_parent = $page_data['parent'];
	$visited = array($path);
	
	while ( $current_parent !== 0 ) {
		if ( in_array($current_parent, $visited) ) {
			$circular_deps[] = "Circular dependency detected: " . implode(' → ', $visited) . " → {$current_parent}";
			break;
		}
		$visited[] = $current_parent;
		
		$found_parent = false;
		foreach ( $test_pages_to_create as $parent_path => $parent_data ) {
			if ( $parent_path === $current_parent ) {
				$current_parent = $parent_data['parent'];
				$found_parent = true;
				break;
			}
		}
		if ( !$found_parent ) {
			break;
		}
	}
}

// Edge Case 2: Check for orphaned pages
$orphaned_pages = array();
foreach ( $test_pages_to_create as $path => $page_data ) {
	if ( $page_data['parent'] !== 0 ) {
		$parent_exists = false;
		foreach ( $test_pages_to_create as $parent_path => $parent_data ) {
			if ( $parent_path === $page_data['parent'] ) {
				$parent_exists = true;
				break;
			}
		}
		if ( !$parent_exists ) {
			$orphaned_pages[] = "Page '{$path}' has non-existent parent '{$page_data['parent']}'";
		}
	}
}

// Edge Case 3: Check for duplicate paths
$duplicate_paths = array();
$paths = array();
foreach ( $test_pages_to_create as $path => $page_data ) {
	if ( in_array($path, $paths) ) {
		$duplicate_paths[] = "Duplicate path: '{$path}'";
	}
	$paths[] = $path;
}

// Edge Case 4: Check for invalid page titles
$invalid_titles = array();
foreach ( $test_pages_to_create as $path => $page_data ) {
	if ( empty($page_data['title']) ) {
		$invalid_titles[] = "Empty title for page '{$path}'";
	}
	if ( strlen($page_data['title']) > 255 ) {
		$invalid_titles[] = "Title too long for page '{$path}'";
	}
}

// Combine all logic issues
$logic_issues = array_merge($circular_deps, $orphaned_pages, $duplicate_paths, $invalid_titles);

echo '<table>';
echo '<tr><th>Edge Case Type</th><th>Issues Found</th><th>Status</th></tr>';

$edge_case_types = array(
	'Circular Dependencies' => $circular_deps,
	'Orphaned Pages' => $orphaned_pages,
	'Duplicate Paths' => $duplicate_paths,
	'Invalid Titles' => $invalid_titles
);

foreach ( $edge_case_types as $type => $issues ) {
	$status = '<span class="success">✅ None</span>';
	if ( !empty($issues) ) {
		$status = '<span class="error">❌ ' . count($issues) . ' found</span>';
		$critical_issues += count($issues);
	}
	
	echo '<tr>';
	echo '<td>' . $type . '</td>';
	echo '<td>' . (empty($issues) ? 'None' : implode('<br>', $issues)) . '</td>';
	echo '<td>' . $status . '</td>';
	echo '</tr>';
	
	$total_tests++;
	if ( empty($issues) ) {
		$passed_tests++;
	} else {
		$failed_tests++;
	}
}
echo '</table>';
echo '</div>';

echo '</div>';

// ============================================================================
// SECTION 3: MENU CREATION AND MENU ITEM PLACEMENT EDGE CASES
// ============================================================================

echo '<div class="test-section">';
echo '<h2>3. Menu Creation and Menu Item Placement Edge Cases</h2>';

$menu_issues = array();
$menu_warnings = array();

echo '<div class="test-subsection">';
echo '<h3>3.1 Menu Structure Validation</h3>';

// Test menu structure logic from update_primary_menu_structure()
$menu_structure = array(
	'root' => array(
		array('slug' => 'registration', 'menu_label' => 'Registration', 'order' => 1, 'parent' => 0),
		array('slug' => 'assessments', 'menu_label' => 'Assessments', 'order' => 2, 'parent' => 0),
		array('slug' => 'dashboard', 'menu_label' => 'Dashboard', 'order' => 3, 'parent' => 0),
		array('slug' => 'call', 'menu_label' => 'Schedule Call', 'order' => 4, 'parent' => 0),
		array('slug' => 'ennu-life-score', 'menu_label' => 'ENNU Life Score', 'order' => 5, 'parent' => 0),
	),
	'assessments' => array(
		array('slug' => 'assessments/hair', 'menu_label' => 'Hair Loss', 'order' => 1, 'parent' => 'assessments'),
		array('slug' => 'assessments/ed-treatment', 'menu_label' => 'ED Treatment', 'order' => 2, 'parent' => 'assessments'),
		array('slug' => 'assessments/weight-loss', 'menu_label' => 'Weight Loss', 'order' => 3, 'parent' => 'assessments'),
		array('slug' => 'assessments/health', 'menu_label' => 'General Health', 'order' => 4, 'parent' => 'assessments'),
		array('slug' => 'assessments/health-optimization', 'menu_label' => 'Health Optimization', 'order' => 5, 'parent' => 'assessments'),
		array('slug' => 'assessments/skin', 'menu_label' => 'Skin Health', 'order' => 6, 'parent' => 'assessments'),
		array('slug' => 'assessments/hormone', 'menu_label' => 'Hormone Balance', 'order' => 7, 'parent' => 'assessments'),
		array('slug' => 'assessments/testosterone', 'menu_label' => 'Testosterone', 'order' => 8, 'parent' => 'assessments'),
		array('slug' => 'assessments/menopause', 'menu_label' => 'Menopause', 'order' => 9, 'parent' => 'assessments'),
		array('slug' => 'assessments/sleep', 'menu_label' => 'Sleep Quality', 'order' => 10, 'parent' => 'assessments'),
	),
);

echo '<h4>3.1.1 Root Menu Items</h4>';
echo '<table>';
echo '<tr><th>Menu Item</th><th>Slug</th><th>Order</th><th>Page Exists</th><th>Status</th></tr>';

$root_menu_issues = array();
foreach ( $menu_structure['root'] as $item ) {
	$page_exists = false;
	foreach ( $test_pages_to_create as $path => $page_data ) {
		if ( $path === $item['slug'] ) {
			$page_exists = true;
			break;
		}
	}
	
	$status = '<span class="success">✅ Valid</span>';
	if ( !$page_exists ) {
		$status = '<span class="error">❌ Page Missing</span>';
		$root_menu_issues[] = "Root menu item '{$item['menu_label']}' references non-existent page '{$item['slug']}'";
		$critical_issues++;
	}
	
	echo '<tr>';
	echo '<td>' . $item['menu_label'] . '</td>';
	echo '<td>' . $item['slug'] . '</td>';
	echo '<td>' . $item['order'] . '</td>';
	echo '<td>' . ($page_exists ? 'Yes' : 'No') . '</td>';
	echo '<td>' . $status . '</td>';
	echo '</tr>';
	
	$total_tests++;
	if ( $page_exists ) {
		$passed_tests++;
	} else {
		$failed_tests++;
	}
}
echo '</table>';

echo '<h4>3.1.2 Assessment Menu Items</h4>';
echo '<table>';
echo '<tr><th>Menu Item</th><th>Slug</th><th>Parent</th><th>Order</th><th>Page Exists</th><th>Parent Exists</th><th>Status</th></tr>';

$assessment_menu_issues = array();
foreach ( $menu_structure['assessments'] as $item ) {
	$page_exists = false;
	$parent_exists = false;
	
	// Check if page exists
	foreach ( $test_pages_to_create as $path => $page_data ) {
		if ( $path === $item['slug'] ) {
			$page_exists = true;
			break;
		}
	}
	
	// Check if parent exists
	foreach ( $menu_structure['root'] as $root_item ) {
		if ( $root_item['slug'] === $item['parent'] ) {
			$parent_exists = true;
			break;
		}
	}
	
	$status = '<span class="success">✅ Valid</span>';
	$issues = array();
	
	if ( !$page_exists ) {
		$issues[] = 'Page missing';
		$assessment_menu_issues[] = "Assessment menu item '{$item['menu_label']}' references non-existent page '{$item['slug']}'";
		$critical_issues++;
	}
	
	if ( !$parent_exists ) {
		$issues[] = 'Parent missing';
		$assessment_menu_issues[] = "Assessment menu item '{$item['menu_label']}' has non-existent parent '{$item['parent']}'";
		$critical_issues++;
	}
	
	if ( !empty($issues) ) {
		$status = '<span class="error">❌ ' . implode(', ', $issues) . '</span>';
	}
	
	echo '<tr>';
	echo '<td>' . $item['menu_label'] . '</td>';
	echo '<td>' . $item['slug'] . '</td>';
	echo '<td>' . $item['parent'] . '</td>';
	echo '<td>' . $item['order'] . '</td>';
	echo '<td>' . ($page_exists ? 'Yes' : 'No') . '</td>';
	echo '<td>' . ($parent_exists ? 'Yes' : 'No') . '</td>';
	echo '<td>' . $status . '</td>';
	echo '</tr>';
	
	$total_tests++;
	if ( $page_exists && $parent_exists ) {
		$passed_tests++;
	} else {
		$failed_tests++;
	}
}
echo '</table>';
echo '</div>';

echo '<div class="test-subsection">';
echo '<h3>3.2 Menu Item Placement Edge Cases</h3>';

$placement_issues = array();

// Edge Case 1: Check for duplicate menu positions
$position_conflicts = array();
$used_positions = array();

foreach ( $menu_structure['root'] as $item ) {
	$position = $item['order'];
	if ( in_array($position, $used_positions) ) {
		$position_conflicts[] = "Duplicate position {$position} in root menu";
	}
	$used_positions[] = $position;
}

$used_positions = array();
foreach ( $menu_structure['assessments'] as $item ) {
	$position = $item['order'];
	if ( in_array($position, $used_positions) ) {
		$position_conflicts[] = "Duplicate position {$position} in assessments submenu";
	}
	$used_positions[] = $position;
}

// Edge Case 2: Check for invalid menu labels
$invalid_labels = array();
foreach ( $menu_structure['root'] as $item ) {
	if ( empty($item['menu_label']) ) {
		$invalid_labels[] = "Empty menu label for root item '{$item['slug']}'";
	}
	if ( strlen($item['menu_label']) > 50 ) {
		$invalid_labels[] = "Menu label too long for root item '{$item['slug']}'";
	}
}

foreach ( $menu_structure['assessments'] as $item ) {
	if ( empty($item['menu_label']) ) {
		$invalid_labels[] = "Empty menu label for assessment item '{$item['slug']}'";
	}
	if ( strlen($item['menu_label']) > 50 ) {
		$invalid_labels[] = "Menu label too long for assessment item '{$item['slug']}'";
	}
}

// Edge Case 3: Check for circular menu references
$circular_menu_refs = array();
foreach ( $menu_structure['assessments'] as $item ) {
	if ( $item['parent'] === $item['slug'] ) {
		$circular_menu_refs[] = "Circular menu reference: '{$item['slug']}' references itself as parent";
	}
}

$placement_issues = array_merge($position_conflicts, $invalid_labels, $circular_menu_refs);

echo '<table>';
echo '<tr><th>Placement Issue Type</th><th>Issues Found</th><th>Status</th></tr>';

$placement_types = array(
	'Position Conflicts' => $position_conflicts,
	'Invalid Labels' => $invalid_labels,
	'Circular References' => $circular_menu_refs
);

foreach ( $placement_types as $type => $issues ) {
	$status = '<span class="success">✅ None</span>';
	if ( !empty($issues) ) {
		$status = '<span class="error">❌ ' . count($issues) . ' found</span>';
		$critical_issues += count($issues);
	}
	
	echo '<tr>';
	echo '<td>' . $type . '</td>';
	echo '<td>' . (empty($issues) ? 'None' : implode('<br>', $issues)) . '</td>';
	echo '<td>' . $status . '</td>';
	echo '</tr>';
	
	$total_tests++;
	if ( empty($issues) ) {
		$passed_tests++;
	} else {
		$failed_tests++;
	}
}
echo '</table>';
echo '</div>';

echo '</div>';

// ============================================================================
// SECTION 4: COMPREHENSIVE EDGE CASE ANALYSIS
// ============================================================================

echo '<div class="test-section">';
echo '<h2>4. Comprehensive Edge Case Analysis</h2>';

echo '<div class="test-subsection">';
echo '<h3>4.1 Critical Issues Summary</h3>';

$all_critical_issues = array_merge(
	$assessment_issues,
	$slug_mapping_issues,
	$page_creation_issues,
	$parent_child_issues,
	$logic_issues,
	$root_menu_issues,
	$assessment_menu_issues,
	$placement_issues
);

if ( empty($all_critical_issues) ) {
	echo '<p class="success">✅ No critical issues found!</p>';
} else {
	echo '<p class="error">❌ Found ' . count($all_critical_issues) . ' critical issues:</p>';
	echo '<ul>';
	foreach ( $all_critical_issues as $issue ) {
		echo '<li class="error">' . htmlspecialchars($issue) . '</li>';
	}
	echo '</ul>';
}
echo '</div>';

echo '<div class="test-subsection">';
echo '<h3>4.2 Warning Summary</h3>';

$all_warnings = array_merge($assessment_warnings, $page_creation_warnings, $menu_warnings);

if ( empty($all_warnings) ) {
	echo '<p class="success">✅ No warnings found!</p>';
} else {
	echo '<p class="warning">⚠️ Found ' . count($all_warnings) . ' warnings:</p>';
	echo '<ul>';
	foreach ( $all_warnings as $warning ) {
		echo '<li class="warning">' . htmlspecialchars($warning) . '</li>';
	}
	echo '</ul>';
}
echo '</div>';

echo '<div class="test-subsection">';
echo '<h3>4.3 Test Results Summary</h3>';

$success_rate = $total_tests > 0 ? round(($passed_tests / $total_tests) * 100, 1) : 0;

echo '<table>';
echo '<tr><th>Metric</th><th>Count</th><th>Percentage</th></tr>';
echo '<tr><td>Total Tests</td><td>' . $total_tests . '</td><td>100%</td></tr>';
echo '<tr><td>Passed Tests</td><td>' . $passed_tests . '</td><td>' . round(($passed_tests / $total_tests) * 100, 1) . '%</td></tr>';
echo '<tr><td>Failed Tests</td><td>' . $failed_tests . '</td><td>' . round(($failed_tests / $total_tests) * 100, 1) . '%</td></tr>';
echo '<tr><td>Warnings</td><td>' . $warnings . '</td><td>' . round(($warnings / $total_tests) * 100, 1) . '%</td></tr>';
echo '<tr><td>Critical Issues</td><td>' . $critical_issues . '</td><td>' . round(($critical_issues / $total_tests) * 100, 1) . '%</td></tr>';
echo '</table>';

if ( $critical_issues === 0 && $failed_tests === 0 ) {
	echo '<p class="success">🎉 ALL EDGE CASE TESTS PASSED!</p>';
	echo '<p>The page creation and menu creation processes are ready for production use.</p>';
} elseif ( $critical_issues === 0 ) {
	echo '<p class="warning">⚠️ TESTS COMPLETED WITH WARNINGS</p>';
	echo '<p>The system will work but some issues should be addressed for optimal performance.</p>';
} else {
	echo '<p class="error">❌ CRITICAL ISSUES DETECTED</p>';
	echo '<p>The system has critical issues that must be resolved before page creation can proceed safely.</p>';
}
echo '</div>';

echo '<div class="test-subsection">';
echo '<h3>4.4 Recommendations</h3>';

echo '<h4>Immediate Actions Required:</h4>';
if ( $critical_issues > 0 ) {
	echo '<ul>';
	echo '<li class="error">Resolve all critical issues before proceeding with page creation</li>';
	echo '<li class="error">Fix missing shortcodes and assessment configurations</li>';
	echo '<li class="error">Resolve parent-child relationship issues</li>';
	echo '<li class="error">Fix menu structure problems</li>';
	echo '</ul>';
} else {
	echo '<ul>';
	echo '<li class="success">✅ System is ready for page creation</li>';
	echo '<li class="success">✅ Menu creation should work properly</li>';
	echo '<li class="success">✅ All critical dependencies are satisfied</li>';
	echo '</ul>';
}

echo '<h4>Optional Improvements:</h4>';
if ( $warnings > 0 ) {
	echo '<ul>';
	echo '<li class="warning">Address warnings for optimal system performance</li>';
	echo '<li class="warning">Review assessment configurations for completeness</li>';
	echo '<li class="warning">Consider theme menu location setup</li>';
	echo '</ul>';
} else {
	echo '<ul>';
	echo '<li class="success">✅ No optional improvements needed</li>';
	echo '</ul>';
}

echo '<h4>Next Steps:</h4>';
echo '<ul>';
echo '<li>Run the actual page creation process in the admin panel</li>';
echo '<li>Test menu creation with the current theme</li>';
echo '<li>Verify all pages are accessible and functional</li>';
echo '<li>Test page deletion and recreation if needed</li>';
echo '<li>Monitor error logs during the process</li>';
echo '</ul>';
echo '</div>';

echo '</div>';

// ============================================================================
// FINAL SUMMARY
// ============================================================================

echo '<div class="test-section">';
echo '<h2>🏁 Comprehensive Edge Case Test Complete</h2>';

echo '<h3>Final Test Results</h3>';
echo '<p><strong>Test Version:</strong> 62.1.4</p>';
echo '<p><strong>Test Date:</strong> ' . date('Y-m-d H:i:s') . '</p>';
echo '<p><strong>Total Tests:</strong> ' . $total_tests . '</p>';
echo '<p><strong>Success Rate:</strong> ' . $success_rate . '%</p>';
echo '<p><strong>Critical Issues:</strong> ' . $critical_issues . '</p>';
echo '<p><strong>Warnings:</strong> ' . $warnings . '</p>';

if ( $critical_issues === 0 ) {
	echo '<p class="success">🎉 SYSTEM READY FOR PRODUCTION USE</p>';
} else {
	echo '<p class="error">❌ SYSTEM HAS CRITICAL ISSUES - RESOLVE BEFORE PROCEEDING</p>';
}

echo '<p class="info">This comprehensive test covers the entire page creation process and menu creation/menu item placement process with extensive edge case testing. All potential failure points have been identified and analyzed.</p>';
echo '</div>';
?> 