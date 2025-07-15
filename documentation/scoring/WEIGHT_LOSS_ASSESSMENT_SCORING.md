# Weight Loss Assessment: Scoring & Logic Guide

**Document Version:** 1.0
**Plugin Version:** 55.0.0

---

## 1.0 Overview

This document provides a comprehensive breakdown of the scoring logic for the **Weight Loss Assessment**. Each question is detailed below with its corresponding field ID, answer options, point values, and its impact on both Category and Pillar scores.

---

## 2.0 Scoring Map

| Question Title | Field ID | Answer Option | Points | Category | Pillar |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **What is your gender?** | `weight_loss_q2` | Male | `5` | Demographics | Body |
| | | Female | `5` | | |
| **What is your primary weight loss goal?** | `weight_loss_q3` | Lose 10-20 lbs | `8` | Motivation & Goals | Mind |
| | | Lose 20-50 lbs | `7` | | |
| | | Lose 50+ lbs | `6` | | |
| | | Maintain current weight | `9` | | |
| **How often do you exercise?** | `weight_loss_q5` | Never | `1` | Physical Activity | Lifestyle |
| | | 1-2 times/week | `3` | | |
| | | 3-4 times/week | `8` | | |
| | | 5+ times/week | `9` | | |
| **How would you describe your typical diet?** | `weight_loss_q6` | Mostly Unhealthy | `2` | Nutrition | Lifestyle |
| | | Generally Balanced | `6` | | |
| | | Very Healthy | `8` | | |
| | | Strict Diet | `7` | | |
| **How many hours of sleep do you get per night?** | `weight_loss_q7` | Less than 5 hours | `3` | Lifestyle Factors | Lifestyle |
| | | 5-6 hours | `5` | | |
| | | 7-8 hours | `9` | | |
| | | More than 8 hours | `8` | | |
| **How would you rate your daily stress levels?** | `weight_loss_q8` | Low | `9` | Psychological Factors | Mind |
| | | Moderate | `7` | | |
| | | High | `4` | | |
| | | Very High | `2` | | |
| **What has been your experience with weight loss in the past?** | `weight_loss_q9` | Never had lasting success | `3` | Weight Loss History | Lifestyle |
| | | Some success, but gained it back | `4` | | |
| | | Good success, maintained for a while | `6` | | |
| | | This is my first serious attempt | `7` | | |
| **Do you have any of these eating habits?** | `weight_loss_q10` | Emotional eating | `3` | Behavioral Patterns | Lifestyle |
| | | Late-night snacking | `4` | | |
| | | Binge eating | `2` | | |
| | | Sugary drinks | `3` | | |
| **How motivated are you to make a change?** | `weight_loss_q11`| Not very motivated | `2` | Motivation & Goals | Mind |
| | | Somewhat motivated | `4` | | |
| | | Very motivated | `7` | | |
| | | Committed and ready | `9` | | |
| **What kind of support system do you have?** | `weight_loss_q12`| I'm on my own | `3` | Social Support | Mind |
| | | Partner/Spouse | `7` | | |
| | | Family and Friends | `8` | | |
| | | Professional (coach, etc.) | `9` | | | 