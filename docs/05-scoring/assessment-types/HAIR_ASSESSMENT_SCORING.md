# Hair Assessment: Scoring & Logic Guide

**Document Version:** 1.0
**Plugin Version:** 55.0.0

---

## 1.0 Overview

This document provides a comprehensive breakdown of the scoring logic for the **Hair Assessment**. Each question is detailed below with its corresponding field ID, answer options, point values, and its impact on both Category and Pillar scores.

---

## 2.0 Scoring Map

| Question Title | Field ID | Answer Option | Points | Category | Pillar |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **What is your gender?** | `hair_q2` | Male | `5` | Demographics | Body |
| | | Female | `5` | | |
| **What are your main hair concerns?** | `hair_q3` | Thinning Hair | `4` | Hair Health Status | Aesthetics |
| | | Receding Hairline | `3` | | |
| | | Bald Spots | `2` | | |
| | | Overall Hair Loss | `1` | | |
| **How long have you been experiencing hair loss?** | `hair_q4` | Less than 6 months | `8` | Progression Timeline | Lifestyle |
| | | 6 months - 2 years | `6` | | |
| | | 2-5 years | `4` | | |
| | | More than 5 years | `2` | | |
| **How would you describe the speed of your hair loss?** | `hair_q5` | Very Slow | `8` | Progression Rate | Aesthetics |
| | | Moderate | `6` | | |
| | | Fast | `3` | | |
| | | Very Fast | `1` | | |
| **Is there a history of hair loss in your family?** | `hair_q6` | No Family History | `9` | Genetic Factors | Body |
| | | Mother's Side | `6` | | |
| | | Father's Side | `5` | | |
| | | Both Sides | `3` | | |
| **What is your current stress level?** | `hair_q7` | Low Stress | `9` | Lifestyle Factors | Lifestyle |
| | | Moderate Stress | `7` | | |
| | | High Stress | `4` | | |
| | | Very High Stress | `2` | | |
| **How would you rate your current diet quality?** | `hair_q8` | Excellent | `9` | Nutritional Support | Body |
| | | Good | `7` | | |
| | | Fair | `5` | | |
| | | Poor | `2` | | |
| **Have you tried any hair loss treatments before?** | `hair_q9` | No Treatments | `7` | Treatment History | Lifestyle |
| | | Over-the-Counter | `6` | | |
| | | Prescription Meds | `5` | | |
| | | Medical Procedures | `4` | | |
| **What are your hair goals?** | `hair_q10` | Stop Hair Loss | `8` | Treatment Expectations | Mind |
| | | Regrow Hair | `6` | | |
| | | Thicken Hair | `7` | | |
| | | Overall Improvement | `8` | | |
