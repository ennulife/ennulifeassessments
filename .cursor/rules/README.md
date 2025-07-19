# AI Employee System - Comprehensive Guide

## Overview

This directory contains 41 specialized AI employee rule files, each representing a different domain expert. The system is designed to automatically route user queries to the most appropriate specialist based on keywords and context.

## File Structure

### Core Files
- `auto_selector.md` - Master router that analyzes input and selects the appropriate employee
- `rule_selector.js` - JavaScript script for advanced keyword matching (in project root)

### Employee Categories

#### Health & Medical Specialists (10)
1. **Dr. Elena Harmonix** - Endocrinology (hormones, thyroid, metabolic)
2. **Dr. Victor Pulse** - Cardiology (heart health, cardiovascular)
3. **Dr. Renata Flux** - Nephrology/Hepatology (kidney/liver function)
4. **Dr. Harlan Vitalis** - Hematology (blood count, immune system)
5. **Dr. Nora Cognita** - Neurology (cognitive health, brain function)
6. **Dr. Linus Eternal** - Gerontology (longevity, aging markers)
7. **Dr. Silas Apex** - Sports Medicine (performance, strength, physical)
8. **Dr. Mira Insight** - Psychiatry/Psychology (mental health, mood)
9. **Coach Aria Vital** - Health Coaching (lifestyle integration)
10. **Dr. Orion Nexus** - General Practitioner Coordinator (interdisciplinary)

#### Technical & Development (11)
11. **Matt Codeweaver** - WordPress Development
12. **Grace Sysforge** - Systems Engineering
13. **Geoffrey Datamind** - Data Science (ML/AI)
14. **Brendan Fullforge** - Full Stack Development
15. **Ken Backendian** - Back End Development
16. **Jeffrey Webzen** - Front End Website Design
17. **Don UXmaster** - Front End App UI/UX Design
18. **Paul Graphicon** - Graphic Design
19. **David Creativus** - Creative Direction
20. **Ogilvy Wordcraft** - Copywriting
21. **Thelma Editrix** - Video Editing

#### Project & Operations (3)
22. **Henry Projmaster** - Project Management
23. **Ann Execaid** - Executive Assistant
24. **Grace Projhelper** - Project Assistant

#### Scientific & Research (4)
25. **Albert Scihelm** - Scientific Direction
26. **Carl Mathgenius** - Mathematics
27. **Isaac Sciquest** - Science
28. **Will Taleweaver** - Storytelling

#### Marketing & Sales (6)
29. **Seth Netmarketer** - Internet Marketing
30. **Gary Responsor** - Direct Response Marketing
31. **Dale Saleslord** - Director of Sales
32. **Zig Stratmaster** - Sales Strategy
33. **Philip Markhelm** - Director of Marketing
34. **Seth Markstrat** - Marketing Strategy

#### Leadership & Support (6)
35. **Daniel EQguide** - Director of Emotional Intelligence
36. **Lincoln Successor** - Director of Customer Success
37. **Thurgood Healthlaw** - Healthcare Legal Counsel
38. **Lawrence Softlaw** - Software Legal Counsel
39. **Edwards Qualguard** - Quality Assurance
40. **Sigmund Psychmind** - Psychology Expert

#### Data & Analytics (1)
41. **Alex Dataforge** - Data Science (analytics, trends)

## How to Use

### Method 1: Automatic Selection (Recommended)
The `auto_selector.md` rule is set to `type: Always`, meaning it will automatically analyze every user input and route to the appropriate specialist.

**Example queries that will auto-route:**
- "My glucose levels are high" → Dr. Elena Harmonix
- "WordPress plugin development" → Matt Codeweaver
- "UX design for mobile apps" → Don UXmaster
- "Sales pipeline optimization" → Dale Saleslord

### Method 2: Manual Selection
You can manually attach specific employee rules using Cursor's rule system:

1. Open the command palette (Cmd/Ctrl + Shift + P)
2. Type "Cursor: Attach Rule"
3. Select the appropriate employee file

### Method 3: JavaScript Rule Selector
For advanced keyword matching, use the `rule_selector.js` script:

```bash
# Test the selector
node rule_selector.js "analyze glucose levels and testosterone"
# Output: .cursor/rules/dr_elena_harmonix.mdc

node rule_selector.js "WordPress plugin development"
# Output: .cursor/rules/matt_codeweaver.mdc
```

## Rule File Format

Each employee rule file follows this structure:

```yaml
---
description: Brief description with key keywords
type: Agent Requested
globs: ['*.relevant', '*.file', '*.patterns']
---

[Full employee prompt with personality, expertise, and guidelines]
```

## Keyword Matching System

### Health Keywords (Examples)
- **Endocrinology**: glucose, hba1c, testosterone, low libido, mood swings, anxiety
- **Cardiology**: blood pressure, cholesterol, apoB, chest pain, palpitations
- **Neurology**: brain fog, memory loss, cognitive decline, ApoE

### Technical Keywords (Examples)
- **WordPress**: wordpress, plugins, themes, cms, php
- **UX/UI**: ux, ui, wireframes, prototypes, user flows
- **Data Science**: machine learning, ml, neural networks, analytics

### Business Keywords (Examples)
- **Sales**: sales direction, teams, pipelines, closes
- **Marketing**: marketing strategy, digital, growth, innovation
- **Project Management**: project management, planning, timelines, teams

## Best Practices

1. **Specific Queries**: Use specific keywords for better routing
2. **Context Matters**: The system considers context, not just individual words
3. **Multiple Matches**: If multiple employees match, the most specific one is selected
4. **Clarification**: If unclear, the system will ask for clarification

## Customization

### Adding New Employees
1. Create a new `.mdc` file in `.cursor/rules/`
2. Follow the established format with YAML metadata
3. Add keywords to the `auto_selector.mdc` file
4. Update `rule_selector.js` with new keywords

### Modifying Keywords
1. Edit the `auto_selector.mdc` file
2. Update the corresponding employee's rule file
3. Modify the `rule_selector.js` script

## Troubleshooting

### Rule Not Matching
- Check if keywords are in the `auto_selector.mdc` file
- Verify the employee's rule file exists
- Test with the JavaScript selector

### Multiple Matches
- The system prioritizes the most specific match
- For ambiguous queries, manually select the desired employee

### Performance
- The JavaScript selector is optimized for speed
- Large keyword lists are cached for efficiency

## Integration with Cursor

This system integrates seamlessly with Cursor's rule system:

1. **Automatic Routing**: The `auto_selector.md` rule handles most cases
2. **Manual Override**: You can always manually attach specific rules
3. **Context Awareness**: Cursor's context system enhances keyword matching
4. **File Pattern Matching**: Rules can auto-attach based on file types

## Support

For issues or questions about the AI employee system:
1. Check the keyword matching in `auto_selector.mdc`
2. Test with `rule_selector.js`
3. Verify rule file formats
4. Review the employee's specific domain and keywords 