# ENNU Life: Master Assessment & Scoring Guide

**Document Version:** 5.0
**Date:** 2024-07-31
**Author:** The World's Greatest Developer
**Status:** FINAL

---

## 1.0 Introduction: The Scoring Symphony

This document is the single, canonical source of truth for the entire ENNU Life assessment and scoring ecosystem. It contains every question, answer, and scoring parameter for all quantitative assessments, as well as the complete architecture for the new **Symptom Qualification Engine**. This guide supersedes all previous, disparate scoring documents.

**Scoring Rationale:** The ENNU LIFE SCORE is the result of a "Scoring Symphony" with four distinct movements:
1.  **Quantitative Engine (Potential):** Calculates a user's *potential* for health based on self-reported history and lifestyle. Higher point values indicate a better state.
2.  **Qualitative Engine (Reality):** Applies a "Pillar Integrity Penalty" based on the *severity and frequency* of a user's real-world symptoms.
3.  **Objective Engine (Actuality):** Applies the ultimate "Actuality Adjustment" based on hard biomarker data from lab tests.
4.  **Intentionality Engine (Alignment):** Applies a small "Alignment Boost" to reward user focus on their stated goals.

---

## 2.0 Quantitative Assessments

This section details the assessments that measure a user's health *potential*.

### 2.1 Welcome Assessment (Global Data Collection)
*Note: This assessment is primarily for gathering foundational user data and does not contribute significantly to the overall score.*

| Question ID | Question | Description/Subtitle | Type | Global Key |
| :--- | :--- | :--- | :--- | :--- |
| `welcome_q1` | What is your gender? | | `radio` | `gender` |
| `welcome_q2` | What is your date of birth?| | `dob_dropdowns`| `user_dob_combined` |
| `welcome_q3` | What are your main health goals? | Select all that apply. | `multiselect`| `health_goals` |
| `welcome_q4` | What's your full name?| | `first_last_name` | `first_last_name` |
| `welcome_q5` | What is your contact information? | | `email_phone`| `email_phone` |

### 2.2 Hair Assessment

| Question ID | Question | Description/Subtitle | Type | Global Key | Options | Answer Value | Points | Category | Weight |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **hair_q1** | What is your date of birth? | | `dob_dropdowns` | `user_dob_combined`| N/A | N/A | 0 | Demographics | 0 |
| **hair_q2** | What is your gender? | | `radio` | `gender` | Male | `male` | 5 | Demographics | 0.5 |
| | | | | | Female | `female` | 5 | | |
| **hair_q3** | What are your main hair concerns? | | `multiselect` | ` ` | Thinning Hair | `thinning` | 4 | Hair Health Status | 3 |
| | | | | | Receding Hairline | `receding` | 3 | | |
| | | | | | Bald Spots | `bald_spots` | 2 | | |
| | | | | | Overall Hair Loss | `overall_loss`| 1 | | |
| **hair_q4** | How long have you been experiencing hair loss?| | `radio` | ` ` | Less than 6 months| `recent` | 8 | Progression Timeline | 2 |
| | | | | | 6 months - 2 years| `moderate` | 6 | | |
| | | | | | 2-5 years | `long` | 4 | | |
| | | | | | More than 5 years| `very_long` | 2 | | |
| **hair_q5** | How would you describe the speed of your hair loss?| | `radio` | ` ` | Very Slow | `slow` | 8 | Progression Rate | 2.5 |
| | | | | | Moderate | `moderate` | 6 | | |
| | | | | | Fast | `fast` | 3 | | |
| | | | | | Very Fast | `very_fast` | 1 | | |
| **hair_q6** | Is there a history of hair loss in your family?| | `radio` | ` ` | No Family History | `none` | 9 | Genetic Factors | 2 |
| | | | | | Mother's Side | `mother` | 6 | | |
| | | | | | Father's Side | `father` | 5 | | |
| | | | | | Both Sides | `both` | 3 | | |
| **hair_q7** | What is your current stress level?| | `radio` | ` ` | Low Stress | `low` | 9 | Lifestyle Factors | 1.5 |
| | | | | | Moderate Stress | `moderate` | 7 | | |
| | | | | | High Stress | `high` | 4 | | |
| | | | | | Very High Stress | `very_high` | 2 | | |
| **hair_q8** | How would you rate your current diet quality? | | `radio` | ` ` | Excellent | `excellent` | 9 | Nutritional Support | 1.5 |
| | | | | | Good | `good` | 7 | | |
| | | | | | Fair | `fair` | 5 | | |
| | | | | | Poor | `poor` | 2 | | |
| **hair_q9** | Have you tried any hair loss treatments before?| | `radio` | ` ` | No Treatments | `none` | 7 | Treatment History | 1 |
| | | | | | Over-the-Counter | `otc` | 6 | | |
| | | | | | Prescription Meds| `prescription`| 5 | | |
| | | | | | Medical Procedures| `procedures` | 4 | | |
| **hair_q10**| What are your hair goals? | | `multiselect` | ` ` | Stop Hair Loss | `stop_loss` | 8 | Treatment Expectations | 1 |
| | | | | | Regrow Hair | `regrow` | 6 | | |
| | | | | | Thicken Hair | `thicken` | 7 | | |
| | | | | | Overall Improvement| `improve` | 8 | | |
| **hair_q11**| Let's get your contact information. | | `contact_info`| `contact_info`| N/A | N/A | N/A | N/A | N/A |

