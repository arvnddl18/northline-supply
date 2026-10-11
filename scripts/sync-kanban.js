import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const rootDir = path.resolve(__dirname, '..');
const vaultDir = path.join(rootDir, 'Vault', 'Northline Supply Vault');
const planPath = path.join(vaultDir, '11 - Specs', 'IMPLEMENTATION_PLAN.md');
const kanbanPath = path.join(vaultDir, '09 - Current State', 'DEVELOPMENT_KANBAN.md');
const currentStatePath = path.join(vaultDir, '09 - Current State', 'CURRENT_STATE.md');

if (!fs.existsSync(planPath)) {
  console.error(`[kanban-sync] Plan not found at: ${planPath}`);
  process.exit(1);
}

const content = fs.readFileSync(planPath, 'utf8');

// 1. Parse Story Priority Map
const storyPriority = {};
const storyLines = content.split('\n');
let inStoryMap = false;

for (const rawLine of storyLines) {
  const line = rawLine.trim();
  if (line.includes('## 2. Story to Sprint Map')) {
    inStoryMap = true;
    continue;
  }
  if (inStoryMap && line.startsWith('## ')) {
    break;
  }
  if (inStoryMap && line.startsWith('|') && line.includes('US-')) {
    const parts = line.split('|').map(s => s.trim().replace(/\*\*/g, '')).filter(Boolean);
    if (parts.length >= 5) {
      const [storyId, title, priority, sprint, spec] = parts;
      storyPriority[storyId] = { priority: priority.toLowerCase(), sprint, spec, title };
    }
  }
}

// 2. Priority mapping helper
function getTaskPriority(taskId, sprintNum) {
  if (sprintNum === 0) {
    if (['S0-T1', 'S0-T2', 'S0-T3'].includes(taskId)) return 'p0-must';
    if (['S0-T4', 'S0-T5', 'S0-T7'].includes(taskId)) return 'p1-should';
    return 'p2-could';
  }
  if (sprintNum === 1) return 'p0-must';
  if (sprintNum === 2) {
    if (['S2-T1', 'S2-T2', 'S2-T3', 'S2-T4', 'S2-T5'].includes(taskId)) return 'p0-must';
    return 'p1-should';
  }
  if (sprintNum === 3) {
    if (['S3-T1', 'S3-T2', 'S3-T4'].includes(taskId)) return 'p0-must';
    return 'p1-should';
  }
  if (sprintNum === 4) return 'p1-should';
  if (sprintNum === 5) {
    if (['S5-T6', 'S5-T7'].includes(taskId)) return 'p0-must';
    return 'p1-should';
  }
  return 'p2-could';
}

// 3. Work Type helper (Decisions vs Development)
function getTaskType(taskId) {
  if (['S0-T1', 'S0-T7', 'INIT-01', 'INIT-02', 'INIT-03'].includes(taskId)) {
    return 'decision';
  }
  return 'dev';
}

