<?php
/**
 * Template: Admin User Health Summary
 *
 * This is a self-contained component to display a beautiful, data-rich
 * summary of a user's scores on their profile page in the admin area.
 */
if (!defined('ABSPATH')) exit;

// Data passed from the rendering function in class-enhanced-admin.php:
// $user, $ennu_life_score, $average_pillar_scores
?>
<style>
    .ennu-health-summary {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .ennu-health-summary h2 {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 20px 0;
        padding-bottom: 15px;
        border-bottom: 1px solid #e5e7eb;
    }
    .summary-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 30px;
        align-items: center;
    }
    .summary-score-display {
        text-align: center;
    }
    .summary-score-value {
        font-size: 56px;
        font-weight: 800;
        line-height: 1;
        color: #2563eb;
    }
    .summary-score-label {
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .summary-pillars-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .summary-pillar-item {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #f4f7f9;
        padding: 10px;
        border-radius: 8px;
    }
    .summary-pillar-name {
        font-weight: 600;
        color: #111827;
        flex-grow: 1;
    }
    .summary-pillar-score {
        font-size: 18px;
        font-weight: 700;
        color: #2563eb;
    }
</style>

<div class="ennu-health-summary">
    <h2><?php echo esc_html($user->display_name); ?>'s Health Summary</h2>
    <div class="summary-grid">
        <div class="summary-score-display">
            <div class="summary-score-value"><?php echo is_numeric($ennu_life_score) ? esc_html(number_format($ennu_life_score, 1)) : 'N/A'; ?></div>
            <div class="summary-score-label">ENNU Life Score</div>
        </div>
        <div class="summary-pillars-grid">
            <?php foreach($average_pillar_scores as $pillar => $score): ?>
                <div class="summary-pillar-item">
                    <span class="summary-pillar-name"><?php echo esc_html($pillar); ?></span>
                    <span class="summary-pillar-score"><?php echo esc_html(number_format($score, 1)); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div> 