### 2.3 ED Treatment Assessment

| Question ID | Question | Description/Subtitle | Type | Global Key | Options | Answer Value | Points | Category | Weight |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **ed_q1** | What is your date of birth?| | `dob_dropdowns`| `user_dob_combined` | N/A | N/A | 0 | Demographics | 0 |
| **ed_q2** | What is your relationship status?| | `radio` | ` ` | Single | `single` | 6 | Psychosocial Factors | 1 |
| | | | | | Dating | `dating` | 7 | | |
| | | | | | Married/Partnered| `married` | 8 | | |
| | | | | | Divorced/Separated| `divorced` | 5 | | |
| **ed_q3** | How would you describe the severity of your ED?| | `radio` | ` ` | Mild | `mild` | 8 | Condition Severity | 3 |
| | | | | | Moderate | `moderate` | 6 | | |
| | | | | | Severe | `severe` | 3 | | |
| | | | | | Complete | `complete` | 1 | | |
| **ed_q4** | How long have you been experiencing symptoms of ED?| | `radio` | ` ` | Less than 6 months| `recent` | 8 | Timeline | 2 |
| | | | | | 6 months - 2 years| `moderate` | 6 | | |
| | | | | | 2-5 years | `long` | 4 | | |
| | | | | | More than 5 years| `very_long` | 2 | | |
| **ed_q5** | Do you have any of the following health conditions?| | `multiselect` | ` ` | None of these | `none` | 9 | Medical Factors | 2.5 |
| | | | | | Diabetes | `diabetes` | 4 | | |
| | | | | | Heart Disease | `heart` | 3 | | |
| | | | | | High Blood Pressure| `hypertension`| 4 | | |
| **ed_q6** | Have you tried any ED treatments before?| | `radio` | ` ` | No previous treatments| `none` | 7 | Treatment History | 1 |
| | | | | | Oral medications | `oral` | 6 | | |
| | | | | | Injections | `injections` | 5 | | |
| | | | | | Vacuum devices | `devices` | 5 | | |
| **ed_q7** | Do you smoke or use tobacco products?| | `radio` | ` ` | No | `no` | 9 | Medical Factors | 1.5 |
| | | | | | Yes, socially | `yes_socially`| 4 | | |
| | | | | | Yes, daily | `yes_daily` | 2 | | |
| | | | | | I am a former smoker| `former` | 6 | | |
| **ed_q8** | How often do you exercise?| | `radio` | ` ` | Never | `never` | 3 | Physical Health | 1.5 |
| | | | | | Rarely | `rarely` | 5 | | |
| | | | | | Regularly | `regularly` | 8 | | |
| | | | | | Daily | `daily` | 9 | | |
| **ed_q9** | How would you describe your current stress level?| | `radio` | ` ` | Low | `low` | 9 | Psychological Factors | 2 |
| | | | | | Moderate | `moderate` | 7 | | |
| | | | | | High | `high` | 4 | | |
| | | | | | Very High | `very_high` | 2 | | |
| **ed_q10**| What are your primary goals for seeking treatment?| | `multiselect`| ` ` | Restore function | `restore` | 8 | Treatment Motivation | 1 |
| | | | | | Boost confidence | `confidence` | 7 | | |
| | | | | | Improve performance | `performance`| 6 | | |
| | | | | | Improve relationship| `relationship`| 8 | | |
| **ed_q11**| Are you currently taking any of the following types of medications?| | `multiselect`| ` ` | No medications | `none` | 8 | Drug Interactions | 1.5 |
| | | | | | Blood pressure meds| `blood_pressure`| 5 | | |
| | | | | | Antidepressants | `antidepressants`| 4 | | |
| | | | | | Other medications | `other` | 6 | | |
| **ed_q12**| Let's get your contact information.| | `contact_info`| `contact_info`| N/A | N/A | N/A | N/A | N/A |

### 2.4 Weight Loss Assessment

