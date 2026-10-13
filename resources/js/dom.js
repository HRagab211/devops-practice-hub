/**
 * Build an element. Children that are strings become text nodes, so API data is never parsed as HTML.
 */
export function h(tag, attributes = {}, ...children) {
    const element = document.createElement(tag);
    Object.entries(attributes).forEach(([name, value]) => {
        if (value === false || value === null || value === undefined) {
            return;
        }
        if (name === 'class') {
            element.className = value;
        } else if (name.startsWith('on')) {
            element.addEventListener(name.slice(2).toLowerCase(), value);
        } else {
            element.setAttribute(name, value === true ? '' : value);
        }
    });
    element.append(...children.flat().filter((child) => child !== null && child !== undefined && child !== false));
    return element;
}

const badgeTone = {
    completed: 'bg-blue-50 text-blue-800',
    in_progress: 'bg-blue-50 text-blue-800',
    processing: 'bg-blue-50 text-blue-800',
    pending: 'bg-slate-100 text-slate-600',
    failed: 'bg-red-50 text-red-800',
};

export const statusLabel = (status) => status.charAt(0).toUpperCase() + status.slice(1).replaceAll('_', ' ');

export function statusBadge(status) {
    return h('span', { class: `inline-flex rounded-md px-2.5 py-1 text-xs font-medium ${badgeTone[status] ?? ''}` }, statusLabel(status));
}

/** Parse "Y-m-d" as a local calendar date (new Date("Y-m-d") would read it as UTC midnight). */
function parseDay(value) {
    const [year, month, day] = value.split('-').map(Number);
    return new Date(year, month - 1, day);
}

const dayFormat = new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
const timeFormat = new Intl.DateTimeFormat('en-GB', { hour: '2-digit', minute: '2-digit' });

export const formatDay = (value) => dayFormat.format(value.includes('T') ? new Date(value) : parseDay(value));

export const formatDateTime = (value) => `${formatDay(value)} · ${timeFormat.format(new Date(value))}`;

export const dueLabel = (dueDate) => (dueDate ? `Due ${formatDay(dueDate)}` : 'No due date');

const relative = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });
const units = [
    ['year', 31536000],
    ['month', 2592000],
    ['week', 604800],
    ['day', 86400],
    ['hour', 3600],
    ['minute', 60],
];

export function timeAgo(value) {
    const seconds = Math.round((new Date(value) - Date.now()) / 1000);
    for (const [unit, size] of units) {
        if (Math.abs(seconds) >= size) {
            return relative.format(Math.round(seconds / size), unit);
        }
    }
    return 'just now';
}

export function messageRow(text, tone = 'muted') {
    return h('p', { class: `py-8 text-sm ${tone === 'error' ? 'text-red-700' : 'text-slate-500'}`, role: tone === 'error' ? 'alert' : null }, text);
}

/**
 * Render "Previous / Page x of y / Next" for a Laravel paginated resource response.
 */
export function pagination({ links, meta }, onNavigate) {
    if (!meta || meta.last_page <= 1) {
        return h('div');
    }
    const button = (label, url) =>
        h(
            'button',
            {
                type: 'button',
                disabled: !url,
                class: 'rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50',
                onclick: () => url && onNavigate(url),
            },
            label,
        );

    return h(
        'nav',
        { class: 'flex items-center justify-between gap-3', 'aria-label': 'Pagination' },
        button('← Previous', links.prev),
        h('span', { class: 'text-sm text-slate-500' }, `Page ${meta.current_page} of ${meta.last_page}`),
        button('Next →', links.next),
    );
}

/**
 * Keep the address bar in sync with the JSON page being shown so reloads and shared links keep their state.
 */
export function syncAddressBar(url) {
    const target = new URL(url, window.location.origin);
    window.history.replaceState(null, '', `${window.location.pathname}${target.search}`);
}
