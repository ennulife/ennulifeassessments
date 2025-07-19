# Hormone Assessment: Scoring & Logic Guide

**Document Version:** 2.0
**Plugin Version:** 60.0.0

---

## 1.0 Overview

This document provides a comprehensive breakdown of the scoring logic for the **Hormone Assessment**. Each question is detailed below with its corresponding field ID, answer options, point values, and its impact on both Category and Pillar scores.

---

## 2.0 Scoring Map

| Question Title | Field ID | Answer Option | Points | Category | Pillar |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **What is your gender?** | `hormone_q_gender` | Male | `N/A` | Demographics | Body |
| | | Female | `N/A` | | |
| | | Other / Prefer not to say | `N/A` | | |
| **What is your age?** | `hormone_q_age` | Under 25 | `5` | Age Factors | Body |
| | | 25-35 | `4` | | |
| | | 36-45 | `3` | | |
| | | 46-55 | `2` | | |
| | | Over 55 | `1` | | |
| **Which of the following symptoms...are you experiencing?** | `hormone_q1` | Fatigue or energy loss | `2` | Symptom Severity | Body |
| | | Mood swings, irritability, or anxiety | `2` | | |
| | | Unexplained weight gain | `1` | | |
| | | Low libido or sexual dysfunction | `2` | | |
| | | Difficulty sleeping or night sweats | `2` | | |
| | | Brain fog or difficulty concentrating | `2` | | |
| | | Hot flashes or temperature sensitivity | `1` | | |
| | | Hair loss or thinning | `1` | | |
| | | Skin changes (dryness, acne, aging) | `2` | | |
| | | Heart palpitations or irregular heartbeat | `1` | | |
| | | Digestive issues or bloating | `2` | | |
| | | Joint pain or muscle weakness | `1` | | |
| | | Memory problems or forgetfulness | `2` | | |
| | | Depression or low mood | `1` | | |
| | | None of the above | `5` | | |
| **How would you rate your daily stress levels?** | `hormone_q2` | Low - I rarely feel stressed | `5` | Stress & Cortisol | Mind |
| | | Moderate - I experience some stress but manage it well | `3` | | |
| | | High - I frequently feel stressed and overwhelmed | `1` | | |
| | | Very High - I feel constantly stressed and struggle to cope | `1` | | |
| **How would you describe your energy levels throughout the day?** | `hormone_q3` | Consistently low - I struggle with energy all day | `1` | Energy & Vitality | Body |
| | | I crash in the afternoon - good morning, tired evening | `2` | | |
| | | Generally stable - consistent energy levels | `4` | | |
| | | High and consistent - I have good energy all day | `5` | | |
| **How many hours of sleep do you get per night?** | `hormone_q4` | Less than 5 hours | `1` | Sleep Quality | Lifestyle |
| | | 5-6 hours | `2` | | |
| | | 7-8 hours | `5` | | |
| | | More than 8 hours | `3` | | |
| **How would you describe your appetite and eating patterns?** | `hormone_q5` | Poor appetite - I often forget to eat | `2` | Metabolic Health | Body |
| | | Emotional eating - I eat when stressed or emotional | `1` | | |
| | | Strong cravings - especially for sugar or carbs | `1` | | |
| | | Stable appetite - I eat regular meals when hungry | `5` | | |
| **How would you rate your ability to handle temperature changes?** | `hormone_q6` | Very sensitive - I am always too hot or too cold | `1` | Thyroid Function | Body |
| | | Somewhat sensitive - temperature affects me more than others | `2` | | |
| | | Moderately tolerant - I handle temperature changes okay | `4` | | |
| | | Very tolerant - temperature changes do not bother me | `5` | | |
| **How would you describe your menstrual cycle? (Females only)** | `hormone_q7` | Irregular - unpredictable timing | `2` | Reproductive Health | Body |
| | | Heavy and painful periods | `1` | | |
| | | Light or infrequent periods | `2` | | |
| | | Regular and normal | `5` | | |
| | | I am in menopause | `3` | | |
| | | Not applicable | `5` | | |
| **How would you rate your muscle strength and recovery?** | `hormone_q8` | Decreasing strength - I feel weaker than before | `1` | Muscle & Strength | Body |
| | | Slow recovery - I take longer to recover from exercise | `2` | | |
| | | Maintaining strength - I can maintain my current level | `4` | | |
| | | Increasing strength - I can build muscle and strength | `5` | | |
| **How would you describe your skin and hair quality?** | `hormone_q9` | Deteriorating - skin and hair quality is declining | `1` | Skin & Hair Health | Body |
| | | Dry and brittle - skin is dry, hair is brittle | `2` | | |
| | | Maintaining - skin and hair quality is stable | `4` | | |
| | | Improving - skin and hair quality is getting better | `5` | | |
| **How would you rate your overall mood and emotional well-being?** | `hormone_q10` | Depressed or anxious - I struggle with mood regularly | `1` | Mental Health | Mind |
| | | Irritable or moody - my mood changes frequently | `2` | | |
| | | Generally stable - my mood is usually good | `4` | | |
| | | Positive and optimistic - I have a good outlook | `5` | | |
| **How would you describe your exercise routine and physical activity?** | `hormone_q11` | Sedentary - I rarely exercise or move much | `1` | Physical Activity | Lifestyle |
| | | Light activity - I do some walking or light exercise | `2` | | |
| | | Moderate exercise - I exercise 3-4 times per week | `4` | | |
| | | Active lifestyle - I exercise regularly and stay active | `5` | | |
| **How motivated are you to optimize your hormonal health?** | `hormone_q12` | Not very motivated - I am not concerned about hormones | `1` | Motivation & Goals | Mind |
| | | Somewhat motivated - I would like to improve if it is easy | `3` | | |
| | | Very motivated - I want to optimize my hormonal health | `4` | | |
| | | Extremely motivated - I am committed to hormone optimization | `5` | | |

