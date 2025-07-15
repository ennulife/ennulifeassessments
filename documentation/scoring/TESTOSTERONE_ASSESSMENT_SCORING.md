# Testosterone Assessment: Scoring & Logic Guide

**Document Version:** 1.0
**Plugin Version:** 55.0.0

---

## 1.0 Overview

This document provides a comprehensive breakdown of the scoring logic for the **Testosterone Assessment**. Each question is detailed below with its corresponding field ID, answer options, point values, and its impact on both Category and Pillar scores.

*Note: This assessment will only be displayed to users who have identified their gender as "Male".*

---

## 2.0 Scoring Map

| Question Title | Field ID | Answer Option | Points | Category | Pillar |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Which of the following symptoms...apply to you?** | `testosterone_q1` | Low sex drive (libido) | `2` | Symptom Severity | Body |
| | | Lack of energy or fatigue | `2` | | |
| | | Reduced muscle mass or difficulty gaining muscle | `2` | | |
| | | Increase in body fat | `2` | | |
| | | Depressed mood or irritability | `2` | | |
| | | Difficulty with erections | `2` | | |
| | | None of the above | `9` | | |
| **How would you describe your ability to build and maintain muscle?** | `testosterone_q2` | Very difficult | `2` | Anabolic Response | Body |
| | | Somewhat difficult | `4` | | |
| | | Moderate | `6` | | |
| | | Relatively easy | `8` | | |
| **How would you describe your energy levels and motivation?** | `testosterone_q3` | Very low | `2` | Vitality & Drive | Mind |
| | | Lower than usual | `4` | | |
| | | Normal | `7` | | |
| | | High | `9` | | | 