| Question ID | Question | Description/Subtitle | Type | Global Key | Options | Answer Value | Points | Category | Weight |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **weight_q1**| What is your date of birth?| | `dob_dropdowns`| `user_dob_combined`| N/A | N/A | 0 | Demographics | 0 |
| **weight_q2**| What is your gender? | | `radio` | `gender` | Male | `male` | 5 | Demographics | 0.5 |
| | | | | | Female | `female` | 5 | | |
| **weight_q3**| What is your primary weight loss goal?| | `multiselect`| `health_goals`| Lose 10-20 lbs | `lose_10` | 8 | Motivation & Goals | 2 |
| | | | | | Lose 20-50 lbs | `lose_30` | 7 | | |
| | | | | | Lose 50+ lbs | `lose_50` | 6 | | |
| | | | | | Maintain current weight | `maintain` | 9 | | |
| **weight_q4**| What is your height and weight? | | `height_weight`| `height_weight`| N/A | N/A | 0 | Current Status | 0 |
| **weight_q5**| How often do you exercise? | | `radio` | ` ` | Never | `never` | 1 | Physical Activity | 2.5 |
| | | | | | 1-2 times/week | `rarely` | 3 | | |
| | | | | | 3-4 times/week | `often` | 8 | | |
| | | | | | 5+ times/week | `daily` | 9 | | |
| **weight_q6**| How would you describe your typical diet? | | `radio` | ` ` | Mostly Unhealthy | `unhealthy` | 2 | Nutrition | 3 |
| | | | | | Generally Balanced| `balanced` | 6 | | |
| | | | | | Very Healthy | `healthy` | 8 | | |
| | | | | | Strict Diet | `strict` | 7 | | |
| **weight_q7**| How many hours of sleep do you get per night?| | `radio` | ` ` | Less than 5 hours| `less_5` | 3 | Lifestyle Factors | 1.5 |
| | | | | | 5-6 hours | `5_6` | 5 | | |
| | | | | | 7-8 hours | `7_8` | 9 | | |
| | | | | | More than 8 hours| `more_8` | 8 | | |
| **weight_q8**| How would you rate your daily stress levels?| | `radio` | ` ` | Low | `low` | 9 | Psychological Factors| 1.5 |
| | | | | | Moderate | `moderate` | 7 | | |
| | | | | | High | `high` | 4 | | |
| | | | | | Very High | `very_high` | 2 | | |
| **weight_q9**| What has been your experience with weight loss in the past?| | `radio` | ` ` | Never had lasting success| `no_success`| 3 | Weight Loss History | 1 |
| | | | | | Some success, but gained it back | `some_success`| 4 | | |
| | | | | | Good success, maintained for a while| `good_success`| 6 | | |
| | | | | | This is my first serious attempt | `first_time`| 7 | | |
| **weight_q10**| Do you have any of these eating habits?| | `multiselect`| ` ` | Emotional eating| `emotional_eating`| 3 | Behavioral Patterns| 1 |
| | | | | | Late-night snacking | `late_night` | 4 | | |
| | | | | | Binge eating | `binge_eating`| 2 | | |
| | | | | | Sugary drinks | `sugary_drinks`| 3 | | |
| **weight_q11**| How motivated are you to make a change?| | `radio` | ` ` | Not very motivated| `not_motivated`| 2 | Motivation & Goals | 1 |
| | | | | | Somewhat motivated| `somewhat` | 4 | | |
| | | | | | Very motivated | `very_motivated`| 7 | | |
| | | | | | Committed and ready| `committed` | 9 | | |
| **weight_q12**| What kind of support system do you have?| | `radio` | ` ` | I'm on my own | `none` | 3 | Social Support | 1 |
| | | | | | Partner/Spouse | `partner` | 7 | | |
| | | | | | Family and Friends| `family` | 8 | | |
| | | | | | Professional (coach, etc.)| `professional`| 9 | | |
| **weight_q13**| Let's get your contact information.| | `contact_info`| `contact_info`| N/A | N/A | N/A | N/A | N/A |

### 2.5 Health Assessment

