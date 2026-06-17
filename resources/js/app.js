

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const confirmDialog = document.querySelector('[data-confirm-dialog]');
const confirmMessage = document.querySelector('[data-confirm-message]');
const confirmSubmit = document.querySelector('[data-confirm-submit]');
const confirmCancel = document.querySelector('[data-confirm-cancel]');
let pendingForm = null;

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!confirmDialog) {
            return;
        }

        event.preventDefault();
        pendingForm = form;
        confirmMessage.textContent = form.dataset.confirm || 'Vuoi continuare?';
        confirmDialog.showModal();
    });
});

confirmCancel?.addEventListener('click', () => {
    pendingForm = null;
    confirmDialog.close();
});

confirmSubmit?.addEventListener('click', () => {
    const form = pendingForm;
    pendingForm = null;
    confirmDialog.close();
    form?.submit();
});

document.querySelectorAll('input[type="file"][name="image"]').forEach((input) => {
    input.addEventListener('change', () => {
        const preview = document.querySelector('[data-image-preview]');
        const file = input.files?.[0];

        if (!preview || !file) {
            return;
        }

        preview.src = URL.createObjectURL(file);
    });
});
