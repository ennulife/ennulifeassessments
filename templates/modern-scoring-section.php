<?php
/**
 * Modern Scoring UI Section - WordPress Template
 * 
 * Replaces the traditional orbs with a modern card-based design
 * 
 * @package ENNU_Life
 * @since 64.57.0
 */

// Ensure we have the necessary data
$dashboard_data = $dashboard_data ?? array();
$ennu_life_score = $ennu_life_score ?? ( $dashboard_data['scores']['ennu_life_score'] ?? 0 );
$average_pillar_scores = $average_pillar_scores ?? ( $dashboard_data['scores']['pillar_scores'] ?? array() );

// Ensure average_pillar_scores is always an array with all pillars
if ( ! is_array( $average_pillar_scores ) ) {
    $average_pillar_scores = array();
}

// Define all pillars and their icons
$all_pillars = array(
    'Mind' => '🧠',
    'Body' => '💪', 
    'Lifestyle' => '🌟',
    'Aesthetics' => '✨'
);

// Use global target if it exists, otherwise calculate it
if (!isset($global_target_score)) {
    // Calculate global target score if not already set
    if ($ennu_life_score == 0) {
        $global_target_score = 3.0;
    } elseif ($ennu_life_score < 3.0) {
        $global_target_score = min(5.0, $ennu_life_score + 2.0);
    } elseif ($ennu_life_score < 6.0) {
        $global_target_score = min(7.5, $ennu_life_score + 1.5);
    } elseif ($ennu_life_score < 8.0) {
        $global_target_score = min(9.0, $ennu_life_score + 1.0);
    } else {
        $global_target_score = min(10.0, $ennu_life_score + 0.5);
    }
}

// Use the global target for all calculations
$ennulife_target = $global_target_score;
$ennulife_progress = $ennu_life_score > 0 ? ($ennu_life_score / $ennulife_target) * 100 : 0;

// Get health score description
$health_score_description = 'Getting started';
if ($ennu_life_score >= 9) {
    $health_score_description = 'Exceptional • Top 5% of users';
} elseif ($ennu_life_score >= 8) {
    $health_score_description = 'Excellent • Top 15% of users';
} elseif ($ennu_life_score >= 7) {
    $health_score_description = 'Very Good • Above average';
} elseif ($ennu_life_score >= 6) {
    $health_score_description = 'Good • Room for improvement';
} elseif ($ennu_life_score >= 5) {
    $health_score_description = 'Fair • Focus on key areas';
} elseif ($ennu_life_score > 0) {
    $health_score_description = 'Needs attention • Start today';
}
?>