| Question ID | Question | Description/Subtitle | Type | Global Key | Options | Answer Value | Points | Category | Weight |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **health_q1** | What is your date of birth? | | `dob_dropdowns` | `user_dob_combined` | N/A | N/A | 0 | Demographics | 0 |
| **health_q2** | What is your gender? | | `radio` | `gender` | Male | `male` | 5 | Demographics | 0.5 |
| | | | | | Female | `female` | 5 | | |
| **health_q3** | How would you rate your overall health? | | `radio` | ` ` | Poor | `poor` | 2 | Current Health Status | 3 |
| | | | | | Fair | `fair` | 5 | | |
| | | | | | Good | `good` | 7 | | |
| | | | | | Excellent | `excellent`| 9 | | |
| **health_q4** | What is your height and weight? | | `height_weight` | `height_weight` | N/A | N/A | N/A | N/A | N/A |
| **health_q5** | How often do you engage in moderate to intense physical activity? | | `radio` | ` ` | Rarely or Never | `rarely` | 1 | Physical Activity | 2.5 |
| | | | | | 1-2 times a week | `sometimes`| 5 | | |
| | | | | | 3-5 times a week | `often` | 8 | | |
| | | | | | Almost every day | `daily` | 9 | | |
| **health_q6** | How would you describe your typical diet? | | `radio` | ` ` | High in processed foods | `processed`| 2 | Nutrition | 2.5 |
| | | | | | A typical Western diet | `average` | 5 | | |
| | | | | | Mostly whole foods | `healthy` | 7 | | |
| | | | | | Very clean, whole foods diet | `very_healthy`| 9 | | |
| **health_q7** | How would you rate your sleep quality? | | `radio` | ` ` | Poor, I wake up tired | `poor` | 3 | Sleep & Recovery | 2 |
| | | | | | Fair, could be better| `fair` | 5 | | |
| | | | | | Good, usually restful| `good` | 7 | | |
| | | | | | Excellent, I wake up refreshed | `excellent`| 9 | | |
| **health_q8** | How well do you manage stress? | | `radio` | ` ` | I don't manage it well| `poorly` | 3 | Stress & Mental Health | 2 |
| | | | | | I have some coping methods| `somewhat` | 5 | | |
| | | | | | I manage it well | `well` | 7 | | |
| | | | | | I have a proactive routine| `proactively`| 9 | | |
| **health_q9** | Do you get regular preventive care (e.g., check-ups)? | | `radio` | ` ` | Never or rarely | `never` | 2 | Preventive Health | 1.5 |
| | | | | | Only when I have a problem| `sometimes`| 6 | | |
| | | | | | I have regular annual check-ups| `regularly`| 9 | | |
| **health_q10**| What are your main health goals? | Select all that apply. | `multiselect` | `health_goals` | Live longer | `live_longer`| 9 | Health Motivation | 1 |
| | | | | | Boost energy | `boost_energy`| 8 | | |
| | | | | | Improve sleep | `improve_sleep`| 8 | | |
| | | | | | Lose weight | `lose_weight` | 7 | | |
| | | | | | Build muscle | `build_muscle`| 7 | | |
| | | | | | Sharpen focus & memory| `sharpen_focus`| 8 | | |
| | | | | | Balance hormones | `balance_hormones`| 9 | | |
| | | | | | Improve mood | `improve_mood` | 7 | | |
| | | | | | Boost libido & performance| `boost_libido`| 8 | | |
| | | | | | Support heart health| `support_heart`| 9 | | |
| | | | | | Manage menopause | `manage_menopause`| 8 | | |
| | | | | | Increase testosterone| `increase_test`| 8 | | |
| **health_q11**| Let's get your contact information. | | `contact_info` | `contact_info` | N/A | N/A | N/A | N/A | N/A |

### 2.6 Skin Assessment

| Question ID | Question | Description/Subtitle | Type | Global Key | Options | Answer Value | Points | Category | Weight |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **skin_q1** | What is your date of birth? | | `dob_dropdowns` | `user_dob_combined` | N/A | N/A | 0 | Demographics | 0 |
| **skin_q2** | What is your gender? | | `radio` | `gender` | Male | `male` | 5 | Demographics | 0.5 |
| | | | | | Female | `female` | 5 | | |
| **skin_q3** | What is your skin type? | | `radio` | ` ` | Normal | `normal` | 8 | Skin Characteristics | 2 |
| | | | | | Dry | `dry` | 6 | | |
| | | | | | Oily | `oily` | 6 | | |
| | | | | | Combination | `combination` | 7 | | |
| | | | | | Sensitive | `sensitive` | 5 | | |
| **skin_q4** | What is your primary skin concern? | | `multiselect` | ` ` | Acne & Blemishes | `acne` | 3 | Primary Skin Issue | 3 |
| | | | | | Fine Lines & Wrinkles | `aging` | 4 | | |
| | | | | | Dark Spots & Hyperpigmentation| `pigmentation`| 5 | | |
| | | | | | Redness & Rosacea | `redness` | 4 | | |
| | | | | | Dryness & Dehydration | `dullness` | 6 | | |
| **skin_q5** | How much sun exposure do you get? | | `radio` | ` ` | Rarely, I'm mostly indoors | `minimal` | 9 | Environmental Factors| 2.5 |
| | | | | | Sometimes, on weekends | `moderate` | 6 | | |
| | | | | | Daily, but I use sunscreen | `high` | 3 | | |
| **skin_q6** | How much water do you typically drink per day? | | `radio` | ` ` | Less than 4 glasses | `low` | 3 | Hydration | 1.5 |
| | | | | | 4-7 glasses | `medium` | 7 | | |
| | | | | | 8 or more glasses | `high` | 9 | | |
| **skin_q7** | What is your current skincare routine?| | `radio` | ` ` | None | `none` | 2 | Current Regimen | 1 |
| | | | | | Basic (cleanse, moisturize, SPF) | `basic` | 6 | | |
| | | | | | Advanced (serums, exfoliants, etc.)| `advanced`| 8 | | |
| **skin_q8** | Does your routine include active ingredients like retinoids or antioxidants? | | `radio` | ` ` | No | `no` | 5 | Advanced Care | 1.5 |
| | | | | | Yes | `yes` | 9 | | |
| | | | | | I'm not sure | `not_sure` | 4 | | |
| **skin_q9** | How does your skin typically react to new products?| | `multiselect` | ` ` | No reaction | `none` | 9 | Skin Reactivity | 1.5 |
| | | | | | Becomes red or flushed| `redness` | 4 | | |
| | | | | | I get breakouts | `breakouts` | 4 | | |
| | | | | | It feels itchy or irritated| `itchiness`| 3 | | |
| | | | | | Becomes dry and tight | `dryness` | 5 | | |
| **skin_q10** | Which of these lifestyle factors apply to you? | | `multiselect` | ` ` | I smoke | `smoker` | 3 | Lifestyle & Diet | 2 |
| | | | | | I have high stress levels | `high_stress`| 4 | | |
| | | | | | I have poor sleep quality| `poor_sleep` | 4 | | |
| | | | | | My diet is high in sugar/processed foods| `high_sugar_diet`| 3 | | |
| **skin_q11** | Let's get your contact information. | | `contact_info` | `contact_info` | N/A | N/A | N/A | N/A | N/A |

