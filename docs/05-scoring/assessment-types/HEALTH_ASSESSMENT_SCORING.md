# Health Assessment: Scoring & Logic Guide

**Document Version:** 1.0
**Plugin Version:** 55.0.0

---

## 1.0 Overview

This document provides a comprehensive breakdown of the scoring logic for the **Health Assessment**. Each question is detailed below with its corresponding field ID, answer options, point values, and its impact on both Category and Pillar scores.

---

## 2.0 Scoring Map

| Question Title | Field ID | Answer Option | Points | Category | Pillar |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **What is your gender?** | `health_q2` | Male | `5` | Demographics | Body |
| | | Female | `5` | | |
| **How would you rate your overall health?** | `health_q3` | Poor | `2` | Current Health Status | Body |
| | | Fair | `5` | | |
| | | Good | `7` | | |
| | | Excellent | `9` | | |
| **How often do you engage in moderate to intense physical activity?** | `health_q5` | Rarely or Never | `1` | Physical Activity | Lifestyle |
| | | 1-2 times a week | `5` | | |
| | | 3-5 times a week | `8` | | |
| | | Almost every day | `9` | | |
| **How would you describe your typical diet?** | `health_q6` | High in processed foods | `2` | Nutrition | Lifestyle |
| | | A typical Western diet | `5` | | |
| | | Mostly whole foods | `7` | | |
| | | Very clean, whole foods diet | `9` | | |
| **How would you rate your sleep quality?** | `health_q7` | Poor, I wake up tired | `3` | Sleep & Recovery | Lifestyle |
| | | Fair, could be better | `5` | | |
| | | Good, usually restful | `7` | | |
| | | Excellent, I wake up refreshed | `9` | | |
| **How well do you manage stress?** | `health_q8` | I don't manage it well | `3` | Stress & Mental Health | Mind |
| | | I have some coping methods | `5` | | |
| | | I manage it well | `7` | | |
| | | I have a proactive routine | `9` | | |
| **Do you get regular preventive care (e.g., check-ups)?** | `health_q9` | Never or rarely | `2` | Preventive Health | Lifestyle |
| | | Only when I have a problem | `6` | | |
| | | I have regular annual check-ups | `9` | | |
| **What are your main health goals?** | `health_q10` | Live longer | `9` | Health Motivation | Mind |
| | | Boost energy | `8` | | |
| | | Improve sleep | `8` | | |
| | | Lose weight | `7` | | |
| | | Build muscle | `7` | | |
| | | Sharpen focus & memory | `8` | | |
| | | Balance hormones | `9` | | |
| | | Improve mood | `7` | | |
| | | Boost libido & performance | `8` | | |
| | | Support heart health | `9` | | |
| | | Manage menopause | `8` | | |
| | | Increase testosterone | `8` | | | 