# ENNU Life Assessment Plugin

**Version:** 57.2.0
**Author:** ENNU Life Development Team
**License:** Proprietary

---

## Overview

The ENNU Life Assessment Plugin is a powerful, enterprise-grade system for creating, managing, and scoring advanced health and wellness assessments. It is designed for high performance and stability, with a focus on providing a seamless user experience and actionable data for health professionals.

The latest releases add powerful new dimensions of **Longitudinal Wellness Tracking** and **Enhanced Administrative Control**.

## Core Features

*   **The Bio-Metric Canvas**: A stunning, interactive dashboard featuring a central, pulsating ENNU LIFE SCORE orb, animated Pillar Orbs, and historical trend charts for both the master score and BMI.
*   **Longitudinal BMI Tracking**: The system now captures a user's BMI history, providing a clear, visual journey of their weight management progress over time.
*   **Tokenized, Unified Results**: A robust, token-based results system that ensures a flawless post-assessment experience for all users.
*   **Intelligent Gender Filtering**: A personalized user dashboard that automatically displays only the assessments relevant to the user's gender profile.
*   **Unified Data Architecture**: All questions and scoring rules now live together in a single, unified configuration file: `includes/config/assessment-definitions.php`.
*   **Advanced Scoring Engine**: A powerful engine that calculates scores for 9 distinct health assessments, aggregates them into 4 Core Health Pillars, and computes a master ENNU LIFE SCORE.
*   **The Administrative Toolkit**: A new suite of powerful tools on the user profile page that allows administrators to recalculate scores and clear assessment data with surgical precision.
*   **Secure by Design**: Nonce-protected AJAX, strict data validation and sanitization, and a hardened database layer.
*   **Developer-Friendly**: A clean, object-oriented architecture and detailed documentation.

## Getting Started

See `INSTALLATION.md` for detailed installation instructions.

### Configuration

All assessment questions and scoring rules are managed in `includes/config/assessment-definitions.php`. To add or modify assessments, please refer to the `documentation/REFACTORING_AND_MAINTENANCE_GUIDE.md` file.

## Security

The ENNU Life Assessment Plugin has been developed with security as a top priority. It includes:
*   **Nonce-Protected AJAX:** All AJAX requests are protected with WordPress nonces.
*   **Data Validation and Sanitization:** All user input is strictly validated and sanitized.
*   **Prepared SQL Queries:** All database queries are prepared using `$wpdb->prepare`.
*   **Capability Checks:** All administrative actions are protected with capability checks.

## Shortcodes

The following shortcodes are available. See `SHORTCODE_DOCUMENTATION.md` for detailed usage instructions.

*   `[ennu-welcome-assessment]`
*   `[ennu-hair-assessment]`
*   `[ennu-ed-treatment-assessment]`
*   `[ennu-weight-loss-assessment]`
*   `[ennu-health-assessment]`
*   `[ennu-skin-assessment]`
*   `[ennu-sleep-assessment]`
*   `[ennu-hormone-assessment]`
*   `[ennu-menopause-assessment]`
*   `[ennu-testosterone-assessment]`
*   `[ennu-user-dashboard]`
*   `[ennu-assessment-results]` (and individual results pages like `[ennu-hair-results]`)
*   `[ennu-*-assessment-details]` (e.g., `[ennu-hair-assessment-details]`)

## Technical Details

- **Minimum PHP Version:** 7.4
- **Minimum WordPress Version:** 5.0
- **Dependencies:** None

---
For support or inquiries, please contact the ENNU Life Development Team.