### 2.7 Sleep Assessment

| Question ID | Question | Description/Subtitle | Type | Global Key | Options | Answer Value | Points | Category | Weight |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **sleep_q1** | On average, how many hours of sleep do you get per night?| | `radio` | ` ` | Less than 5 hours | `less_than_5`| 2 | Sleep Duration | 2.5 |
| | | | | | 5-6 hours | `5_to_6` | 4 | | |
| | | | | | 7-8 hours (Recommended)| `7_to_8` | 9 | | |
| | | | | | 9 or more hours | `more_than_9`| 7 | | |
| **sleep_q2** | How would you rate the quality of your sleep?| | `radio` | ` ` | Very Poor | `very_poor` | 1 | Sleep Quality | 3 |
| | | | | | Poor | `poor` | 3 | | |
| | | | | | Fair | `fair` | 5 | | |
| | | | | | Good | `good` | 8 | | |
| **sleep_q3** | How often do you wake up during the night?| | `radio` | ` ` | Frequently (3+ times)| `frequently` | 2 | Sleep Continuity | 2 |
| | | | | | Sometimes (1-2 times)| `sometimes` | 5 | | |
| | | | | | Rarely | `rarely` | 8 | | |
| | | | | | Never | `never` | 9 | | |
| **sleep_q4** | Once in bed, how long does it take you to fall asleep?| | `radio` | ` ` | > 45 minutes | `long` | 2 | Sleep Latency | 2 |
| | | | | | 30-45 minutes | `moderate` | 5 | | |
| | | | | | 15-30 minutes | `short` | 8 | | |
| | | | | | < 15 minutes | `very_short` | 9 | | |
| **sleep_q5** | How often do you feel drowsy or have the urge to nap during the day? | | `radio` | ` ` | Often | `often` | 2 | Daytime Function | 2.5 |
| | | | | | Sometimes | `sometimes` | 5 | | |
| | | | | | Rarely or Never | `rarely` | 9 | | |
| **sleep_q6** | Which do you do within an hour of your intended bedtime?| Select all that apply. | `multiselect`| ` ` | Use phone/TV/computer | `screen_time` | 3 | Sleep Hygiene | 1.5 |
| | | | | | Consume caffeine | `caffeine` | 2 | | |
| | | | | | Eat a large meal | `large_meal` | 4 | | |
| | | | | | Engage in vigorous exercise | `exercise` | 4 | | |
| **sleep_q7** | Which of the following do you use to help you sleep? | Select all that apply. | `multiselect` | ` ` | None | `none` | 9 | Sleep Dependency | 1.5 |
| | | | | | Melatonin | `melatonin` | 6 | | |
| | | | | | Herbal supplements (e.g., Valerian)| `herbal_supplements`| 5 | | |
| | | | | | Over-the-counter sleep aids| `otc_sleep_aids` | 4 | | |
| | | | | | Prescription sleep medication| `prescription_meds`| 2 | | |
| **sleep_q8** | Let's get your contact information.| | `contact_info`| `contact_info`| N/A | N/A | N/A | N/A | N/A |

### 2.8 Hormone Assessment