---

## 3.0 Category Weighting

| Category | Weight | Description |
| :--- | :--- | :--- |
| **Symptom Severity** | 3.0 | Comprehensive symptom assessment across all hormone systems |
| **Stress & Cortisol** | 2.5 | Stress hormone impact and cortisol regulation |
| **Energy & Vitality** | 2.0 | Energy levels and vitality indicators |
| **Sleep Quality** | 2.0 | Sleep patterns and hormone regulation |
| **Metabolic Health** | 2.0 | Appetite, cravings, and metabolic function |
| **Thyroid Function** | 1.5 | Temperature sensitivity and thyroid indicators |
| **Reproductive Health** | 2.0 | Menstrual cycle and reproductive hormone function |
| **Muscle & Strength** | 1.5 | Muscle strength, recovery, and anabolic hormones |
| **Skin & Hair Health** | 1.5 | Skin and hair quality as hormone indicators |
| **Mental Health** | 2.0 | Mood, emotional well-being, and mental health |
| **Physical Activity** | 2.0 | Exercise routine and physical activity levels |
| **Motivation & Goals** | 1.5 | Motivation for hormone optimization |
| **Age Factors** | 1.5 | Age-related hormone changes and considerations |

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
- **Mind**: Stress & Cortisol, Mental Health, Motivation & Goals
- **Body**: Symptom Severity, Energy & Vitality, Metabolic Health, Thyroid Function, Reproductive Health, Muscle & Strength, Skin & Hair Health, Age Factors
- **Lifestyle**: Sleep Quality, Physical Activity

---

## 5.0 Data Validation

### 5.1 Required Fields
All questions are marked as required to ensure complete data collection.

### 5.2 Gender-Specific Logic
- Menstrual cycle question includes "Not applicable" option for males
- Assessment adapts scoring based on gender-specific hormone concerns

### 5.3 Scoring Validation
- All questions with scoring rules must have valid answer mappings
- Weights must be positive numbers
- Answer scores must be between 1-5

---

## 6.0 Version History

- **v2.0 (60.0.0)**: Complete rebuild with 13 comprehensive questions, proper gender-specific logic, evidence-based scoring
- **v1.0 (55.0.0)**: Initial documentation version 