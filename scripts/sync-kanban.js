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
  console.error(`[kanban-sync] Implementation plan not found at: ${planPath}`);
  process.exit(1);
}

const content = fs.readFileSync(planPath, 'utf8');
const lines = content.split('\n');

const tasks = [];
let inTaskTable = false;

for (const rawLine of lines) {
  const line = rawLine.trim();
  if (line.startsWith('## 2. Task Breakdown')) {
    inTaskTable = true;
    continue;
  }
  if (inTaskTable && line.startsWith('## ')) {
    break;
  }
  if (inTaskTable && line.startsWith('|') && !line.includes('---') && !line.includes('Depends On')) {
    const parts = line.split('|').map(s => s.trim()).filter(Boolean);
    if (parts.length >= 7) {
      const [id, task, phase, dependsOn, owner, linkedSpec, status] = parts;
      tasks.push({ id, task, phase, dependsOn, owner, linkedSpec, status });
    }
  }
}

const columns = {
  Backlog: [],
  Todo: [],
  'In Progress': [],
  Done: []
};

for (const t of tasks) {
  const normStatus = t.status.toLowerCase();
  const checked = normStatus === 'done' ? 'x' : ' ';
  const phaseTag = t.phase ? ` #${t.phase.toLowerCase().replace(/[^a-z0-9]/g, '')}` : '';
  const specLink = t.linkedSpec && t.linkedSpec !== '-' ? ` ${t.linkedSpec}` : '';
  const item = `- [${checked}] **${t.id}**: ${t.task} (${t.owner})${phaseTag}${specLink}`;

  if (normStatus === 'done') {
    columns.Done.push(item);
  } else if (normStatus.includes('progress') || normStatus === 'active') {
    columns['In Progress'].push(item);
  } else if (normStatus === 'todo' || normStatus === 'ready') {
    columns.Todo.push(item);
  } else {
    columns.Backlog.push(item);
  }
}

const now = new Date().toISOString().split('T')[0];

const kanbanContent = `---

kanban-plugin: board

---

## Backlog

${columns.Backlog.join('\n') || ''}

## Todo

${columns.Todo.join('\n') || ''}

## In Progress

${columns['In Progress'].join('\n') || ''}

## Done

**Complete**
${columns.Done.join('\n') || ''}

%% kanban:settings
\`\`\`
{"kanban-plugin":"board","tag-colors":[{"tagKey":"#phase1","color":"#ffffff","backgroundColor":"#10b981"},{"tagKey":"#phase2","color":"#ffffff","backgroundColor":"#2563eb"},{"tagKey":"#phase3","color":"#ffffff","backgroundColor":"#7c3aed"}]}
\`\`\`
%%
`;

fs.writeFileSync(kanbanPath, kanbanContent, 'utf8');
console.log(`[kanban-sync] Successfully synchronized ${tasks.length} tasks to:`);
console.log(`  -> ${path.relative(rootDir, kanbanPath)}`);
console.log(`  -> Done: ${columns.Done.length} | In Progress: ${columns['In Progress'].length} | Todo: ${columns.Todo.length} | Backlog: ${columns.Backlog.length}`);
