
import 'iconify-icon';
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
    const dropzone = input.closest('[data-upload-zone]');
    const fileName = dropzone?.querySelector('[data-upload-file-name]');

    const setFile = (file) => {
        const preview = document.querySelector('[data-image-preview]');

        if (!preview || !file) {
            return;
        }

        preview.src = URL.createObjectURL(file);

        if (fileName) {
            fileName.textContent = file.name;
        }
    };

    input.addEventListener('change', () => {
        setFile(input.files?.[0]);
    });

    dropzone?.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropzone.classList.add('is-dragging');
    });

    dropzone?.addEventListener('dragleave', () => {
        dropzone.classList.remove('is-dragging');
    });

    dropzone?.addEventListener('drop', (event) => {
        event.preventDefault();
        dropzone.classList.remove('is-dragging');

        const file = event.dataTransfer?.files?.[0];
        if (!file) {
            return;
        }

        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
        setFile(file);
    });
});

document.querySelectorAll('select[data-custom-select]').forEach((select) => {
    const wrapper = document.createElement('div');
    const button = document.createElement('button');
    const panel = document.createElement('div');
    const search = document.createElement('input');
    const list = document.createElement('div');

    wrapper.className = 'custom-select';
    button.className = 'custom-select__button';
    button.type = 'button';
    button.setAttribute('aria-haspopup', 'listbox');
    button.setAttribute('aria-expanded', 'false');
    panel.className = 'custom-select__panel';
    search.className = 'custom-select__search';
    search.type = 'search';
    search.placeholder = 'Cerca...';
    list.className = 'custom-select__list';
    list.role = 'listbox';

    const syncButton = () => {
        button.textContent = select.selectedOptions[0]?.textContent || 'Seleziona';
    };

    const renderOptions = () => {
        const term = search.value.trim().toLowerCase();
        list.replaceChildren();

        Array.from(select.options)
            .filter((option) => option.textContent.toLowerCase().includes(term))
            .forEach((option) => {
                const item = document.createElement('button');
                item.className = 'custom-select__option';
                item.type = 'button';
                item.role = 'option';
                item.textContent = option.textContent;
                item.setAttribute('aria-selected', String(option.selected));
                item.addEventListener('click', () => {
                    select.value = option.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    syncButton();
                    wrapper.classList.remove('is-open');
                    button.setAttribute('aria-expanded', 'false');
                });
                list.append(item);
            });
    };

    button.addEventListener('click', () => {
        const open = !wrapper.classList.contains('is-open');
        wrapper.classList.toggle('is-open', open);
        button.setAttribute('aria-expanded', String(open));

        if (open) {
            search.focus();
            renderOptions();
        }
    });

    search.addEventListener('input', renderOptions);
    document.addEventListener('click', (event) => {
        if (!wrapper.contains(event.target)) {
            wrapper.classList.remove('is-open');
            button.setAttribute('aria-expanded', 'false');
        }
    });

    syncButton();
    renderOptions();
    panel.append(search, list);
    wrapper.append(button, panel);
    select.classList.add('native-select-hidden');
    select.after(wrapper);
});
