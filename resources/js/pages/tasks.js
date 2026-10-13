import { taskList } from '../components';
import { dueLabel, formatDay, messageRow, pagination, statusBadge, syncAddressBar, timeAgo } from '../dom';
import { getJson } from '../http';

function initTaskIndex(root) {
    const list = root.querySelector('[data-task-list]');
    const pager = root.querySelector('[data-pagination]');
    const filter = root.querySelector('[data-task-filter]');

    async function load(url) {
        list.setAttribute('aria-busy', 'true');
        try {
            const page = await getJson(url);
            list.replaceChildren(taskList(page.data, root.dataset.createUrl));
            pager.replaceChildren(pagination(page, load));
            syncAddressBar(url);
        } catch (error) {
            list.replaceChildren(messageRow(error.message, 'error'));
            pager.replaceChildren();
        } finally {
            list.removeAttribute('aria-busy');
        }
    }

    filter.addEventListener('submit', (event) => {
        event.preventDefault();
        const params = new URLSearchParams(new FormData(filter));
        if (!params.get('status')) {
            params.delete('status');
        }
        const query = params.toString();
        load(`${root.dataset.endpoint}${query ? `?${query}` : ''}`);
    });
    filter.elements.namedItem('status')?.addEventListener('change', () => filter.requestSubmit());

    load(`${root.dataset.endpoint}${window.location.search}`);
}

async function initTaskShow(root) {
    const field = (name) => root.querySelector(`[data-field="${name}"]`);
    try {
        const { data: task } = await getJson(root.dataset.endpoint);
        document.title = `${task.title} · DevOps Practice Hub`;
        field('title').textContent = task.title;
        field('status').replaceChildren(statusBadge(task.status));
        field('due').textContent = dueLabel(task.due_date);
        field('description').textContent = task.description || 'No description added.';
        field('meta').textContent = `Created ${formatDay(task.created_at)} · Updated ${timeAgo(task.updated_at)}`;
        root.removeAttribute('aria-busy');
    } catch (error) {
        field('description').replaceChildren(messageRow(error.message, 'error'));
    }
}

document.querySelectorAll('[data-task-index]').forEach(initTaskIndex);
document.querySelectorAll('[data-task-show]').forEach(initTaskShow);
