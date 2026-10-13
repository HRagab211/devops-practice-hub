export const jsonHeaders = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

export async function readJson(response) {
    if (response.status === 204) {
        return {};
    }
    try {
        return await response.json();
    } catch {
        return {};
    }
}

/**
 * GET a JSON endpoint with the session cookie. Throws with the server message on failure.
 */
export async function getJson(url) {
    const response = await fetch(url, { headers: jsonHeaders, credentials: 'same-origin' });
    const json = await readJson(response);
    if (response.status === 401) {
        window.location.assign('/login');
    }
    if (!response.ok) {
        throw new Error(json.message || 'We could not load this data. Please try again.');
    }
    return json;
}