<div class="modern-scores-section">
    <style>
        /* Include modern scoring CSS inline for immediate loading */
        @import url('<?php echo ENNU_LIFE_PLUGIN_URL; ?>assets/css/modern-scoring-ui.css');
    </style>

    <div class="modern-scores-layout">
        <!-- Hero Section - Three Columns -->
        <div class="hero-row">
            <!-- Column 1: Member Information -->
            <div class="pillar-card member-info-card">
                <div class="pillar-card-header">
                    <img src="<?php echo ENNU_LIFE_PLUGIN_URL; ?>assets/img/ennu-logo-black.png" 
                         alt="ENNU Life" 
                         class="member-card-logo"
                         style="max-height: 35px; width: auto; display: block; margin: 0 auto;">
                </div>
                
                <div class="member-info-content">
                    <div class="member-greeting" id="member-card-greeting-modern" style="text-align: center; margin-top: 10px;">
                        <?php 
                        $current_user = wp_get_current_user();
                        $first_name = $current_user->first_name ?: 'Member';
                        $display_name = $first_name;
                        $member_since = date('F Y', strtotime($current_user->user_registered));
                        ?>
                        <h3 style="margin: 0; font-size: 16px; font-weight: 600;">Welcome, <?php echo esc_html($first_name); ?>!</h3>
                    </div>
                    <div class="member-details">
                        <div class="member-detail-item">
                            <span class="detail-label">Member Since:</span>
                            <span class="detail-value"><?php echo esc_html($member_since); ?></span>
                        </div>
                        <div class="member-detail-item">
                            <span class="detail-label">Assessments:</span>
                            <span class="detail-value"><?php 
                                $completed_count = count(array_filter($average_pillar_scores, function($score) { return $score > 0; }));
                                echo esc_html($completed_count . '/11'); 
                            ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 2: EnnuLife Score (styled like pillar cards) -->
            <div class="pillar-card ennulife" 
                 style="--progress: <?php echo esc_attr($ennulife_progress); ?>"
                 data-pillar="EnnuLife"
                 role="button"
                 tabindex="0"
                 aria-label="<?php echo esc_attr('EnnuLife score ' . number_format($ennu_life_score, 1) . ' out of 10'); ?>">
                
                <!-- Background image layer -->
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: url('<?php echo ENNU_LIFE_PLUGIN_URL; ?>assets/img/ennulife-score.png'); background-repeat: no-repeat; background-position: center center; background-size: 50%; opacity: 0.06; pointer-events: none; z-index: 0;"></div>
                
                <div class="pillar-card-header" style="position: relative; z-index: 1;">
                    <div class="pillar-card-title">EnnuLife Score</div>
                </div>
                
                <div class="pillar-card-score" style="position: relative; z-index: 1;">
                    <div style="display: flex; align-items: baseline; gap: 6px;">
                        <div class="pillar-score-value" aria-live="polite">
                            <?php echo esc_html(number_format($ennu_life_score, 1)); ?>
                        </div>
                        <div class="pillar-score-max">/10</div>
                    </div>
                    <div class="pillar-target-value" style="background: red; color: white; padding: 5px;">
                        TARGET: <?php echo esc_html(isset($ennulife_target) ? number_format($ennulife_target, 1) : 'UNDEFINED'); ?>
                    </div>
                </div>
                
                <div class="pillar-progress-bar" style="position: relative; z-index: 1;"
                     role="progressbar" 
                     aria-valuemin="0" 
                     aria-valuemax="10" 
                     aria-valuenow="<?php echo esc_attr($ennu_life_score); ?>">
                    <div class="pillar-progress-fill"></div>
                </div>
                
                <div class="pillar-card-footer" style="position: relative; z-index: 1;">
                    <div class="pillar-target-label">
                        Target: <span class="target-value-small"><?php echo esc_html(number_format($ennulife_target, 1)); ?></span>
                    </div>
                    <div class="pillar-progress-percent"><?php echo esc_html(round($ennulife_progress)); ?>% there</div>
                </div>
            </div>

            <!-- Column 3: Health Coach Information -->
            <div class="pillar-card coach-info-card">
                <div class="pillar-card-header">
                    <div class="pillar-card-title">Your Health Coach</div>
                </div>
                
                <div class="coach-info-content">
                    <div class="coach-profile-section">
                        <div class="coach-profile-pic">
                            <img src="<?php echo ENNU_LIFE_PLUGIN_URL; ?>assets/img/leesa.png" 
                                 alt="Leesa Ennenbach" 
                                 class="profile-pic-circle">
                        </div>
                        <div class="coach-details">
                            <div class="coach-name">Leesa Ennenbach</div>
                            <div class="coach-specialty">Health & Wellness Coach</div>
                        </div>
                    </div>
                    <div class="coach-actions">
                        <button class="coach-action-btn schedule-btn" style="background-color: #374151; color: white;">Schedule Call</button>
                        <button class="coach-action-btn message-btn" style="background-color: #374151; color: white;">Send Message</button>
                    </div>
                    <div class="coach-availability">
                        <span class="availability-dot"></span>
                        Available Today
                    </div>
                </div>
            </div>
        </div>

        <!-- Pillar Cards Row -->

        <?php foreach ($all_pillars as $pillar => $icon) : 
            $has_data = isset($average_pillar_scores[$pillar]) && $average_pillar_scores[$pillar] > 0;
            $score = $has_data ? $average_pillar_scores[$pillar] : 0;
            // Use the same global target for all pillars
            $target_score = $global_target_score;
            $pillar_progress = $score > 0 ? ($score / $target_score) * 100 : 0;
            
            $pillar_image = ENNU_LIFE_PLUGIN_URL . 'assets/img/' . strtolower($pillar) . '-score.png';
            ?>
            
            <div class="pillar-card <?php echo esc_attr(strtolower($pillar)); ?>" 
                 style="--progress: <?php echo esc_attr($pillar_progress); ?>"
                 data-pillar="<?php echo esc_attr($pillar); ?>"
                 role="button"
                 tabindex="0"
                 aria-label="<?php echo esc_attr($pillar . ' pillar score ' . number_format($score, 1) . ' out of 10'); ?>">
                
                <!-- Background image layer -->
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: url('<?php echo esc_url($pillar_image); ?>'); background-repeat: no-repeat; background-position: center center; background-size: 50%; opacity: 0.08; pointer-events: none; z-index: 0;"></div>
                
                <div class="pillar-card-header" style="position: relative; z-index: 1;">
                    <div class="pillar-card-title">
                        <?php echo esc_html($pillar); ?>
                    </div>
                </div>
                
                <div class="pillar-card-score" style="position: relative; z-index: 1;">
                    <div style="display: flex; align-items: baseline; gap: 6px;">
                        <div class="pillar-score-value" aria-live="polite">
                            <?php echo $has_data ? esc_html(number_format($score, 1)) : '0.0'; ?>
                        </div>
                        <div class="pillar-score-max">/10</div>
                    </div>
                    <div class="pillar-target-value" style="background: red; color: white; padding: 5px;">
                        TARGET: <?php echo esc_html(isset($target_score) ? number_format($target_score, 1) : 'UNDEFINED'); ?>
                    </div>
                </div>
                
                <div class="pillar-progress-bar" style="position: relative; z-index: 1;"
                     role="progressbar" 
                     aria-valuemin="0" 
                     aria-valuemax="10" 
                     aria-valuenow="<?php echo esc_attr($score); ?>">
                    <div class="pillar-progress-fill"></div>
                </div>
                
                <div class="pillar-card-footer" style="position: relative; z-index: 1;">
                    <div class="pillar-target-label">
                        Target: <span class="target-value-small"><?php echo esc_html(number_format($target_score, 1)); ?></span>
                    </div>
                    <div class="pillar-progress-percent"><?php echo esc_html(round($pillar_progress)); ?>% there</div>
                </div>
            </div>
            
        <?php endforeach; ?>
    </div>