| Question ID | Question | Description/Subtitle | Type | Global Key | Options | Answer Value | Points | Category | Weight |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **hormone_q1**| Which of these symptoms have you been experiencing?| Select all that apply. | `multiselect` | ` ` | Fatigue or lack of energy| `fatigue_energy`| 2 | Symptom Severity | 3 |
| | | | | | Unexplained weight gain...| `weight_fluctuations`| 2 | | |
| | | | | | Mood swings...| `mood_swings` | 2 | | |
| | | | | | Decreased sex drive | `libido_changes`| 2 | | |
| | | | | | Difficulty sleeping | `sleep_issues` | 2 | | |
| | | | | | Changes in skin or hair...| `skin_hair_changes`| 2 | | |
| | | | | | None of the above | `none` | 9 | | |
| **hormone_q2**| In the last month, have you frequently experienced: | Select all that apply. | `multiselect` | ` ` | Irritability | `irritability` | 4 | Mood & Cognition | 2.5 |
| | | | | | Anxiety | `anxiety` | 4 | | |
| | | | | | Low Mood | `low_mood` | 3 | | |
| | | | | | Brain Fog | `brain_fog` | 3 | | |
| **hormone_q3**| How would you describe your energy levels?| | `radio` | ` ` | Consistently low | `consistently_low`| 2 | Vitality | 2 |
| | | | | | I crash in the afternoon| `afternoon_crash`| 4 | | |
| | | | | | Generally stable | `stable` | 7 | | |
| | | | | | High and consistent | `high_energy` | 9 | | |
| **hormone_q4** | How would you describe your ability to focus on complex tasks? | | `radio` | ` ` | Very difficult | `difficult` | 3 | Mental Acuity | 2 |
| | | | | | Somewhat difficult | `somewhat` | 5 | | |
| | | | | | No issues with focus | `no_issues` | 9 | | |
| **hormone_q5**| How many servings of cruciferous vegetables do you eat per week? | (e.g., broccoli, cauliflower, cabbage) | `radio` | ` ` | 0-1 servings | `zero_one` | 3 | Diet & Lifestyle | 1.5 |
| | | | | | 2-3 servings | `two_three` | 6 | | |
| | | | | | 4+ servings | `four_plus` | 9 | | |
| **hormone_q6**| Let's get your contact information.| | `contact_info`| `contact_info`| N/A | N/A | N/A | N/A | N/A |

### 2.9 Menopause Assessment

| Question ID | Question | Description/Subtitle | Type | Global Key | Options | Answer Value | Points | Category | Weight |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **menopause_q1**| Which of the following best describes your current stage? | | `radio` | ` ` | I have not yet started menopause| `not_started` | 9 | Menopause Stage | 1 |
| | | | | | Perimenopause | `perimenopause`| 5 | | |
| | | | | | Menopause | `menopause` | 3 | | |
| | | | | | Post-menopause | `post_menopause`| 4 | | |
| **menopause_q2**| Which of these symptoms are you experiencing?| Select all that apply. | `multiselect` | ` ` | Hot flashes | `hot_flashes` | 2 | Symptom Severity | 3 |
| | | | | | Night sweats | `night_sweats`| 2 | | |
| | | | | | Sleep disturbances| `sleep_disturbances`| 2 | | |
| | | | | | Mood changes or irritability| `mood_changes`| 2 | | |
| | | | | | Vaginal dryness | `vaginal_dryness`| 2 | | |
| | | | | | None of the above | `none` | 9 | | |
| **menopause_q3**| In the last month, have you frequently experienced:| Select all that apply. | `multiselect` | ` ` | Irritability or Anxiety | `mood_issues` | 3 | Mood & Cognition | 2.5 |
| | | | | | Low Mood or Depression | `low_mood` | 3 | | |
| | | | | | Brain Fog or Memory Lapses | `brain_fog` | 2 | | |
| **menopause_q4**| How would you describe your ability to recover from exercise?| | `radio` | ` ` | Slower than it used to be | `slower` | 4 | Physical Performance | 2 |
| | | | | | About the same | `same` | 7 | | |
| | | | | | I recover quickly | `quickly` | 9 | | |
| **menopause_q5**| Have you noticed changes in how your body stores fat?| | `radio` | ` ` | No change | `no_change` | 8 | Body Composition | 2 |
| | | | | | Minor increase, especially abdomen | `minor` | 5 | | |
| | | | | | Significant increase | `significant` | 2 | | |
| **menopause_q6**| Are you currently using or have you previously used HRT? | | `radio` | ` ` | Never | `never` | 7 | Treatment History | 1.5 |
| | | | | | Currently using | `currently` | 5 | | |
| | | | | | Used in the past | `previously` | 6 | | |
| **menopause_q7**| Let's get your contact information. | | `contact_info` | `contact_info`| N/A | N/A | N/A | N/A | N/A |

### 2.10 Testosterone Assessment

| Question ID | Question | Description/Subtitle | Type | Global Key | Options | Answer Value | Points | Category | Weight |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **testosterone_q1**| Which of the following symptoms...apply to you? | Select all that apply. | `multiselect`| ` ` | Low sex drive (libido)| `low_libido` | 2 | Symptom Severity | 3 |
| | | | | | Lack of energy or fatigue| `fatigue` | 2 | | |
| | | | | | Reduced muscle mass...| `reduced_muscle`| 2 | | |
| | | | | | Increase in body fat | `increased_fat` | 2 | | |
| | | | | | Difficulty with erections| `erectile_dysfunction`| 2 | | |
| | | | | | None of the above | `none` | 9 | | |
| **testosterone_q2**| In the last month, have you frequently experienced: | Select all that apply. | `multiselect` | ` ` | Irritability | `irritability` | 4 | Mood & Cognition | 2.5 |
| | | | | | Lack of Motivation| `low_motivation`| 3 | | |
| | | | | | "Brain Fog" | `brain_fog` | 3 | | |
| **testosterone_q3**| How would you describe your physical endurance or stamina? | | `radio` | ` ` | Decreased significantly | `decreased` | 3 | Physical Performance | 2 |
| | | | | | Somewhat decreased | `somewhat` | 5 | | |
| | | | | | Has not changed | `no_change` | 8 | | |
| **testosterone_q4**| How would you describe your ability to build...muscle? | | `radio` | ` ` | Very difficult | `very_difficult`| 2 | Anabolic Response | 2 |
| | | | | | Somewhat difficult| `somewhat_difficult`| 4 | | |
| | | | | | Moderate | `moderate` | 6 | | |
| | | | | | Relatively easy | `easy` | 8 | | |
| **testosterone_q5**| How would you describe your energy levels and motivation?| | `radio` | ` ` | Very low | `very_low` | 2 | Vitality & Drive | 2 |
| | | | | | Lower than usual | `lower_than_usual`| 4 | | |
| | | | | | Normal | `normal` | 7 | | |
| | | | | | High | `high` | 9 | | |
| **testosterone_q6**| Let's get your contact information. | | `contact_info`| `contact_info`| N/A | N/A | N/A | N/A | N/A |

