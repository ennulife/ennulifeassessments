# Skin Assessment: Scoring & Logic Guide

**Document Version:** 1.0
**Plugin Version:** 55.0.0

---

## 1.0 Overview

This document provides a comprehensive breakdown of the scoring logic for the **Skin Assessment**. Each question is detailed below with its corresponding field ID, answer options, point values, and its impact on both Category and Pillar scores.

---

## 2.0 Scoring Map

| Question Title | Field ID | Answer Option | Points | Category | Pillar |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **What is your gender?** | `skin_q2` | Male | `5` | Demographics | Body |
| | | Female | `5` | | |
| **What is your skin type?** | `skin_q3` | Normal | `8` | Skin Characteristics | Aesthetics |
| | | Dry | `6` | | |
| | | Oily | `6` | | |
| | | Combination | `7` | | |
| | | Sensitive | `5` | | |
| **What is your primary skin concern?** | `skin_q4` | Acne & Blemishes | `3` | Primary Skin Issue | Aesthetics |
| | | Fine Lines & Wrinkles | `4` | | |
| | | Dark Spots & Hyperpigmentation | `5` | | |
| | | Redness & Rosacea | `4` | | |
| | | Dryness & Dehydration | `6` | | |
| **How much sun exposure do you get?** | `skin_q5` | Rarely, I'm mostly indoors | `9` | Environmental Factors | Lifestyle |
| | | Sometimes, on weekends | `6` | | |
| | | Daily, but I use sunscreen | `3` | | |
| **What is your current skincare routine?** | `skin_q6` | None | `2` | Current Regimen | Lifestyle |
| | | Basic (cleanse, moisturize, SPF) | `6` | | |
| | | Advanced (serums, exfoliants, etc.)| `8` | | |
| **How does your skin typically react to new products?** | `skin_q7` | No reaction | `9` | Skin Reactivity | Aesthetics |
| | | Becomes red or flushed | `4` | | |
| | | I get breakouts | `4` | | |
| | | It feels itchy or irritated | `3` | | |
| | | Becomes dry and tight | `5` | | |
| **Which of these lifestyle factors apply to you?** | `skin_q8` | I smoke | `3` | Lifestyle & Diet | Lifestyle |
| | | I have high stress levels | `4` | | |
| | | I have poor sleep quality | `4` | | |
| | | My diet is high in sugar/processed foods | `3` | | | 