import { getJson, jsonHeaders, readJson } from "./http";

const alertClasses = {
    success:
        "mb-6 rounded-lg border p-4 text-sm border-blue-200 bg-blue-50 text-blue-900",
    error: "mb-6 rounded-lg border p-4 text-sm border-red-200 bg-red-50 text-red-800",
};

const pendingOperations = new WeakMap();
const alertsContainer = () => document.getElementById("ajax-alerts");

export function showAlert(message, type = "success") {
    const container = alertsContainer();
    if (!container) {
        return;
    }
    const alert = document.createElement("div");
    alert.setAttribute("role", type === "error" ? "alert" : "status");
    alert.className = alertClasses[type];
    alert.textContent = message;
    container.replaceChildren(alert);
    container.scrollIntoView({ behavior: "smooth", block: "nearest" });
}

function showValidationSummary(errors) {
    const container = alertsContainer();
    if (!container) {
        return;
    }
    const summary = document.createElement("div");
    summary.setAttribute("role", "alert");
    summary.className =
        "mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800";
    const heading = document.createElement("p");
    heading.className = "font-semibold";
    heading.textContent = "Please check the following:";
    const list = document.createElement("ul");
    list.className = "mt-2 list-inside list-disc";
    Object.values(errors)
        .flat()
        .forEach((message) => {
            const item = document.createElement("li");
            item.textContent = message;
            list.append(item);
        });
    summary.append(heading, list);
    container.replaceChildren(summary);
    container.scrollIntoView({ behavior: "smooth", block: "nearest" });
}

function clearErrors(form) {
    alertsContainer()?.replaceChildren();
    form.querySelectorAll("[data-ajax-error]").forEach((node) => node.remove());
    form.querySelectorAll("[aria-invalid]").forEach((field) => {
        field.removeAttribute("aria-invalid");
        field.removeAttribute("aria-describedby");
    });
}

function showFieldErrors(form, errors) {
    Object.entries(errors).forEach(([name, messages]) => {
        const field = form.querySelector(`[name="${CSS.escape(name)}"]`);
        if (!field) {
            return;
        }
        document.getElementById(`${name}-error`)?.remove();
        const error = document.createElement("p");
        error.id = `${name}-error`;
        error.className = "mt-2 text-sm text-red-700";
        error.dataset.ajaxError = "";
        error.textContent = messages[0];
        field.setAttribute("aria-invalid", "true");
        field.setAttribute("aria-describedby", error.id);
        field.after(error);
    });
    form.querySelector('[aria-invalid="true"]')?.focus();
}

async function submitForm(form) {
    const submitButtons = form.querySelectorAll(
        'button[type="submit"], button:not([type])',
    );
    submitButtons.forEach((button) => (button.disabled = true));
    form.setAttribute("aria-busy", "true");
    clearErrors(form);

    // if (form.getAttribute("aria-busy") === "true") {
    //     return;
    // }

    let operation = pendingOperations.get(form);
    if (!operation) {
        operation = {
            key: crypto.randomUUID(),
            url: form.action,
            body: new FormData(form),
        };

        pendingOperations.set(form, operation);
    }

    console.log(operation);

    try {
        const response = await fetch(operation.url, {
            method: "POST",
            body: operation.body,
            headers: {
                ...jsonHeaders,
                "Idempotency-Key": operation.key,
            },
            credentials: "same-origin",
        });
        const json = await readJson(response);

        if (response.status === 422 && json.errors) {
            showFieldErrors(form, json.errors);
            showValidationSummary(json.errors);
            return;
        }

        if (response.status === 419) {
            showAlert(
                "Your session has expired. Refresh the page and try again.",
                "error",
            );
            return;
        }

        if (!response.ok) {
            showAlert(
                json.message || "Something went wrong. Please try again.",
                "error",
            );
            return;
        }

        form.dispatchEvent(
            new CustomEvent("ajax:success", { bubbles: true, detail: json }),
        );

        const redirect = json.redirect ?? form.dataset.redirect;
        if (redirect) {
            window.location.assign(redirect);
            return;
        }

        if (form.hasAttribute("data-reset")) {
            form.reset();
        }
        if (json.message) {
            showAlert(json.message);
        }
    } catch {
        showAlert(
            "We could not reach the server. Check your connection and try again.",
            "error",
        );
    } finally {
        submitButtons.forEach((button) => (button.disabled = false));
        form.removeAttribute("aria-busy");
    }
}

document.addEventListener("submit", (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) {
        return;
    }
    if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
        event.preventDefault();
        return;
    }
    if (form.hasAttribute("data-ajax")) {
        event.preventDefault();
        submitForm(form);
    }
});

/**
 * Prefill a form from a JSON resource, e.g. <form data-fill-from="/tasks/1">.
 */
async function fillForm(form) {
    const fieldset = form.querySelector("fieldset");
    fieldset?.setAttribute("disabled", "");
    try {
        const { data } = await getJson(form.dataset.fillFrom);
        Object.entries(data).forEach(([name, value]) => {
            const field = form.elements.namedItem(name);
            if (field && "value" in field && field.type !== "file") {
                field.value = value ?? "";
            }
        });
        form.dispatchEvent(
            new CustomEvent("ajax:filled", { bubbles: true, detail: data }),
        );
    } catch (error) {
        showAlert(error.message, "error");
    } finally {
        fieldset?.removeAttribute("disabled");
    }
}

document.querySelectorAll("form[data-fill-from]").forEach(fillForm);
