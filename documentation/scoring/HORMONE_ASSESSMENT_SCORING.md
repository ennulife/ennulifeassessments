# Hormone Assessment: Scoring & Logic Guide

**Document Version:** 1.0
**Plugin Version:** 55.0.0

---

## 1.0 Overview

This document provides a comprehensive breakdown of the scoring logic for the **Hormone Assessment**. Each question is detailed below with its corresponding field ID, answer options, point values, and its impact on both Category and Pillar scores.

---

## 2.0 Scoring Map

| Question Title | Field ID | Answer Option | Points | Category | Pillar |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Which of the following symptoms...have you been experiencing?** | `hormone_q1` | Fatigue or lack of energy | `2` | Symptom Severity | Body |
| | | Unexplained weight gain... | `2` | | |
| | | Mood swings, irritability, or anxiety | `2` | | |
| | | Decreased sex drive | `2` | | |
| | | Difficulty sleeping | `2` | | |
| | | Changes in skin or hair... | `2` | | |
| | | None of the above | `9` | | |
| **How would you describe your energy levels throughout the day?** | `hormone_q2` | Consistently low | `2` | Vitality | Body |
| | | I crash in the afternoon | `4` | | |
| | | Generally stable | `7` | | |
| | | High and consistent | `9` | | | 