<<<<<<< HEAD
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
||||||| f31b4df
=======
# Weight Loss Assessment: Scoring & Logic Guide

**Document Version:** 2.0
**Plugin Version:** 60.0.0

---

## 1.0 Overview

This document provides a comprehensive breakdown of the scoring logic for the **Weight Loss Assessment**. Each question is detailed below with its corresponding field ID, answer options, point values, and its impact on both Category and Pillar scores.

---

## 2.0 Scoring Map

| Question Title | Field ID | Answer Option | Points | Category | Pillar |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **What is your gender?** | `wl_q_gender` | Male | `N/A` | Demographics | Body |
| | | Female | `N/A` | | |
| | | Other / Prefer not to say | `N/A` | | |
| **What is your current height and weight?** | `wl_q1` | Height/Weight Input | `N/A` | Demographics | Body |
| **What is your primary weight loss goal?** | `wl_q2` | Lose 10-20 lbs | `5` | Motivation & Goals | Mind |
| | | Lose 20-50 lbs | `4` | | |
| | | Lose 50+ lbs | `3` | | |
| | | Maintain current weight | `5` | | |
| **How would you describe your typical diet?** | `wl_q3` | Balanced and nutrient-rich | `5` | Nutrition | Lifestyle |
| | | High in processed foods | `1` | | |
| | | Low-carb or ketogenic | `4` | | |
| | | Vegetarian or vegan | `4` | | |
| | | Intermittent fasting | `5` | | |
| **How often do you exercise per week?** | `wl_q4` | 5 or more times | `5` | Physical Activity | Lifestyle |
| | | 3-4 times | `4` | | |
| | | 1-2 times | `2` | | |
| | | I rarely or never exercise | `1` | | |
| **How many hours of sleep do you get per night?** | `wl_q5` | Less than 5 hours | `1` | Lifestyle Factors | Lifestyle |
| | | 5-6 hours | `2` | | |
| | | 7-8 hours | `5` | | |
| | | More than 8 hours | `4` | | |
| **How would you rate your daily stress levels?** | `wl_q6` | Low | `5` | Psychological Factors | Mind |
| | | Moderate | `4` | | |
| | | High | `2` | | |
| | | Very High | `1` | | |
| **What has been your experience with weight loss in the past?** | `wl_q7` | Never had lasting success | `2` | Weight Loss History | Lifestyle |
| | | Some success, but gained it back | `3` | | |
| | | Good success, maintained for a while | `4` | | |
| | | This is my first serious attempt | `5` | | |
| **Do you find yourself eating due to stress, boredom, or other emotional cues?** | `wl_q8` | Often | `1` | Behavioral Patterns | Lifestyle |
| | | Sometimes | `2` | | |
| | | Rarely | `4` | | |
| | | Never | `5` | | |
| **Have you been diagnosed with any conditions that can affect weight?** | `wl_q9` | Thyroid Issues | `2` | Medical Factors | Body |
| | | PCOS | `2` | | |
| | | Insulin Resistance | `2` | | |
| | | None of the above | `5` | | |
| **How motivated are you to make lifestyle changes?** | `wl_q10` | Very motivated | `5` | Motivation & Goals | Mind |
| | | Somewhat motivated | `3` | | |
| | | Not very motivated | `1` | | |
| | | I'm not sure | `2` | | |
| **What is your primary body composition goal?** | `wl_q11` | Lose Fat | `4` | Aesthetics | Body |
| | | Build Muscle | `4` | | |
| | | Both | `5` | | |
| **Do you have a strong support system?** | `wl_q12` | Yes, very supportive | `5` | Social Support | Mind |
| | | Somewhat supportive | `3` | | |
| | | No, I am mostly on my own | `1` | | |
| **How confident are you in your ability to achieve your weight loss goals?** | `wl_q13` | Very confident | `5` | Psychological Factors | Mind |
| | | Somewhat confident | `3` | | |
| | | Not very confident | `1` | | |

---

## 3.0 Category Weighting

| Category | Weight | Description |
| :--- | :--- | :--- |
| **Nutrition** | 2.5 | Diet quality and eating patterns |
| **Physical Activity** | 2.0 | Exercise frequency and intensity |
| **Lifestyle Factors** | 1.5 | Sleep quality and duration |
| **Psychological Factors** | 2.0 | Stress levels and confidence |
| **Behavioral Patterns** | 2.0 | Emotional eating and habits |
| **Medical Factors** | 2.5 | Health conditions affecting weight |
| **Motivation & Goals** | 2.5 | Goal clarity and motivation level |
| **Weight Loss History** | 1.5 | Past weight loss experiences |
| **Aesthetics** | 1.0 | Body composition goals |
| **Social Support** | 1.0 | Support system availability |

---

## 4.0 Scoring Logic

### 4.1 Overall Score Calculation
The assessment uses a weighted average system where:
- Each question has a category weight
- Answers are scored on a 1-5 scale
- Final score = (Sum of weighted scores) / (Sum of weights)

### 4.2 Category Scoring
Each category score is calculated independently and contributes to the overall assessment score.

### 4.3 Pillar Mapping
- **Mind**: Psychological Factors, Motivation & Goals, Social Support
- **Body**: Medical Factors, Aesthetics, Demographics
- **Lifestyle**: Nutrition, Physical Activity, Lifestyle Factors, Behavioral Patterns, Weight Loss History

---

## 5.0 Data Validation

### 5.1 Required Fields
All questions are marked as required to ensure complete data collection.

### 5.2 Height/Weight Validation
- Height must be between 36-96 inches (3-8 feet)
- Weight must be between 50-500 pounds
- BMI calculation is performed automatically

### 5.3 Scoring Validation
- All questions with scoring rules must have valid answer mappings
- Weights must be positive numbers
- Answer scores must be between 1-5

---

## 6.0 Version History

- **v2.0 (60.0.0)**: Updated to match corrected assessment structure, added missing questions, fixed scoring logic
- **v1.0 (55.0.0)**: Initial documentation version 
>>>>>>> origin/main
