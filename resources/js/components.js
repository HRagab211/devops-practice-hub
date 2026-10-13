import { dueLabel, formatDateTime, h, statusBadge, timeAgo } from './dom';

const linkButtonClass = 'bg-brand inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-800';

export function taskList(tasks, createUrl) {
    if (tasks.length === 0) {
        return h(
            'div',
            { class: 'py-10 text-center' },
            h('p', { class: 'font-medium' }, 'A clear space for your next step.'),
            h('p', { class: 'mt-2 text-sm text-slate-500' }, 'Create a task to start tracking your work.'),
            h('a', { class: `mt-5 ${linkButtonClass}`, href: createUrl }, 'Create a task'),
        );
    }

    return h(
        'div',
        { class: 'divide-y divide-slate-100' },
        tasks.map((task) =>
            h(
                'article',
                { class: 'flex flex-wrap items-center justify-between gap-4 py-5' },
                h(
                    'div',
                    { class: 'min-w-0' },
                    h('a', { class: 'hover:text-brand font-medium break-words', href: task.url }, task.title),
                    h('p', { class: 'mt-1 text-xs text-slate-500' }, `${dueLabel(task.due_date)} · Updated ${timeAgo(task.updated_at)}`),
                ),
                statusBadge(task.status),
            ),
        ),
    );
}

export function reportList(reports) {
    if (reports.length === 0) {
        return h('p', { class: 'py-8 text-sm text-slate-500' }, 'No reports yet. Generate your first task report to get started.');
    }

    return h(
        'div',
        { class: 'divide-y divide-slate-100' },
        reports.map((report) =>
            h(
                'article',
                { class: 'flex flex-wrap items-center justify-between gap-4 py-5' },
                h(
                    'div',
                    {},
                    h('p', { class: 'font-medium' }, `Task report #${report.id}`),
                    h('p', { class: 'mt-1 text-xs text-slate-500' }, formatDateTime(report.created_at)),
                    report.status === 'failed' && h('p', { class: 'mt-2 text-sm text-red-700' }, 'This report failed. Please request a new report.'),
                ),
                h(
                    'div',
                    { class: 'flex items-center gap-4' },
                    statusBadge(report.status),
                    report.download_url &&
                        h(
                            'a',
                            { class: 'text-brand text-sm font-medium', href: report.download_url },
                            'Download CSV',
                            h('span', { class: 'sr-only' }, ` report ${report.id}`),
                        ),
                ),
            ),
        ),
    );
}
