
import 'iconify-icon';
import Alpine from 'alpinejs';
import echo from './lib/echo';

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

function syncTableLabels(table) {
    const headers = Array.from(table.querySelectorAll('thead th')).map((cell) => cell.textContent.trim());

    table.querySelectorAll('tbody tr').forEach((row) => {
        Array.from(row.children).forEach((cell, index) => {
            if (headers[index]) {
                cell.dataset.label = headers[index];
            }
        });
    });
}

document.querySelectorAll('.admin-table').forEach((table) => {
    syncTableLabels(table);
});

const confirmDialog = document.querySelector('[data-confirm-dialog]');
const confirmMessage = document.querySelector('[data-confirm-message]');
const confirmSubmit = document.querySelector('[data-confirm-submit]');
const confirmCancel = document.querySelector('[data-confirm-cancel]');
let pendingForm = null;

document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-confirm]');

    if (!form || !confirmDialog) {
        return;
    }

    event.preventDefault();
    pendingForm = form;
    confirmMessage.textContent = form.dataset.confirm || 'Vuoi continuare?';
    confirmDialog.showModal();
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

const realtimeOrders = document.querySelector('[data-realtime-orders]');

if (realtimeOrders) {
    const table = realtimeOrders.querySelector('.admin-table');
    const tbody = realtimeOrders.querySelector('[data-orders-table-body]');
    const emptyRow = realtimeOrders.querySelector('[data-orders-empty-row]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const soundToggle = document.querySelector('[data-order-sound-toggle]');
    const soundLabel = soundToggle?.querySelector('[data-sound-label]');
    const soundIcon = soundToggle?.querySelector('[data-sound-icon]');
    let soundEnabled = localStorage.getItem('rya-admin-order-sound') === 'on';
    let audioContext = null;
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    const euroFormatter = new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' });
    const dateFormatter = new Intl.DateTimeFormat('it-IT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

    const fillTemplate = (template, slug) => template.replace('__SLUG__', encodeURIComponent(slug));
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[character]));

    const orderActions = (order) => {
        const acceptUrl = fillTemplate(realtimeOrders.dataset.acceptUrlTemplate, order.slug);
        const cancelUrl = fillTemplate(realtimeOrders.dataset.cancelUrlTemplate, order.slug);
        const editUrl = fillTemplate(realtimeOrders.dataset.editUrlTemplate, order.slug);

        return `
            <div class="admin-actions">
                <form method="POST" action="${acceptUrl}" data-confirm="Prendere questo ordine in preparazione?">
                    <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                    <input type="hidden" name="_method" value="PATCH">
                    <button class="admin-btn success admin-btn--icon" type="submit" aria-label="Accetta ordine" title="Accetta">
                        <iconify-icon icon="solar:check-square-bold-duotone"></iconify-icon>
                    </button>
                </form>
                <form method="POST" action="${cancelUrl}" data-confirm="Annullare o rifiutare questo ordine? Sara spostato nello storico.">
                    <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                    <input type="hidden" name="_method" value="PATCH">
                    <button class="admin-btn danger admin-btn--icon" type="submit" aria-label="Annulla ordine" title="Annulla">
                        <iconify-icon icon="solar:close-square-bold-duotone"></iconify-icon>
                    </button>
                </form>
                <a class="admin-btn edit admin-btn--icon" href="${editUrl}" aria-label="Modifica ordine" title="Modifica">
                    <iconify-icon icon="solar:pen-2-bold-duotone"></iconify-icon>
                </a>
            </div>
        `;
    };

    const syncSoundToggle = () => {
        if (!soundToggle || !soundLabel || !soundIcon) {
            return;
        }

        soundToggle.setAttribute('aria-pressed', String(soundEnabled));
        soundToggle.classList.toggle('is-on', soundEnabled);
        soundLabel.textContent = soundEnabled ? 'Audio attivo' : 'Audio spento';
        soundIcon.setAttribute('icon', soundEnabled ? 'solar:bell-bing-bold-duotone' : 'solar:bell-off-linear');
    };

    const playOrderChime = () => {
        if (!soundEnabled) {
            return;
        }

        if (!AudioContextClass) {
            return;
        }

        audioContext ??= new AudioContextClass();

        const now = audioContext.currentTime;
        const notes = [659.25, 783.99];

        notes.forEach((frequency, index) => {
            const oscillator = audioContext.createOscillator();
            const gain = audioContext.createGain();
            const start = now + (index * 0.11);

            oscillator.type = 'sine';
            oscillator.frequency.value = frequency;
            gain.gain.setValueAtTime(0.0001, start);
            gain.gain.exponentialRampToValueAtTime(0.08, start + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, start + 0.18);
            oscillator.connect(gain).connect(audioContext.destination);
            oscillator.start(start);
            oscillator.stop(start + 0.2);
        });
    };

    soundToggle?.addEventListener('click', async () => {
        soundEnabled = !soundEnabled;
        localStorage.setItem('rya-admin-order-sound', soundEnabled ? 'on' : 'off');
        syncSoundToggle();

        if (soundEnabled) {
            if (!AudioContextClass) {
                return;
            }

            audioContext ??= new AudioContextClass();
            await audioContext.resume();
            playOrderChime();
        }
    });

    syncSoundToggle();

    const productChip = (item) => `
        <span class="admin-product-chip">
            <img src="${escapeHtml(item.product?.image_url || '')}" alt="">
            <span>${escapeHtml(item.quantity)}x ${escapeHtml(item.product?.name || 'Prodotto')}</span>
        </span>
    `;

    const orderProducts = (order) => {
        const items = order.items || [];

        if (items.length <= 4) {
            return `
                <div class="admin-product-stack" data-order-products>
                    ${items.map(productChip).join('')}
                </div>
            `;
        }

        return `
            <div class="admin-product-stack" data-order-products>
                ${productChip(items[0])}
                <div class="admin-product-stack__extra" data-order-products-extra hidden>
                    ${items.slice(1).map(productChip).join('')}
                </div>
                <button class="admin-products-toggle" type="button" data-order-products-toggle aria-expanded="false">
                    <iconify-icon icon="solar:menu-dots-bold"></iconify-icon>
                    <span data-toggle-label>+${items.length - 1} altri prodotti</span>
                </button>
            </div>
        `;
    };

    const expandedOrderProducts = (container) => (
        Array.from(container.querySelectorAll('.admin-product-chip')).length
    );

    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-order-products-toggle]');

        if (!toggle) {
            return;
        }

        const stack = toggle.closest('[data-order-products]');
        const extra = stack?.querySelector('[data-order-products-extra]');
        const label = toggle.querySelector('[data-toggle-label]');

        if (!extra || !label) {
            return;
        }

        const willOpen = extra.hidden;
        extra.hidden = !willOpen;
        toggle.setAttribute('aria-expanded', String(willOpen));
        label.textContent = willOpen ? 'Mostra meno' : `+${expandedOrderProducts(extra)} altri prodotti`;
    });

    const orderRow = (order) => {
        const row = document.createElement('tr');
        row.dataset.orderId = order.id;
        row.className = 'is-live-new';
        row.innerHTML = `
            <td>
                <strong>${escapeHtml(order.customer_name)}</strong><br>
                <small>Tavolo ${escapeHtml(order.table_number)} · ${escapeHtml(order.slug)}</small>
            </td>
            <td>${orderProducts(order)}</td>
            <td>${euroFormatter.format(Number(order.total_price || 0))}</td>
            <td><span class="badge ${escapeHtml(order.status)}">${escapeHtml(order.status_label || order.status)}</span></td>
            <td>${order.created_at ? dateFormatter.format(new Date(order.created_at)) : ''}</td>
            <td>${orderActions(order)}</td>
        `;

        return row;
    };

    const prependOrder = (order) => {
        if (!order || !tbody || tbody.querySelector(`[data-order-id="${order.id}"]`)) {
            return false;
        }

        emptyRow?.remove();
        const row = orderRow(order);
        tbody.prepend(row);

        if (table) {
            syncTableLabels(table);
        }

        window.setTimeout(() => row.classList.remove('is-live-new'), 5000);

        return true;
    };

    const syncMissingOrders = async () => {
        if (!realtimeOrders.dataset.liveUrl) {
            return;
        }

        try {
            const response = await fetch(realtimeOrders.dataset.liveUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            const missingOrders = (data.orders || []).filter((order) => (
                !tbody?.querySelector(`[data-order-id="${order.id}"]`)
            ));

            missingOrders.reverse().forEach((order) => prependOrder(order));

        } catch {
            // Keep realtime recovery silent: orders still arrive through the WebSocket.
        }
    };

    echo.connector.pusher.connection.bind('state_change', ({ current }) => {
        if (current === 'connected') {
            syncMissingOrders();
        }
    });

    echo.channel('orders')
        .subscribed(() => {
            syncMissingOrders();
        })
        .listen('.order.created', (event) => {
            if (!prependOrder(event.order)) {
                return;
            }

            playOrderChime();
        });
}

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
