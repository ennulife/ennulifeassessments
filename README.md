# ENNU Life Assessment Plugin

**Version:** 25.0.0
**Author:** ENNU Life Development Team
**License:** Proprietary

---

## Overview

The ENNU Life Assessment Plugin is a powerful, enterprise-grade system for creating, managing, and scoring advanced health and wellness assessments. It is designed for high performance and stability, with a focus on providing a seamless user experience and actionable data for health professionals.

This version represents a complete architectural overhaul, resulting in a stable, secure, and highly maintainable codebase.

## Core Features

- **Dynamic Assessment System**: Assessments are managed through centralized configuration files, allowing for easy updates without modifying core plugin logic.
- **Advanced Scoring Engine**: A flexible scoring system with customizable weights and categories for nuanced and accurate results.
- **Secure by Design**: Built with enterprise-grade security, including nonce-protected AJAX endpoints and a robust data validation pipeline.
- **Admin Management Suite**: A comprehensive admin dashboard for viewing user submissions, managing data, and monitoring system health.
- **WooCommerce Integration**: Seamlessly connects assessment results to WooCommerce for recommending and selling products.
- **Developer-Friendly**: The new architecture is clean, well-documented, and easy to extend.

## Getting Started

### Installation

1.  Upload the `ennulifeassessments` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.

### Configuration

All assessment questions and scoring rules are now managed in the `includes/config/` directory. For detailed instructions on how to modify or add assessments, please refer to the new `documentation/REFACTORING_AND_MAINTENANCE_GUIDE.md` file.

## Shortcodes

The following shortcodes are available to display the assessments on the frontend:

-   `[ennu-welcome-assessment]`
-   `[ennu-hair-assessment]`
-   `[ennu-ed-treatment-assessment]`
-   `[ennu-weight-loss-assessment]`
-   `[ennu-health-assessment]`
-   `[ennu-skin-assessment]`

## Technical Details

- **Minimum PHP Version:** 7.4
- **Minimum WordPress Version:** 5.0
- **Dependencies:** None (WooCommerce is recommended for full functionality but not required).

---
For support or inquiries, please contact the ENNU Life Development Team.