// 4. Parse Sprint Tasks
const tasks = [];
const sprintSections = content.split(/### Sprint\s+(\d+):/i);

for (let i = 1; i < sprintSections.length; i += 2) {
  const sprintNum = parseInt(sprintSections[i], 10);
  const sprintBody = sprintSections[i + 1] || '';
  const lines = sprintBody.split('\n');
  let inTaskTable = false;

  for (const rawLine of lines) {
    const line = rawLine.trim();
    if (line.startsWith('| #') || line.startsWith('|---|')) {
      inTaskTable = true;
      continue;
    }
    if (inTaskTable && line.startsWith('##')) break;
    if (inTaskTable && line.startsWith('|') && line.includes('S' + sprintNum + '-T')) {
      const parts = line.split('|').map(s => s.trim().replace(/\*\*/g, '')).filter(Boolean);
      if (parts.length >= 4) {
        const [taskId, title, detail, days] = parts;
        const priority = getTaskPriority(taskId, sprintNum);
        const type = getTaskType(taskId);

        tasks.push({
          id: taskId,
          title,
          detail,
          days,
          sprint: `sprint${sprintNum}`,
          priority,
          type
        });
      }
    }
  }
}

// Dedicated Legend Cards (Simple and concise, matching Image 2)
const legendItems = [
  `- **Priority**<br>🔴 **#p0-must** Must Have<br>🟠 **#p1-should** Should Have<br>🔵 **#p2-could** Could Have`,
  `- **Work Type**<br>⚖️ **#decision** Decision<br>🛠️ **#dev** Development`
];

// Foundation Done items
const doneItems = [
  `- [x] **S0-T2**: Shopify and Stripe test accounts (Dev store, Partner app, Stripe test keys AUD, Stripe CLI) #p0-must #dev #sprint0 #0.5d [[STRIPE_AND_SHOPIFY_SETUP]]`,
  `- [x] **S0-T1**: Decide checkout option (A or B) (Option B locked, Option A fallback) #p0-must #decision #sprint0 #0.5d [[ADR_002_CHECKOUT_STRATEGY]]`,
  `- [x] **INIT-01**: Ingest 12-page requirements & spec baseline (Agent) #p0-must #decision #sprint0 [[PRD]]`,
  `- [x] **INIT-02**: Establish 10-page Sprint Plan v1.0 (50 dev days, 24 stories) (Agent) #p0-must #decision #sprint0 [[IMPLEMENTATION_PLAN]]`,
  `- [x] **INIT-03**: Create Checkout Strategy Decision Record (Option B primary) (Agent) #p0-must #decision #sprint0 [[ADR_002_CHECKOUT_STRATEGY]]`
];

// Sort helper: P0-Must > P1-Should > P2-Could
const priorityWeight = {
  'p0-must': 1,
  'p1-should': 2,
  'p2-could': 3
};

function sortCards(a, b) {
  const wA = priorityWeight[a.priority] || 99;
  const wB = priorityWeight[b.priority] || 99;
  if (wA !== wB) return wA - wB;
  return a.id.localeCompare(b.id, undefined, { numeric: true });
}

const columns = {
  Backlog: [],
  Todo: [],
  'In Progress': [],
  Done: doneItems
};

for (const t of tasks) {
  const dayTag = t.days ? ` #${t.days}d` : '';
  const item = {
    priority: t.priority,
    id: t.id,
    type: t.type,
    markdown: `- [ ] **${t.id}**: ${t.title} #${t.priority} #${t.type} #${t.sprint}${dayTag}`
  };

  if (['S0-T1', 'S0-T2'].includes(t.id)) {
    // S0-T1 and S0-T2 are done and recorded in doneItems
    continue;
  } else if (t.sprint === 'sprint0' || t.sprint === 'sprint1') {
    columns.Todo.push(item);
  } else {
    columns.Backlog.push(item);
  }
}

// Sort cards in each column by priority hierarchy
columns['In Progress'].sort(sortCards);
columns.Todo.sort(sortCards);
columns.Backlog.sort(sortCards);

const tagColors = [
  // Priority Tags (Clear visual hierarchy)
  { tagKey: '#p0-must', color: '#ffffff', backgroundColor: '#ef4444' },      // Crimson Red
  { tagKey: '#p1-should', color: '#ffffff', backgroundColor: '#f97316' },    // Flame Orange
  { tagKey: '#p2-could', color: '#ffffff', backgroundColor: '#3b82f6' },     // Royal Blue
  
  // Work Type Tags (Decision vs Development)
  { tagKey: '#decision', color: '#6b21a8', backgroundColor: '#f3e8ff' },     // Purple Chip
  { tagKey: '#dev', color: '#0f766e', backgroundColor: '#f0fdfa' },          // Teal Chip

  // Sprint Tags (Soft non-distracting pastel badges)
  { tagKey: '#sprint0', color: '#047857', backgroundColor: '#ecfdf5' },      // Soft Emerald
  { tagKey: '#sprint1', color: '#0e7490', backgroundColor: '#ecfeff' },      // Soft Cyan
  { tagKey: '#sprint2', color: '#4338ca', backgroundColor: '#eef2ff' },      // Soft Indigo
  { tagKey: '#sprint3', color: '#6d28d9', backgroundColor: '#f5f3ff' },      // Soft Violet
  { tagKey: '#sprint4', color: '#7e22ce', backgroundColor: '#faf5ff' },      // Soft Purple
  { tagKey: '#sprint5', color: '#475569', backgroundColor: '#f8fafc' },      // Soft Slate

  // Estimate Tags
  { tagKey: '#0.5d', color: '#64748b', backgroundColor: '#f1f5f9' },
  { tagKey: '#1.0d', color: '#64748b', backgroundColor: '#f1f5f9' },
  { tagKey: '#1.5d', color: '#64748b', backgroundColor: '#f1f5f9' },
  { tagKey: '#2.0d', color: '#64748b', backgroundColor: '#f1f5f9' },
  { tagKey: '#2.5d', color: '#64748b', backgroundColor: '#f1f5f9' }
];

const settingsBlock = {
  'kanban-plugin': 'board',
  'tag-colors': tagColors
};

const kanbanContent = `---

kanban-plugin: board

---

## 📌 Legend & Key

${legendItems.join('\n')}

## Backlog

${columns.Backlog.map(c => c.markdown).join('\n')}

## Todo

${columns.Todo.map(c => c.markdown).join('\n')}

## In Progress

${columns['In Progress'].map(c => c.markdown).join('\n')}

## Done

**Complete**
${columns.Done.join('\n')}

%% kanban:settings
\`\`\`
${JSON.stringify(settingsBlock)}
\`\`\`
%%
`;

fs.writeFileSync(kanbanPath, kanbanContent, 'utf8');

console.log(`[kanban-sync] Successfully generated priority-tiered Kanban board with Legend:`);
console.log(`  -> Legend: ${legendItems.length} guide cards`);
console.log(`  -> Tasks: ${tasks.length + doneItems.length} total`);
console.log(`  -> In Progress: ${columns['In Progress'].length} (Immediate P0 Focus)`);
console.log(`  -> Todo: ${columns.Todo.length} (Sorted: P0-Must -> P1-Should -> P2-Could)`);
console.log(`  -> Backlog: ${columns.Backlog.length} (Future Sprints 2-5)`);
console.log(`  -> Done: ${columns.Done.length} (Foundation & Architecture baseline)`);