---

## 3.0 Qualitative Engine: The Symptom Qualification Engine

This section details the new assessment and logic that measures a user's health *reality*. This engine provides a far more nuanced and accurate picture than a simple symptom checklist.

*Note: The Health Optimization assessment will be defined in the configuration with the flag `'assessment_engine' => 'qualitative'` to ensure it is processed by the correct engine.*

### 3.1 User Experience: Two-Stage Symptom Qualification

The user will be presented with a dynamic, two-stage assessment:
1.  **Stage 1: Symptom Identification:** The user selects all symptoms they are currently experiencing from a high-level list.
2.  **Stage 2: Symptom Qualification:** For *each symptom selected*, a sub-form immediately appears, prompting the user to provide crucial context by qualifying the symptom's **Severity** and **Frequency**.

### 3.2 Health Optimization Assessment Questions

This assessment is a single logical unit, but is presented to the user in two dynamic stages.

#### Stage 1: Symptom Identification

| Question ID | Question | Description/Subtitle | Type | Options (Select all that apply) |
| :--- | :--- | :--- | :--- | :--- |
| `symptom_q1` | How have your overall energy levels been feeling lately? | | `multiselect` | Fatigue, Chronic Fatigue |
| `symptom_q2` | Have you noticed any lack of drive or motivation? | | `multiselect` | Lack of Motivation, Procrastination, Apathy or Indifference |
| `symptom_q3` | Are you finding it harder to stay active or perform physically?| | `multiselect` | Reduced Physical Performance, Decreased Physical Activity |
| `symptom_q4` | How's your sleep been? | | `multiselect` | Poor Sleep, Sleep Problems, Sleep Disturbance |
| `symptom_q5` | Have you been dealing with any night-time discomforts? | | `multiselect` | Night Sweats, Feeling Overheated During the Day, Excessive Sweating (General) |
| `symptom_q6` | What about your mood—any ups and downs or changes lately? | | `multiselect` | Mood Swings, Mood Changes, Change in Personality |
| `symptom_q7` | Are you feeling more anxious, depressed, or irritable? | | `multiselect` | Anxiety, Depression, Irritability |
| `symptom_q8` | How's your self-confidence or esteem been holding up? | | `multiselect` | Low Self-Esteem, Negative Self-Talk, Feeling of Worthlessness |
| `symptom_q9` | Have you experienced any fog or confusion in your thinking? | | `multiselect` | Brain Fog, Confusion, Cognitive Decline |
| `symptom_q10`| Any trouble with memory or concentration? | | `multiselect` | Memory Loss, Poor Concentration |
| `symptom_q11`| Have you noticed issues with language or coordination? | | `multiselect` | Language Problems, Poor Coordination |
| `symptom_q12`| What about your libido or sexual health—any changes there?| | `multiselect` | Low Sex Drive (Libido), Decreased Spontaneous Arousal, Difficulty Reaching Orgasm |
| `symptom_q13`| Are you experiencing any sexual function difficulties? | | `multiselect` | Erectile Dysfunction, Vaginal Dryness, Infertility |
| `symptom_q14`| Have you had any hot flashes or similar sensations? | | `multiselect` | Hot Flashes, Sudden Chills, Facial Flushing |
| `symptom_q15`| Let's talk about physical strength—any weakness or loss of muscle?| | `multiselect` | Muscle Weakness, Weakness, Muscle Loss, Muscle Mass Loss |
| `symptom_q16`| How's your mobility and balance been? | | `multiselect` | Decreased Mobility, Poor Balance |
| `symptom_q17`| After activity, do you find recovery taking longer? | | `multiselect` | Slow Recovery, Prolonged Soreness |
| `symptom_q18`| Any joint pain or related discomforts? | | `multiselect` | Joint Pain, Joint Stiffness, Muscle Aches |
| `symptom_q19`| On the heart side, have you felt any chest pain or shortness of breath?| | `multiselect` | Chest Pain, Shortness of Breath |
| `symptom_q20`| What about palpitations, lightheadedness, or swelling? | | `multiselect` | Palpitations, Lightheadedness, Swelling |
| `symptom_q21`| How's your exercise tolerance—any issues pushing yourself?| | `multiselect` | Poor Exercise Tolerance, Out of Breath Easily, Fatigue After Minimal Exertion |
| `symptom_q22`| Have you noticed changes in your blood pressure? | | `multiselect` | Known High Blood Pressure, Dizziness or Lightheadedness, Frequent Headaches |
| `symptom_q23`| Let's check on body composition—any increases in fat? | | `multiselect` | Increased Body Fat, Abdominal Fat Gain, Weight Changes |
| `symptom_q24`| Is your metabolism feeling slower, or any blood sugar irregularities?| | `multiselect` | Slow Metabolism, Blood Glucose Dysregulation |
| `symptom_q25`| Finally, have you been getting sick more often, or noticing skin issues?| | `multiselect` | Frequent Illness, Itchy Skin, Slow Healing Wounds |

