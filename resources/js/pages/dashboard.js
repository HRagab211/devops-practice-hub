import { taskList } from '../components';
import { formatDateTime, h, messageRow, statusBadge } from '../dom';
import { getJson } from '../http';

async function initDashboard(root) {
    const slot = (name) => root.querySelector(`[data-slot="${name}"]`);
    try {
        const { data } = await getJson(root.dataset.endpoint);
        Object.entries(data.counts).forEach(([key, count]) => {
            const node = slot(`count-${key}`);
            if (node) {
                node.textContent = count;
            }
        });
        slot('recent-tasks').replaceChildren(taskList(data.recent_tasks, root.dataset.createUrl));
        slot('latest-report').replaceChildren(
            data.latest_report
                ? h(
                      'div',
                      {},
                      h('p', { class: 'my-4 text-sm text-slate-500' }, `Requested ${formatDateTime(data.latest_report.created_at)}`),
                      statusBadge(data.latest_report.status),
                  )
                : h('p', { class: 'mt-4 text-sm leading-relaxed text-slate-500' }, 'No reports yet. Create a CSV of your tasks whenever you need a copy.'),
        );
    } catch (error) {
        slot('recent-tasks').replaceChildren(messageRow(error.message, 'error'));
    }
}

document.querySelectorAll('[data-dashboard]').forEach(initDashboard);
