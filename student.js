const REGISTRATION_STORAGE_KEY = "studentStudyHubRegistration";
const USER_NAME_STORAGE_KEY = "studentStudyHubUserName";

function canUseStorage() {
    try {
        const key = "__studentStudyHubTest__";
        window.localStorage.setItem(key, "1");
        window.localStorage.removeItem(key);
        return true;
    } catch (_error) {
        return false;
    }
}

function readStorage(key) {
    if (!canUseStorage()) {
        return null;
    }

    try {
        return window.localStorage.getItem(key);
    } catch (_error) {
        return null;
    }
}

function writeStorage(key, value) {
    if (!canUseStorage()) {
        return false;
    }

    try {
        window.localStorage.setItem(key, value);
        return true;
    } catch (_error) {
        return false;
    }
}

function removeStorage(key) {
    if (!canUseStorage()) {
        return false;
    }

    try {
        window.localStorage.removeItem(key);
        return true;
    } catch (_error) {
        return false;
    }
}

function escapeHtml(value) {
    return String(value)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#39;");
}

function getStoredUserName() {
    return readStorage(USER_NAME_STORAGE_KEY) || "";
}

function setStoredUserName(name) {
    return writeStorage(USER_NAME_STORAGE_KEY, name);
}

function getSavedRegistration() {
    const saved = readStorage(REGISTRATION_STORAGE_KEY);
    if (!saved) {
        return null;
    }

    try {
        return JSON.parse(saved);
    } catch (_error) {
        removeStorage(REGISTRATION_STORAGE_KEY);
        return null;
    }
}

function saveRegistration(payload) {
    return writeStorage(REGISTRATION_STORAGE_KEY, JSON.stringify(payload));
}

function setStatus(statusElement, message, state) {
    if (!statusElement) {
        return;
    }

    statusElement.textContent = message;
    statusElement.dataset.state = state || "";
    statusElement.hidden = message.length === 0;
}

function getFieldLabel(field) {
    if (field.id) {
        const label = document.querySelector(`label[for="${field.id}"]`);
        if (label) {
            return label.textContent.trim();
        }
    }

    if (field.name) {
        return field.name;
    }

    return "This field";
}

function getMissingRequiredFields(form) {
    const missing = [];
    const handledRadioGroups = new Set();

    form.querySelectorAll("[required]").forEach((field) => {
        if (field instanceof HTMLInputElement && field.type === "radio") {
            if (handledRadioGroups.has(field.name)) {
                return;
            }

            handledRadioGroups.add(field.name);

            const checked = form.querySelector(`input[name="${field.name}"]:checked`);
            if (!checked) {
                const wrapper = form.querySelector(`[data-required-group="${field.name}"]`);
                missing.push(wrapper?.dataset.groupLabel || field.name);
            }
            return;
        }

        if (field instanceof HTMLInputElement && field.type === "checkbox") {
            return;
        }

        if (typeof field.value === "string" && field.value.trim() === "") {
            missing.push(getFieldLabel(field));
        }
    });

    form.querySelectorAll("[data-required-group]").forEach((group) => {
        const groupName = group.dataset.requiredGroup;
        if (!groupName) {
            return;
        }

        const inputs = form.querySelectorAll(`input[name="${groupName}"]`);
        if (!inputs.length) {
            return;
        }

        const hasCheckedInput = Array.from(inputs).some((input) => input.checked);
        if (!hasCheckedInput) {
            const label = group.dataset.groupLabel || groupName;
            if (!missing.includes(label)) {
                missing.push(label);
            }
        }
    });

    return missing;
}

function renderWelcomeMessage(name) {
    const welcome = document.querySelector("[data-home-welcome]");
    if (!welcome) {
        return;
    }

    if (name) {
        welcome.innerHTML = `Welcome, <strong>${escapeHtml(name)}</strong>. Your personalised study hub is ready.`;
    } else {
        welcome.textContent = "Welcome to Student Study Hub. Enter your name to personalise this page.";
    }
}