### 3.3 Symptom-to-Vector Map

Each identified symptom is mapped to one or more **Health Optimization Vectors**.

| Symptom | Mapped Vector(s) |
| :--- | :--- |
| Abdominal Fat Gain | Weight Loss |
| Anxiety | Hormones |
| Blood Glucose Dysregulation | Weight Loss |
| Brain Fog | Energy, Cognitive Health |
| Change in Personality | Cognitive Health |
| Chest Pain | Heart Health |
| Chronic Fatigue | Longevity |
| Cognitive Decline | Longevity |
| Confusion | Cognitive Health |
| Decreased Mobility | Strength |
| Decreased Physical Activity | Longevity |
| Depression | Hormones |
| Erectile Dysfunction | Hormones, Heart Health, Libido |
| Fatigue | Energy, Heart Health, Weight Loss, Strength |
| Frequent Illness | Energy, Longevity |
| High Blood Pressure | Weight Loss |
| Hot Flashes | Hormones |
| Increased Body Fat | Weight Loss |
| Infertility | Hormones, Libido |
| Irritability | Hormones |
| Itchy Skin | Longevity |
| Joint Pain | Weight Loss, Strength |
| Joint Stiffness | Strength, Longevity |
| Lack of Motivation | Energy, Mind |
| Language Problems | Cognitive Health |
| Lightheadedness | Heart Health |
| Low Libido | Hormones, Libido |
| Low Self-Esteem | Libido |
| Memory Loss | Cognitive Health |
| Mood Changes | Cognitive Health |
| Mood Swings | Hormones |
| Muscle Aches | Strength, Energy |
| Muscle Loss | Strength, Longevity |
| Muscle Mass Loss | Strength |
| Muscle Weakness | Energy |
| Night Sweats | Hormones |
| Out of Breath Easily | Heart Health, Energy |
| Palpitations | Heart Health |
| Poor Balance | Strength |
| Poor Concentration | Cognitive Health |
| Poor Coordination | Cognitive Health |
| Poor Exercise Tolerance | Heart Health |
| Poor Sleep | Energy |
| Prolonged Soreness | Strength |
| Reduced Physical Performance | Energy, Weight Loss |
| Shortness of Breath | Heart Health |
| Sleep Disturbance | Cognitive Health |
| Sleep Problems | Weight Loss |
| Slow Healing Wounds | Longevity |
| Slow Metabolism | Weight Loss |
| Slow Recovery | Strength |
| Swelling | Heart Health |
| Sudden Chills | Hormones |
| Vaginal Dryness | Hormones, Libido |
| Weakness | Strength |
| Weight Changes | Longevity |

### 3.4 Pillar Integrity Penalty Matrix

The core of the engine. It uses the user's qualified responses (Severity and Frequency) for each Vector to determine the precise penalty to apply to the corresponding Pillar. The highest penalty per pillar is the one that is used.

| Health Optimization Vector | Severity | Frequency | Pillar Impacted | Penalty Value |
| :--- | :--- | :--- | :--- | :--- |
| **Heart Health** | Severe | Daily | **Body** | **-20%** |
| | Severe | A few times a week | | -18% |
| | Moderate | Daily | | -15% |
| | ... | ... | | ... |
| **Cognitive Health**| Severe | Daily | **Mind** | **-20%** |
| | ... | ... | | ... |
| **Hormones** | Severe | Daily | **Body** | -10% |
| | ... | ... | | ... |
| **Weight Loss** | Severe | Daily | **Lifestyle** | -10% |
| | ... | ... | | ... |
| **Strength** | Severe | Daily | **Body** | -10% |
| | ... | ... | | ... |
| **Longevity** | Severe | Daily | **Lifestyle** | -10% |
| | ... | ... | | ... |
| **Energy** | Severe | Daily | **Lifestyle** | -8% |
| | Moderate | Daily | | -6% |
| | ... | ... | | ... |
| **Libido** | Severe | Daily | **Mind** | -8% |
| | ... | ... | | ... |

*(Note: This is an illustrative, not exhaustive, representation of the final penalty matrix.)* 