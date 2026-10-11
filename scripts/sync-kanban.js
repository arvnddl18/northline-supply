import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const rootDir = path.resolve(__dirname, '..');
const vaultDir = path.join(rootDir, 'Vault', 'Northline Supply Vault');
const planPath = path.join(vaultDir, '11 - Specs', 'IMPLEMENTATION_PLAN.md');
const kanbanPath = path.join(vaultDir, '09 - Current State', 'DEVELOPMENT_KANBAN.md');
const currentStatePath = path.join(vaultDir, '09 - Current State', 'CURRENT_STATE.md');

function sync() {
  if (!fs.existsSync(planPath)) {
    console.error(`[kanban-sync] Plan not found at: ${planPath}`);
    return false;
  }

  const content = fs.readFileSync(planPath, 'utf8');

  // 1. Parse CURRENT_STATE.md if available
  const inProgressIds = new Set();
  const completedIds = new Set();
  const blockedIds = new Set();

  if (fs.existsSync(currentStatePath)) {
    const stateContent = fs.readFileSync(currentStatePath, 'utf8');
    let section = '';
    for (const rawLine of stateContent.split('\n')) {
      const line = rawLine.trim();
      if (line.startsWith('## Completed')) section = 'completed';
      else if (line.startsWith('## In Progress')) section = 'inprogress';
      else if (line.startsWith('## Blocked')) section = 'blocked';
      else if (line.startsWith('## ')) section = '';

      const taskMatch = line.match(/^[-*]\s+(?:\*\*)?(`?)(S\d+-T\d+|INIT-\d+)\1(?:\*\*)?(?:\s+Completed)?:?/i);
      if (taskMatch) {
        const id = taskMatch[2];
        if (section === 'completed') completedIds.add(id);
        else if (section === 'inprogress') inProgressIds.add(id);
        else if (section === 'blocked') blockedIds.add(id);
      }
    }
  }

  // 2. Parse Story Priority Map
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

  // 3. Priority mapping helper
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

  // 4. Work Type helper (Decisions vs Development)
  function getTaskType(taskId) {
    if (['S0-T1', 'S0-T7', 'INIT-01', 'INIT-02', 'INIT-03'].includes(taskId)) {
      return 'decision';
    }
    return 'dev';
  }

  // 5. Parse Sprint Tasks
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

  // Dedicated Legend Cards
  const legendItems = [
    `- **Priority**<br>🔴 **#p0-must** Must Have<br>🟠 **#p1-should** Should Have<br>🔵 **#p2-could** Could Have`,
    `- **Work Type**<br>⚖️ **#decision** Decision<br>🛠️ **#dev** Development`
  ];

  // Foundation Done items with rich context & links
  const documentedDone = {
    'S0-T2': `- [x] **S0-T2**: Shopify and Stripe test accounts (Dev store, Partner app, Stripe test keys AUD, Stripe CLI) #p0-must #dev #sprint0 #0.5d [[STRIPE_AND_SHOPIFY_SETUP]]`,
    'S0-T1': `- [x] **S0-T1**: Decide checkout option (A or B) (Option B locked, Option A fallback) #p0-must #decision #sprint0 #0.5d [[ADR_002_CHECKOUT_STRATEGY]]`,
    'INIT-01': `- [x] **INIT-01**: Ingest 12-page requirements & spec baseline (Agent) #p0-must #decision #sprint0 [[PRD]]`,
    'INIT-02': `- [x] **INIT-02**: Establish 10-page Sprint Plan v1.0 (50 dev days, 24 stories) (Agent) #p0-must #decision #sprint0 [[IMPLEMENTATION_PLAN]]`,
    'INIT-03': `- [x] **INIT-03**: Create Checkout Strategy Decision Record (Option B primary) (Agent) #p0-must #decision #sprint0 [[ADR_002_CHECKOUT_STRATEGY]]`
  };

  const doneList = [];
  const handledDoneIds = new Set();

  for (const [id, item] of Object.entries(documentedDone)) {
    if (completedIds.has(id) || id.startsWith('INIT-') || ['S0-T1', 'S0-T2'].includes(id)) {
      doneList.push(item);
      handledDoneIds.add(id);
    }
  }

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
    Done: doneList
  };

  for (const t of tasks) {
    const dayTag = t.days ? ` #${t.days}d` : '';
    const item = {
      priority: t.priority,
      id: t.id,
      type: t.type,
      markdown: `- [ ] **${t.id}**: ${t.title} #${t.priority} #${t.type} #${t.sprint}${dayTag}`
    };

    if (handledDoneIds.has(t.id)) {
      continue;
    }

    if (completedIds.has(t.id)) {
      columns.Done.push(`- [x] **${t.id}**: ${t.title} #${t.priority} #${t.type} #${t.sprint}${dayTag}`);
    } else if (inProgressIds.has(t.id)) {
      columns['In Progress'].push(item);
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
    { tagKey: '#p0-must', color: '#ffffff', backgroundColor: '#ef4444' },
    { tagKey: '#p1-should', color: '#ffffff', backgroundColor: '#f97316' },
    { tagKey: '#p2-could', color: '#ffffff', backgroundColor: '#3b82f6' },
    { tagKey: '#decision', color: '#6b21a8', backgroundColor: '#f3e8ff' },
    { tagKey: '#dev', color: '#0f766e', backgroundColor: '#f0fdfa' },
    { tagKey: '#sprint0', color: '#047857', backgroundColor: '#ecfdf5' },
    { tagKey: '#sprint1', color: '#0e7490', backgroundColor: '#ecfeff' },
    { tagKey: '#sprint2', color: '#4338ca', backgroundColor: '#eef2ff' },
    { tagKey: '#sprint3', color: '#6d28d9', backgroundColor: '#f5f3ff' },
    { tagKey: '#sprint4', color: '#7e22ce', backgroundColor: '#faf5ff' },
    { tagKey: '#sprint5', color: '#475569', backgroundColor: '#f8fafc' },
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

  const now = new Date().toLocaleTimeString();
  console.log(`[kanban-sync ${now}] Kanban board synchronized:`);
  console.log(`  -> In Progress: ${columns['In Progress'].length} (${columns['In Progress'].map(c => c.id).join(', ') || 'none'})`);
  console.log(`  -> Todo: ${columns.Todo.length}`);
  console.log(`  -> Backlog: ${columns.Backlog.length}`);
  console.log(`  -> Done: ${columns.Done.length}`);
  return true;
}

// Initial sync
sync();

// Watch mode if --watch flag is supplied
if (process.argv.includes('--watch')) {
  console.log(`[kanban-sync] Live watcher running at livespeed. Watching CURRENT_STATE.md and IMPLEMENTATION_PLAN.md...`);

  let debounceTimer = null;
  const triggerSync = (changedFile) => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      console.log(`[kanban-sync] Detected update in ${changedFile}. Updating Kanban live...`);
      sync();
    }, 100);
  };

  const specsDir = path.dirname(planPath);
  const stateDir = path.dirname(currentStatePath);

  const watchDir = (dir, allowedNames) => {
    if (fs.existsSync(dir)) {
      try {
        fs.watch(dir, (eventType, filename) => {
          if (filename && allowedNames.includes(filename)) {
            triggerSync(filename);
          }
        });
      } catch (err) {
        console.warn(`[kanban-sync] Directory watch warning for ${dir}:`, err.message);
      }
    }
  };

  watchDir(specsDir, ['IMPLEMENTATION_PLAN.md']);
  watchDir(stateDir, ['CURRENT_STATE.md']);
}