</div>

<script>
// Dynamic greeting system
function getRandomGreeting(displayName) {
    const hour = new Date().getHours();
    
    // Morning variations (5 AM - 12 PM)
    const morningGreetings = [
        `Good morning, ${displayName}`,
        `Morning, ${displayName}`,
        `Rise and shine, ${displayName}`
    ];
    
    // Afternoon variations (12 PM - 5 PM)
    const afternoonGreetings = [
        `Good afternoon, ${displayName}`,
        `Afternoon, ${displayName}`
    ];
    
    // Evening variations (5 PM - 9 PM)
    const eveningGreetings = [
        `Good evening, ${displayName}`,
        `Evening, ${displayName}`
    ];
    
    // Night variations (9 PM - 12 AM)
    const nightGreetings = [
        `Good evening, ${displayName}`,
        `Evening, ${displayName}`
    ];
    
    // Late night variations (12 AM - 5 AM)
    const lateNightGreetings = [
        `Still up, ${displayName}?`,
        `Late night, ${displayName}`,
        `Night owl, ${displayName}`
    ];
    
    // Select greeting based on user's local time
    let greetings;
    if (hour >= 5 && hour < 12) {
        greetings = morningGreetings;
    } else if (hour >= 12 && hour < 17) {
        greetings = afternoonGreetings;
    } else if (hour >= 17 && hour < 21) {
        greetings = eveningGreetings;
    } else if (hour >= 21 && hour < 24) {
        greetings = nightGreetings;
    } else {
        greetings = lateNightGreetings;
    }
    
    // Randomly select one greeting
    return greetings[Math.floor(Math.random() * greetings.length)];
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize modern scoring UI
    console.log('🎨 Initializing Modern Scoring UI');
    
    // Update member card greeting
    const memberCardGreeting = document.getElementById('member-card-greeting-modern');
    if (memberCardGreeting) {
        const h3 = memberCardGreeting.querySelector('h3');
        if (h3) {
            const displayName = '<?php echo esc_js($display_name); ?>';
            h3.textContent = getRandomGreeting(displayName);
        }
    }
    
    // Animate progress bars after page load
    setTimeout(() => {
        const cards = document.querySelectorAll('.pillar-card');
        cards.forEach((card, index) => {
            const progress = parseInt(card.style.getPropertyValue('--progress') || 0);
            const progressFill = card.querySelector('.pillar-progress-fill');
            
            if (progressFill) {
                // Stagger animation for visual appeal
                setTimeout(() => {
                    progressFill.style.transform = `scaleX(${progress / 100})`;
                }, index * 200);
            }
        });
        
        // Animate circular progress for health score
        const circularProgress = document.querySelector('.progress-bar');
        if (circularProgress) {
            const progress = parseInt(circularProgress.style.getPropertyValue('--progress') || 0);
            const circumference = 283; // 2 * π * 45
            const offset = circumference - (circumference * progress / 100);
            circularProgress.style.strokeDashoffset = offset;
        }
    }, 800);
    
    // Add click handlers for pillar cards
    const cards = document.querySelectorAll('.pillar-card');
    cards.forEach(card => {
        card.addEventListener('click', function() {
            const pillar = this.dataset.pillar;
            console.log(`Clicked ${pillar} pillar card`);
            
            // Add visual feedback
            this.style.transform = 'translateY(-8px) scale(1.02)';
            setTimeout(() => {
                this.style.transform = '';
            }, 200);
            
            // Trigger custom event for other scripts to listen to
            const event = new CustomEvent('pillarCardClick', {
                detail: { pillar: pillar, element: this }
            });
            document.dispatchEvent(event);
        });
        
        // Add keyboard support
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
    
    // Add click handler for hero health score
    const heroCard = document.querySelector('.health-score-hero');
    if (heroCard) {
        heroCard.addEventListener('click', function() {
            console.log('Clicked health score hero');
            this.style.transform = 'translateY(-6px) scale(1.01)';
            setTimeout(() => {
                this.style.transform = '';
            }, 200);
            
            // Trigger custom event
            const event = new CustomEvent('healthScoreClick', {
                detail: { element: this }
            });
            document.dispatchEvent(event);
        });
    }
    
    console.log('✅ Modern Scoring UI initialized successfully');
});
</script>