const rules = {
  'dr_elena_harmonix': ['glucose', 'hba1c', 'testosterone', 'low libido', 'mood swings', 'anxiety', 'hormones', 'thyroid', 'metabolic', 'tsh', 't4', 't3', 'estradiol', 'progesterone', 'cortisol', 'dhea', 'prolactin', 'lh', 'fsh', 'shbg', 'igf-1', 'insulin', 'homa-ir', 'leptin', 'ghrelin', 'vitamin d', 'night sweats', 'hot flashes', 'erectile dysfunction', 'vaginal dryness', 'infertility', 'slow metabolism', 'blood glucose dysregulation', 'fatigue', 'depression', 'irritability'],
  
  'dr_victor_pulse': ['blood pressure', 'cholesterol', 'apob', 'chest pain', 'palpitations', 'heart', 'cardiovascular', 'triglycerides', 'hdl', 'vldl', 'ldl', 'hs-crp', 'homocysteine', 'lp(a)', 'omega-3', 'heart rate', 'shortness of breath', 'lightheadedness', 'swelling', 'poor exercise tolerance', 'high blood pressure'],
  
  'dr_renata_flux': ['bun', 'creatinine', 'gfr', 'kidney', 'liver', 'electrolytes', 'swelling', 'bun/creatinine ratio', 'sodium', 'potassium', 'chloride', 'carbon dioxide', 'calcium', 'magnesium', 'protein', 'albumin', 'alkaline phosphate', 'ast', 'alt', 'uric acid', 'weakness', 'slow healing wounds'],
  
  'dr_harlan_vitalis': ['wbc', 'rbc', 'hemoglobin', 'blood count', 'cbc', 'immune', 'frequent illness', 'hematocrit', 'mcv', 'mch', 'mchc', 'rdw', 'platelets', 'ferritin', 'slow healing wounds', 'weakness'],
  
  'dr_nora_cognita': ['brain fog', 'memory loss', 'cognitive decline', 'apoe', 'neurology', 'brain', 'homocysteine', 'omega-3', 'folate', 'confusion', 'poor concentration', 'language problems', 'poor coordination', 'mood changes', 'personality change', 'sleep disturbance'],
  
  'dr_linus_eternal': ['telomeres', 'nad+', 'longevity', 'aging', 'chronic fatigue', 'muscle loss', 'tac', 'uric acid', 'gut microbiota', 'mirna-486', 'igf-1', 'il-6', 'il-18', 'decreased physical activity', 'weight changes', 'itchy skin', 'slow healing wounds', 'frequent illness'],
  
  'dr_silas_apex': ['performance', 'strength', 'grip strength', 'muscle weakness', 'joint pain', 'sports', 'weight', 'bmi', 'body fat', 'waist measurement', 'neck measurement', 'temperature', 'creatine kinase', 'il-6', 'il-18', 'reduced physical performance', 'muscle mass loss', 'decreased mobility', 'poor balance', 'slow recovery', 'prolonged soreness', 'decreased physical activity'],
  
  'dr_mira_insight': ['anxiety', 'depression', 'mood swings', 'irritability', 'mental health', 'psychology', 'cortisol', 'vitamin d', 'folate', 'lack of motivation', 'low self-esteem', 'poor sleep', 'sleep problems', 'night sweats', 'confusion'],
  
  'alex_dataforge': ['data science', 'analytics', 'ai analysis', 'trends', 'correlations', 'machine learning', 'ml', 'neural networks', 'big data', 'statistics', 'pearson correlation', 'predict outcomes', 'models', 'algorithms'],
  
  'coach_aria_vital': ['lifestyle', 'wellness', 'habits', 'weight loss', 'health coaching', 'pillars', 'abdominal fat gain', 'atomic habits', 'action plans', 'motivate', 'track progress', 'personalize', 'adherence'],
  
  'dr_orion_nexus': ['coordination', 'interdisciplinary', 'holistic', 'general practice', 'multi-agent', 'overlaps', 'fatigue across domains', 'synthesize', 'referrals', 'holistic reports', 'prevent silos', 'anticipate conflicts'],
  
  'matt_codeweaver': ['wordpress', 'plugins', 'themes', 'cms', 'php', 'open-source', 'site optimization', 'customization', 'best practices', 'guidelines', 'community builder'],
  
  'grace_sysforge': ['systems', 'infrastructure', 'os', 'networks', 'scalability', 'engineering', 'ieee', 'reliability', 'best practices', 'naval analogies'],
  
  'geoffrey_datamind': ['machine learning', 'ml', 'neural networks', 'analytics', 'ai', 'data science', 'hinton', 'neural pathways', 'stats', 'predict outcomes', 'models', 'algorithms'],
  
  'brendan_fullforge': ['fullstack', 'frontend', 'backend', 'database', 'deployment', 'javascript', 'js', 'web stacks', 'ecosystem', 'paradigms', 'standards', 'builds', 'bugs', 'updates'],
  
  'ken_backendian': ['backend', 'api', 'server', 'database', 'security', 'unix', 'systems', 'structures', 'optimizations', 'vulnerabilities', 'front-ends'],
  
  'jeffrey_webzen': ['frontend', 'html', 'css', 'responsive', 'accessibility', 'web standards', 'w3c', 'aesthetics', 'function', 'testing', 'accessible stories'],
  
  'don_uxmaster': ['ux', 'ui', 'wireframes', 'prototypes', 'user flows', 'usability', 'hcd', 'user needs', 'iterations', 'inclusivity', 'everyday experiences'],
  
  'paul_graphicon': ['graphic design', 'logos', 'branding', 'visuals', 'layouts', 'design history', 'trends', 'icons', 'impact', 'strategy', 'eternal symbols'],
  
  'david_creativus': ['creative direction', 'campaigns', 'vision', 'team leadership', 'ad principles', 'classics', 'innovations', 'risks', 'feedback', 'persuasive art'],
  
  'ogilvy_wordcraft': ['copywriting', 'ads', 'content', 'seo', 'narratives', 'direct response', 'hooks', 'refinements', 'ethics', 'sales sage'],
  
  'thelma_editrix': ['video editing', 'cuts', 'effects', 'pacing', 'post-production', 'film techniques', 'oscars', 'enhancements', 'revisions', 'story breaths'],
  
  'henry_projmaster': ['project management', 'planning', 'timelines', 'teams', 'risks', 'pmbok', 'tasks', 'coordinate', 'holistic plans', 'conflicts', 'charts'],
  
  'ann_execaid': ['executive assistance', 'scheduling', 'logistics', 'support', 'high-level ops', 'action plans', 'motivate', 'track progress', 'adherence', 'small wins'],
  
  'grace_projhelper': ['project assistance', 'coordination', 'documentation', 'support', 'project standards', 'tasks', 'history', 'efficiency', 'trends', 'educate', 'bug fixes'],
  
  'albert_scihelm': ['scientific direction', 'research', 'teams', 'innovation', 'scientific method', 'discoveries', 'nobel', 'ethics', 'bias', 'curiosity'],
  
  'carl_mathgenius': ['mathematics', 'theory', 'statistics', 'applications', 'proofs', 'equations', 'models', 'predict', 'complexity', 'distributions'],
  
  'isaac_sciquest': ['science', 'experiments', 'theories', 'discovery', 'scientific laws', 'hypotheses', 'classics', 'tests', 'evidence', 'gravity'],
  
  'will_taleweaver': ['storytelling', 'narratives', 'plots', 'engagement', 'literary classics', 'arcs', 'plays', 'twists', 'empathy', 'human mirrors'],
  
  'seth_netmarketer': ['internet marketing', 'seo', 'content', 'strategies', 'digital', 'permission marketing', 'trends', 'stats', 'growth', 'ethics', 'tactics', 'purple cows'],
  
  'gary_responsor': ['direct response', 'copy', 'funnels', 'conversions', 'mail mastery', 'calls-to-action', 'letters', 'tests', 'spam', 'value', 'hooks'],
  
  'dale_saleslord': ['sales direction', 'teams', 'pipelines', 'closes', 'influence principles', 'rapport', 'books', 'strategies', 'trust', 'friendships'],
  
  'zig_stratmaster': ['sales strategy', 'planning', 'psychology', 'growth', 'closing techniques', 'assessment', 'studies', 'routines', 'pitches', 'burnout', 'secrets'],
  
  'philip_markhelm': ['marketing direction', 'oversight', 'campaigns', 'roi', 'kotler', 'plans', 'coordinate', 'holistic views', 'silos', 'trends', 'management'],
  
  'seth_markstrat': ['marketing strategy', 'digital', 'growth', 'innovation', 'permission principles', 'forecast', 'books', 'ethics', 'reviews', 'hype', 'ideas'],
  
  'daniel_eqguide': ['emotional intelligence', 'self-awareness', 'empathy', 'leadership', 'ei models', 'emotions', 'studies', 'practices', 'wellness', 'inner mountains'],
  
  'lincoln_successor': ['customer success', 'retention', 'ltv', 'expansion', 'cs models', 'journeys', 'metrics', 'churn', 'strategies', 'frameworks'],
  
  'thurgood_healthlaw': ['healthcare law', 'regulations', 'ethics', 'compliance', 'hipaa', 'rights', 'cases', 'advice', 'protections', 'conflicts', 'reforms'],
  
  'lawrence_softlaw': ['software law', 'ip', 'licenses', 'ethics', 'open source', 'creative commons', 'rights', 'cases', 'protections', 'code'],
  
  'edwards_qualguard': ['quality assurance', 'processes', 'testing', 'standards', 'tqm', 'audits', 'cycles', 'optimizations', 'trends', 'teams', 'continuous improvement'],
  
  'sigmund_psychmind': ['psychology', 'behaviors', 'therapies', 'insights', 'dsm', 'psych theories', 'feelings', 'studies', 'mindfulness', 'confidentiality', 'hidden depths']
};

function selectRule(input) {
  let bestMatch = null;
  let maxMatches = 0;
  
  for (const [rule, keywords] of Object.entries(rules)) {
    const matches = keywords.filter(k => 
      input.toLowerCase().includes(k.toLowerCase())
    ).length;
    
    if (matches > maxMatches) {
      bestMatch = rule;
      maxMatches = matches;
    }
  }
  
  return bestMatch ? `.cursor/rules/${bestMatch}.mdc` : null;
}

// Usage: In Cursor terminal, run node rule_selector.js "input text" to get the rule file.
const input = process.argv[2];
if (input) {
  const result = selectRule(input);
  console.log(result);
} else {
  console.log('Usage: node rule_selector.js "your input text"');
  console.log('Example: node rule_selector.js "analyze glucose levels"');
} 