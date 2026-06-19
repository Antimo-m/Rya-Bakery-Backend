
import 'iconify-icon';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const adminMenuToggle = document.querySelector('[data-admin-menu-toggle]');
const adminMenuClose = document.querySelector('[data-admin-menu-close]');

const setAdminMenu = (open) => {
    document.body.classList.toggle('admin-menu-open', open);
    adminMenuToggle?.setAttribute('aria-expanded', String(open));
};

adminMenuToggle?.addEventListener('click', () => {
    setAdminMenu(!document.body.classList.contains('admin-menu-open'));
});

adminMenuClose?.addEventListener('click', () => setAdminMenu(false));
document.querySelectorAll('.admin-sidebar a').forEach((link) => {
    link.addEventListener('click', () => setAdminMenu(false));
});

document.querySelectorAll('.admin-table').forEach((table) => {
    const headers = Array.from(table.querySelectorAll('thead th')).map((cell) => cell.textContent.trim());

    table.querySelectorAll('tbody tr').forEach((row) => {
        Array.from(row.children).forEach((cell, index) => {
            if (headers[index]) {
                cell.dataset.label = headers[index];
            }
        });
    });
});

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

const monthFormatter = new Intl.DateTimeFormat('it-IT', { month: 'long', year: 'numeric' });
const weekDays = ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];

function parseIsoDate(value) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value || '')) return null;

    const [year, month, day] = value.split('-').map(Number);
    const date = new Date(year, month - 1, day);

    return Number.isNaN(date.getTime()) ? null : date;
}

function formatIsoDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function sameDay(left, right) {
    return left && right
        && left.getFullYear() === right.getFullYear()
        && left.getMonth() === right.getMonth()
        && left.getDate() === right.getDate();
}

document.querySelectorAll('.custom-date-field input').forEach((input) => {
    const wrapper = input.closest('.custom-date-field');
    const panel = document.createElement('div');
    const selected = parseIsoDate(input.value);
    let viewDate = selected || new Date();

    panel.className = 'custom-datepicker';
    wrapper.append(panel);

    const close = () => {
        wrapper.classList.remove('is-open');
    };

    const render = () => {
        const currentValue = parseIsoDate(input.value);
        const year = viewDate.getFullYear();
        const month = viewDate.getMonth();
        const firstDay = new Date(year, month, 1);
        const offset = (firstDay.getDay() + 6) % 7;
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const cells = [];

        for (let index = 0; index < offset; index += 1) {
            cells.push('<span class="custom-datepicker__empty"></span>');
        }

        for (let day = 1; day <= daysInMonth; day += 1) {
            const date = new Date(year, month, day);
            const selectedClass = sameDay(date, currentValue) ? ' is-selected' : '';
            const todayClass = sameDay(date, new Date()) ? ' is-today' : '';
            cells.push(`<button class="custom-datepicker__day${selectedClass}${todayClass}" type="button" data-date="${formatIsoDate(date)}">${day}</button>`);
        }

        panel.innerHTML = `
            <div class="custom-datepicker__header">
                <button type="button" data-month="-1" aria-label="Mese precedente"><iconify-icon icon="solar:alt-arrow-left-linear"></iconify-icon></button>
                <strong>${monthFormatter.format(viewDate)}</strong>
                <button type="button" data-month="1" aria-label="Mese successivo"><iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon></button>
            </div>
            <div class="custom-datepicker__weekdays">${weekDays.map((day) => `<span>${day}</span>`).join('')}</div>
            <div class="custom-datepicker__grid">${cells.join('')}</div>
        `;

        panel.querySelectorAll('[data-month]').forEach((control) => {
            control.addEventListener('click', () => {
                viewDate = new Date(year, month + Number(control.dataset.month), 1);
                render();
            });
        });

        panel.querySelectorAll('[data-date]').forEach((control) => {
            control.addEventListener('click', () => {
                input.value = control.dataset.date;
                input.dispatchEvent(new Event('change', { bubbles: true }));
                close();
            });
        });
    };

    const open = () => {
        wrapper.classList.add('is-open');
        viewDate = parseIsoDate(input.value) || viewDate;
        render();
    };

    wrapper.addEventListener('click', (event) => {
        if (panel.contains(event.target)) {
            return;
        }

        open();
        input.focus();
    });

    input.addEventListener('focus', open);

    document.addEventListener('click', (event) => {
        if (!wrapper.contains(event.target)) {
            close();
        }
    });

    render();
});
