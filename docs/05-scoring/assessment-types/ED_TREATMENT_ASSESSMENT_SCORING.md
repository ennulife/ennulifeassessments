# ED Treatment Assessment: Scoring & Logic Guide

**Document Version:** 1.0
**Plugin Version:** 55.0.0

---

## 1.0 Overview

This document provides a comprehensive breakdown of the scoring logic for the **ED Treatment Assessment**. Each question is detailed below with its corresponding field ID, answer options, point values, and its impact on both Category and Pillar scores.

---

## 2.0 Scoring Map

| Question Title | Field ID | Answer Option | Points | Category | Pillar |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **What is your relationship status?** | `ed_treatment_q2` | Single | `6` | Psychosocial Factors | Mind |
| | | Dating | `7` | | |
| | | Married/Partnered | `8` | | |
| | | Divorced/Separated | `5` | | |
| **How would you describe the severity of your ED?** | `ed_treatment_q3` | Mild | `8` | Condition Severity | Body |
| | | Moderate | `6` | | |
| | | Severe | `3` | | |
| | | Complete | `1` | | |
| **How long have you been experiencing symptoms of ED?** | `ed_treatment_q4` | Less than 6 months | `8` | Timeline | Lifestyle |
| | | 6 months - 2 years | `6` | | |
| | | 2-5 years | `4` | | |
| | | More than 5 years | `2` | | |
| **Do you have any of the following health conditions?** | `ed_treatment_q5` | None of these | `9` | Medical Factors | Body |
| | | Diabetes | `4` | | |
| | | Heart Disease | `3` | | |
| | | High Blood Pressure | `4` | | |
| **Have you tried any ED treatments before?** | `ed_treatment_q6` | No previous treatments | `7` | Treatment History | Lifestyle |
| | | Oral medications | `6` | | |
| | | Injections | `5` | | |
| | | Vacuum devices | `5` | | |
| **Do you smoke or use tobacco products?** | `ed_treatment_q7` | No | `9` | Medical Factors | Body |
| | | Yes, socially | `4` | | |
| | | Yes, daily | `2` | | |
| | | I am a former smoker | `6` | | |
| **How often do you exercise?** | `ed_treatment_q8` | Never | `3` | Physical Health | Lifestyle |
| | | Rarely | `5` | | |
| | | Regularly | `8` | | |
| | | Daily | `9` | | |
| **How would you describe your current stress level?** | `ed_treatment_q9` | Low | `9` | Psychological Factors | Mind |
| | | Moderate | `7` | | |
| | | High | `4` | | |
| | | Very High | `2` | | |
| **What are your primary goals for seeking treatment?** | `ed_treatment_q10` | Restore function | `8` | Treatment Motivation | Mind |
| | | Boost confidence | `7` | | |
| | | Improve performance | `6` | | |
| | | Improve relationship | `8` | | |
| **Are you currently taking any of the following types of medications?** | `ed_treatment_q11`| No medications | `8` | Drug Interactions | Body |
| | | Blood pressure meds | `5` | | |
| | | Antidepressants | `4` | | |
| | | Other medications | `6` | | | 