function requestUserName() {
    const enteredName = window.prompt("Enter your name:");
    if (enteredName === null) {
        return null;
    }

    const trimmedName = enteredName.trim();
    if (!trimmedName) {
        window.alert("Please enter a name to personalise the welcome message.");
        return null;
    }

    setStoredUserName(trimmedName);
    return trimmedName;
}

function initHomeWelcome() {
    const welcome = document.querySelector("[data-home-welcome]");
    if (!welcome) {
        return;
    }

    const welcomeButton = document.querySelector("[data-update-welcome]");
    const savedName = getStoredUserName();

    if (savedName) {
        renderWelcomeMessage(savedName);
    } else {
        const promptedName = requestUserName();
        renderWelcomeMessage(promptedName || "");
    }

    if (welcomeButton) {
        welcomeButton.addEventListener("click", () => {
            const promptedName = requestUserName();
            renderWelcomeMessage(promptedName || getStoredUserName());
        });
    }
}

function initSectionToggles() {
    document.querySelectorAll("[data-toggle-target]").forEach((button) => {
        const targetId = button.dataset.toggleTarget;
        const target = targetId ? document.getElementById(targetId) : null;

        if (!target) {
            return;
        }

        const showText = button.dataset.showText || button.textContent.trim() || "Show more";
        const hideText = button.dataset.hideText || "Show less";

        button.textContent = target.hidden ? showText : hideText;

        button.addEventListener("click", () => {
            target.hidden = !target.hidden;
            button.textContent = target.hidden ? showText : hideText;
        });
    });
}

function initRegistrationForm() {
    const form = document.querySelector("[data-register-form]");
    if (!form) {
        return;
    }

    const status = document.querySelector("[data-form-status]");

    const saved = getSavedRegistration();
    if (saved) {
        if (form.elements.fullname) form.elements.fullname.value = saved.fullName || "";
        if (form.elements.email) form.elements.email.value = saved.email || "";
        if (form.elements.password) form.elements.password.value = saved.password || "";
        if (form.elements.dob) form.elements.dob.value = saved.dob || "";
        if (form.elements.course) form.elements.course.value = saved.course || "HTML Basics";

        if (saved.gender) {
            form.querySelectorAll('input[name="gender"]').forEach((input) => {
                input.checked = input.value === saved.gender;
            });
        }

        if (Array.isArray(saved.interests)) {
            form.querySelectorAll('input[name="interests"]').forEach((input) => {
                input.checked = saved.interests.includes(input.value);
            });
        }

        setStatus(status, "Loaded your last saved registration details in this browser.", "success");
    }

    form.addEventListener("input", () => {
        if (status && status.dataset.state === "error") {
            setStatus(status, "", "");
        }
    });

    form.addEventListener("submit", (event) => {
        event.preventDefault();

        const missingFields = getMissingRequiredFields(form);
        if (missingFields.length > 0) {
            setStatus(status, `Please complete the required fields before submitting: ${missingFields.join(", ")}.`, "error");
            return;
        }

        if (!form.checkValidity()) {
            form.reportValidity();
            setStatus(status, "Please correct the highlighted field before submitting again.", "error");
            return;
        }

        const payload = {
            fullName: form.elements.fullname.value.trim(),
            email: form.elements.email.value.trim(),
            password: form.elements.password.value,
            dob: form.elements.dob.value,
            gender: form.querySelector('input[name="gender"]:checked')?.value || "",
            course: form.elements.course.value,
            interests: Array.from(form.querySelectorAll('input[name="interests"]:checked')).map((input) => input.value),
        };

        const savedSuccessfully = saveRegistration(payload);
        const interestsText = payload.interests.length > 0 ? payload.interests.join(", ") : "no interests selected";

        setStoredUserName(payload.fullName);
        renderWelcomeMessage(payload.fullName);

        setStatus(
            status,
            savedSuccessfully
                ? `Registration saved for ${payload.fullName}. Course: ${payload.course}. Interests: ${interestsText}.`
                : `Validated ${payload.fullName} for ${payload.course}, but browser storage was blocked.`,
            "success"
        );
    });
}

document.addEventListener("DOMContentLoaded", () => {
    initHomeWelcome();
    initSectionToggles();
    initRegistrationForm();
});
