import { reportList } from '../components';
import { messageRow, pagination, syncAddressBar } from '../dom';
import { getJson } from '../http';

const pollEveryMs = 5000;

function initReports(root) {
    const list = root.querySelector('[data-report-list]');
    const pager = root.querySelector('[data-pagination]');
    let currentUrl = `${root.dataset.endpoint}${window.location.search}`;
    let pollTimer;

    async function load(url = currentUrl) {
        clearTimeout(pollTimer);
        currentUrl = url;
        list.setAttribute('aria-busy', 'true');
        try {
            const page = await getJson(url);
            list.replaceChildren(reportList(page.data));
            pager.replaceChildren(pagination(page, load));
            syncAddressBar(url);
            if (page.data.some((report) => ['pending', 'processing'].includes(report.status))) {
                pollTimer = setTimeout(() => load(), pollEveryMs);
            }
        } catch (error) {
            list.replaceChildren(messageRow(error.message, 'error'));
        } finally {
            list.removeAttribute('aria-busy');
        }
    }

    root.querySelector('[data-refresh]')?.addEventListener('click', () => load());
    document.addEventListener('ajax:success', (event) => {
        if (event.target.matches('[data-reloads-reports]')) {
            load(root.dataset.endpoint);
        }
    });

    load();
}

document.querySelectorAll('[data-reports]').forEach(initReports);
