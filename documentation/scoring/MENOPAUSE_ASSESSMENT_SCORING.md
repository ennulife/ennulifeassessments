# Menopause Assessment: Scoring & Logic Guide

**Document Version:** 1.0
**Plugin Version:** 55.0.0

---

## 1.0 Overview

This document provides a comprehensive breakdown of the scoring logic for the **Menopause Assessment**. Each question is detailed below with its corresponding field ID, answer options, point values, and its impact on both Category and Pillar scores.

*Note: This assessment will only be displayed to users who have identified their gender as "Female".*

---

## 2.0 Scoring Map

| Question Title | Field ID | Answer Option | Points | Category | Pillar |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Which of the following best describes your current stage?** | `menopause_q1` | I have not yet started menopause | `9` | Menopause Stage | Body |
| | | Perimenopause (transitioning) | `5` | | |
| | | Menopause (last period over 12 months ago) | `3` | | |
| | | Post-menopause | `4` | | |
| **Which of the following symptoms...are you experiencing?** | `menopause_q2` | Hot flashes | `2` | Symptom Severity | Body |
| | | Night sweats | `2` | | |
| | | Sleep disturbances | `2` | | |
| | | Mood changes or irritability | `2` | | |
| | | Vaginal dryness | `2` | | |
| | | None of the above | `9` | | |
| **Are you currently using or have you previously used HRT?** | `menopause_q3` | Never | `7` | Treatment History | Lifestyle |
| | | Currently using | `5` | | |
| | | Used in the past | `6` | | | 