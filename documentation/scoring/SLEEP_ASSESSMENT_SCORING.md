# Sleep Assessment: Scoring & Logic Guide

**Document Version:** 1.0
**Plugin Version:** 55.0.0

---

## 1.0 Overview

This document provides a comprehensive breakdown of the scoring logic for the **Sleep Assessment**. Each question is detailed below with its corresponding field ID, answer options, point values, and its impact on both Category and Pillar scores.

---

## 2.0 Scoring Map

| Question Title | Field ID | Answer Option | Points | Category | Pillar |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **On average, how many hours of sleep do you get per night?** | `sleep_q1` | Less than 5 hours | `2` | Sleep Duration | Lifestyle |
| | | 5-6 hours | `4` | | |
| | | 7-8 hours (Recommended) | `9` | | |
| | | 9 or more hours | `7` | | |
| **How would you rate the quality of your sleep?** | `sleep_q2` | Very Poor | `1` | Sleep Quality | Lifestyle |
| | | Poor | `3` | | |
| | | Fair | `5` | | |
| | | Good | `8` | | |
| **How often do you wake up during the night?** | `sleep_q3` | Frequently (3+ times) | `2` | Sleep Continuity | Lifestyle |
| | | Sometimes (1-2 times) | `5` | | |
| | | Rarely | `8` | | |
| | | Never | `9` | | |
| **Which of the following do you use to help you sleep?** | `sleep_q4` | None | `9` | Sleep Dependency | Lifestyle |
| | | Melatonin | `6` | | |
| | | Herbal supplements (e.g., Valerian) | `5` | | |
| | | Over-the-counter sleep aids | `4` | | |
| | | Prescription sleep medication | `2` | | | 