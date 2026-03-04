const ALLOWED_TRANSITIONS = {
    open: ['in_progress'],
    in_progress: ['closed'],
    closed: [],
};

const statusBadgeClass = {
    open: 'text-bg-warning',
    in_progress: 'text-bg-primary',
    closed: 'text-bg-success',
};

const priorityBadgeClass = {
    low: 'text-bg-light',
    medium: 'text-bg-secondary',
    high: 'text-bg-warning',
    urgent: 'text-bg-danger',
};

const text = (v, fallback = '-') => (v === null || v === undefined || v === '' ? fallback : String(v));

const escapeHtml = (value) => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

const parseListResponse = (json) => {
    if (Array.isArray(json)) {
        return { data: json, meta: { current_page: 1, last_page: 1, total: json.length } };
    }

    if (json && Array.isArray(json.data)) {
        const current = Number(json.current_page ?? json.meta?.current_page ?? 1);
        const last = Number(json.last_page ?? json.meta?.last_page ?? 1);
        const total = Number(json.total ?? json.meta?.total ?? json.data.length);

        return {
            data: json.data,
            meta: {
                current_page: Number.isFinite(current) ? current : 1,
                last_page: Number.isFinite(last) ? last : 1,
                total: Number.isFinite(total) ? total : json.data.length,
            },
        };
    }

    return { data: [], meta: { current_page: 1, last_page: 1, total: 0 } };
};

const initDashboardTickets = () => {
    const root = document.getElementById('ticketsDashboard');

    if (!root) {
        return;
    }

    const currentUserId = Number(root.dataset.currentUserId || 0);
    const apiBase = root.dataset.apiBase || '/api';
    const ticketShowBase = root.dataset.ticketShowBase || '/tickets/__ID__';
    const initialTickets = (() => {
        try {
            const parsed = JSON.parse(root.dataset.initialTickets || '[]');
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    })();
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const els = {
        filtersForm: document.getElementById('filtersForm'),
        filterQ: document.getElementById('filterQ'),
        filterStatus: document.getElementById('filterStatus'),
        filterPriority: document.getElementById('filterPriority'),
        filterAssignee: document.getElementById('filterAssignee'),
        filterTag: document.getElementById('filterTag'),
        btnClearFilters: document.getElementById('btnClearFilters'),
        btnPrevPage: document.getElementById('btnPrevPage'),
        btnNextPage: document.getElementById('btnNextPage'),
        paginationSummary: document.getElementById('paginationSummary'),
        ticketsLoading: document.getElementById('ticketsLoading'),
        ticketsError: document.getElementById('ticketsError'),
        tableBody: document.getElementById('ticketsTableBody'),
        kpiOpen: document.querySelector('[data-kpi="open"]'),
        kpiInProgress: document.querySelector('[data-kpi="in_progress"]'),
        kpiClosed: document.querySelector('[data-kpi="closed"]'),
        kpiMine: document.querySelector('[data-kpi="mine"]'),

        offcanvasEl: document.getElementById('ticketDetailCanvas'),
        detailLoading: document.getElementById('detailLoading'),
        detailContent: document.getElementById('detailContent'),
        detailSubject: document.getElementById('detailSubject'),
        detailDescription: document.getElementById('detailDescription'),
        detailStatus: document.getElementById('detailStatus'),
        detailPriority: document.getElementById('detailPriority'),
        detailRequester: document.getElementById('detailRequester'),
        detailAssignee: document.getElementById('detailAssignee'),
        detailTags: document.getElementById('detailTags'),
        detailActivities: document.getElementById('detailActivities'),
        detailAlert: document.getElementById('detailAlert'),

        statusForm: document.getElementById('statusForm'),
        statusSelect: document.getElementById('statusSelect'),
        assignForm: document.getElementById('assignForm'),
        assignSelect: document.getElementById('assignSelect'),
        commentForm: document.getElementById('commentForm'),
        commentBody: document.getElementById('commentBody'),
        commentVisibility: document.getElementById('commentVisibility'),
        tagForm: document.getElementById('tagForm'),
        tagInput: document.getElementById('tagInput'),
    };

    const offcanvas = (window.bootstrap && els.offcanvasEl)
        ? window.bootstrap.Offcanvas.getOrCreateInstance(els.offcanvasEl)
        : null;

    const state = {
        allTickets: initialTickets,
        useLocalData: initialTickets.length > 0,
        perPage: 20,
        page: 1,
        filters: {
            q: '',
            status: '',
            priority: '',
            assignee: '',
            tag: '',
        },
        tickets: [],
        meta: { current_page: 1, last_page: 1, total: 0 },
        currentTicketId: null,
        currentTicket: null,
        assignees: [],
        tags: [],
    };

    const buildTicketShowUrl = (ticket) => {
        if (ticket?.show_url) {
            return String(ticket.show_url);
        }

        const ticketId = ticket?.id ?? '';
        return ticketShowBase.replace('__ID__', String(ticketId));
    };

    const loadLocalTickets = () => {
        let filtered = state.allTickets.slice();

        if (state.filters.q) {
            const term = state.filters.q.toLowerCase();
            filtered = filtered.filter((ticket) => (
                text(ticket.subject, '').toLowerCase().includes(term)
                || text(ticket.requester?.name || ticket.requester_name, '').toLowerCase().includes(term)
                || text(ticket.assignee?.name || ticket.assignee_name, '').toLowerCase().includes(term)
            ));
        }

        if (state.filters.status) {
            filtered = filtered.filter((ticket) => ticket.status === state.filters.status);
        }

        if (state.filters.priority) {
            filtered = filtered.filter((ticket) => ticket.priority === state.filters.priority);
        }

        if (state.filters.assignee) {
            filtered = filtered.filter((ticket) => String(ticket.assignee_id || ticket.assignee?.id || '') === String(state.filters.assignee));
        }

        if (state.filters.tag) {
            filtered = filtered.filter((ticket) => (ticket.tags || []).some((tag) => (
                String(typeof tag === 'string' ? tag : tag.name).toLowerCase() === String(state.filters.tag).toLowerCase()
            )));
        }

        const total = filtered.length;
        const lastPage = Math.max(1, Math.ceil(total / state.perPage));
        const currentPage = Math.min(Math.max(1, state.page), lastPage);
        const start = (currentPage - 1) * state.perPage;
        const end = start + state.perPage;

        state.tickets = filtered.slice(start, end);
        state.meta = { current_page: currentPage, last_page: lastPage, total };
    };

    const apiFetch = async (path, options = {}) => {
        const headers = {
            Accept: 'application/json',
            ...(options.method && options.method !== 'GET' ? { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf } : {}),
            ...(options.headers || {}),
        };

        const res = await fetch(`${apiBase}${path}`, {
            credentials: 'same-origin',
            ...options,
            headers,
        });

        const payload = await res.json().catch(() => ({}));

        if (!res.ok) {
            const message = payload.message || 'No se pudo completar la operacion.';
            throw new Error(message);
        }

        return payload;
    };

    const setTicketsLoading = (on) => {
        els.ticketsLoading.classList.toggle('d-none', !on);
    };

    const showTicketsError = (msg = '') => {
        if (!msg) {
            els.ticketsError.classList.add('d-none');
            els.ticketsError.textContent = '';
            return;
        }

        els.ticketsError.classList.remove('d-none');
        els.ticketsError.textContent = msg;
    };

    const showDetailAlert = (message, type = 'danger') => {
        els.detailAlert.className = `alert alert-${type} mt-3`;
        els.detailAlert.textContent = message;
        els.detailAlert.classList.remove('d-none');
    };

    const clearDetailAlert = () => {
        els.detailAlert.className = 'alert d-none mt-3';
        els.detailAlert.textContent = '';
    };

    const renderKPIs = () => {
        const countOpen = state.tickets.filter((t) => t.status === 'open').length;
        const countInProgress = state.tickets.filter((t) => t.status === 'in_progress').length;
        const countClosed = state.tickets.filter((t) => t.status === 'closed').length;
        const countMine = state.tickets.filter((t) => Number(t.assignee_id || t.assignee?.id || 0) === currentUserId).length;

        els.kpiOpen.textContent = String(countOpen);
        els.kpiInProgress.textContent = String(countInProgress);
        els.kpiClosed.textContent = String(countClosed);
        els.kpiMine.textContent = String(countMine);
    };

    const humanStatus = (status) => {
        if (status === 'in_progress') return 'In progress';
        if (status === 'open') return 'Open';
        if (status === 'closed') return 'Closed';
        return text(status);
    };

    const renderTable = () => {
        if (!state.tickets.length) {
            els.tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-secondary py-5">No se encontraron tickets para los filtros actuales.</td></tr>';
            return;
        }

        els.tableBody.innerHTML = state.tickets
            .map((ticket) => {
                const statusClass = statusBadgeClass[ticket.status] || 'text-bg-secondary';
                const priorityClass = priorityBadgeClass[ticket.priority] || 'text-bg-secondary';
                const id = ticket.id;
                const showUrl = buildTicketShowUrl(ticket);

                return `
                    <tr data-ticket-id="${id}" data-ticket-url="${escapeHtml(showUrl)}" role="button">
                        <td>#${id}</td>
                        <td class="fw-semibold">${escapeHtml(text(ticket.subject))}</td>
                        <td>${escapeHtml(text(ticket.requester?.name || ticket.requester_name, '-'))}</td>
                        <td>${escapeHtml(text(ticket.assignee?.name || ticket.assignee_name, 'Unassigned'))}</td>
                        <td><span class="badge ${statusClass}">${escapeHtml(humanStatus(ticket.status))}</span></td>
                        <td><span class="badge ${priorityClass}">${escapeHtml(text(ticket.priority, '-'))}</span></td>
                        <td class="text-secondary">${escapeHtml(text(ticket.updated_at_human || ticket.updated_at, '-'))}</td>
                    </tr>
                `;
            })
            .join('');
    };

    const renderPagination = () => {
        const { current_page: currentPage, last_page: lastPage, total } = state.meta;
        els.paginationSummary.textContent = `${total} tickets · pagina ${currentPage} de ${lastPage}`;
        els.btnPrevPage.disabled = currentPage <= 1;
        els.btnNextPage.disabled = currentPage >= lastPage;
    };

    const updateFilterOptions = () => {
        const assigneeMap = new Map();
        const tagSet = new Set();

        state.tickets.forEach((t) => {
            const assignee = t.assignee;
            if (assignee && assignee.id) {
                assigneeMap.set(String(assignee.id), assignee.name);
            }

            (t.tags || []).forEach((tag) => {
                tagSet.add(typeof tag === 'string' ? tag : tag.name);
            });
        });

        state.assignees = Array.from(assigneeMap, ([id, name]) => ({ id, name }));
        state.tags = Array.from(tagSet).filter(Boolean);

        const assigneeOptions = ['<option value="">Todos</option>']
            .concat(state.assignees.map((a) => `<option value="${escapeHtml(a.id)}">${escapeHtml(a.name)}</option>`));

        const tagOptions = ['<option value="">Todos</option>']
            .concat(state.tags.map((tag) => `<option value="${escapeHtml(tag)}">${escapeHtml(tag)}</option>`));

        els.filterAssignee.innerHTML = assigneeOptions.join('');
        els.filterTag.innerHTML = tagOptions.join('');

        els.filterAssignee.value = state.filters.assignee;
        els.filterTag.value = state.filters.tag;
    };

    const toQuery = () => {
        const params = new URLSearchParams();

        Object.entries(state.filters).forEach(([key, value]) => {
            if (value) params.set(key, value);
        });

        params.set('page', String(state.page));
        return params.toString();
    };

    const loadTickets = async () => {
        if (state.useLocalData) {
            loadLocalTickets();
            showTicketsError();
            renderKPIs();
            updateFilterOptions();
            renderTable();
            renderPagination();
            return;
        }

        setTicketsLoading(true);
        showTicketsError();

        try {
            const json = await apiFetch(`/tickets?${toQuery()}`);
            const { data, meta } = parseListResponse(json);
            state.tickets = data;
            state.meta = meta;

            renderKPIs();
            updateFilterOptions();
            renderTable();
            renderPagination();
        } catch (error) {
            if (state.allTickets.length > 0) {
                state.useLocalData = true;
                loadLocalTickets();
                renderKPIs();
                updateFilterOptions();
                renderTable();
                renderPagination();
                showTicketsError();
            } else {
                state.tickets = [];
                state.meta = { current_page: 1, last_page: 1, total: 0 };
                renderTable();
                renderPagination();
                showTicketsError(error.message);
            }
        } finally {
            setTicketsLoading(false);
        }
    };

    const setDetailLoading = (on) => {
        els.detailLoading.classList.toggle('d-none', !on);
        els.detailContent.classList.toggle('d-none', on);
    };

    const renderTags = (tags = []) => {
        if (!tags.length) {
            els.detailTags.innerHTML = '<span class="badge text-bg-light">Sin tags</span>';
            return;
        }

        els.detailTags.innerHTML = tags
            .map((tag) => {
                const name = typeof tag === 'string' ? tag : tag.name;
                return `<span class="badge text-bg-light">${escapeHtml(text(name))}</span>`;
            })
            .join('');
    };

    const renderActivities = (activities = []) => {
        if (!activities.length) {
            els.detailActivities.innerHTML = '<li class="list-group-item text-secondary">Sin actividad por el momento.</li>';
            return;
        }

        els.detailActivities.innerHTML = activities
            .map((activity) => `
                <li class="list-group-item">
                    <div class="fw-semibold">${escapeHtml(text(activity.title || activity.type, 'Actividad'))}</div>
                    <div class="small text-secondary">${escapeHtml(text(activity.description || activity.message, ''))}</div>
                    <div class="small text-secondary">${escapeHtml(text(activity.created_at_human || activity.created_at, ''))}</div>
                </li>
            `)
            .join('');
    };

    const fillAssignSelect = () => {
        const options = ['<option value="">Sin asignar</option>']
            .concat(state.assignees.map((a) => `<option value="${escapeHtml(a.id)}">${escapeHtml(a.name)}</option>`));

        els.assignSelect.innerHTML = options.join('');
    };

    const renderDetail = (ticket) => {
        state.currentTicket = ticket;

        els.detailSubject.textContent = text(ticket.subject);
        els.detailDescription.textContent = text(ticket.description, 'Sin descripcion');

        els.detailStatus.className = `badge ${statusBadgeClass[ticket.status] || 'text-bg-secondary'}`;
        els.detailStatus.textContent = humanStatus(ticket.status);

        els.detailPriority.className = `badge ${priorityBadgeClass[ticket.priority] || 'text-bg-secondary'}`;
        els.detailPriority.textContent = text(ticket.priority);

        els.detailRequester.textContent = `Requester: ${text(ticket.requester?.name || ticket.requester_name, '-')}`;
        els.detailAssignee.textContent = `Assignee: ${text(ticket.assignee?.name || ticket.assignee_name, 'Unassigned')}`;

        els.statusSelect.value = ticket.status || 'open';
        fillAssignSelect();
        els.assignSelect.value = String(ticket.assignee_id || ticket.assignee?.id || '');

        renderTags(ticket.tags || []);
        renderActivities(ticket.activities || ticket.activity_log || []);
    };

    const loadTicketDetail = async (ticketId) => {
        state.currentTicketId = ticketId;
        clearDetailAlert();
        setDetailLoading(true);
        offcanvas?.show();

        try {
            const data = await apiFetch(`/tickets/${ticketId}`);
            renderDetail(data.data || data);
        } catch (error) {
            showDetailAlert(error.message);
        } finally {
            setDetailLoading(false);
        }
    };

    const optimisticRowUpdate = () => {
        if (!state.currentTicketId || !state.currentTicket) return;

        const targetIndex = state.tickets.findIndex((t) => Number(t.id) === Number(state.currentTicketId));
        if (targetIndex >= 0) {
            state.tickets[targetIndex] = {
                ...state.tickets[targetIndex],
                ...state.currentTicket,
            };
        }

        renderTable();
        renderKPIs();
    };

    const applyFiltersFromUI = () => {
        state.filters.q = els.filterQ.value.trim();
        state.filters.status = els.filterStatus.value;
        state.filters.priority = els.filterPriority.value;
        state.filters.assignee = els.filterAssignee.value;
        state.filters.tag = els.filterTag.value;
    };

    els.filtersForm.addEventListener('submit', (e) => {
        e.preventDefault();
        state.page = 1;
        applyFiltersFromUI();
        loadTickets();
    });

    ['change', 'input'].forEach((eventName) => {
        els.filterQ.addEventListener(eventName, () => {
            state.page = 1;
            applyFiltersFromUI();
            loadTickets();
        });
    });

    [els.filterStatus, els.filterPriority, els.filterAssignee, els.filterTag].forEach((el) => {
        el.addEventListener('change', () => {
            state.page = 1;
            applyFiltersFromUI();
            loadTickets();
        });
    });

    els.btnClearFilters.addEventListener('click', () => {
        state.filters = { q: '', status: '', priority: '', assignee: '', tag: '' };
        state.page = 1;

        els.filterQ.value = '';
        els.filterStatus.value = '';
        els.filterPriority.value = '';
        els.filterAssignee.value = '';
        els.filterTag.value = '';

        loadTickets();
    });

    els.btnPrevPage.addEventListener('click', () => {
        if (state.meta.current_page <= 1) return;
        state.page = state.meta.current_page - 1;
        loadTickets();
    });

    els.btnNextPage.addEventListener('click', () => {
        if (state.meta.current_page >= state.meta.last_page) return;
        state.page = state.meta.current_page + 1;
        loadTickets();
    });

    els.tableBody.addEventListener('click', (e) => {
        const row = e.target.closest('tr[data-ticket-id]');
        if (!row) return;

        const targetUrl = row.dataset.ticketUrl;
        if (!targetUrl) return;
        window.location.assign(targetUrl);
    });

    els.statusForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearDetailAlert();

        if (!state.currentTicket) return;

        const from = state.currentTicket.status;
        const to = els.statusSelect.value;

        if (from === to) {
            showDetailAlert('Selecciona un estado diferente.', 'warning');
            return;
        }

        if (!(ALLOWED_TRANSITIONS[from] || []).includes(to)) {
            showDetailAlert(`Transicion invalida: ${from} -> ${to}.`, 'danger');
            return;
        }

        try {
            await apiFetch(`/tickets/${state.currentTicketId}/status`, {
                method: 'POST',
                body: JSON.stringify({ status: to }),
            });

            state.currentTicket.status = to;
            renderDetail(state.currentTicket);
            optimisticRowUpdate();
            showDetailAlert('Estado actualizado.', 'success');
        } catch (error) {
            showDetailAlert(error.message);
        }
    });

    els.assignForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearDetailAlert();

        if (!state.currentTicket) return;

        const assigneeId = els.assignSelect.value || null;

        try {
            await apiFetch(`/tickets/${state.currentTicketId}/assign`, {
                method: 'POST',
                body: JSON.stringify({ assignee_id: assigneeId }),
            });

            const selected = state.assignees.find((a) => String(a.id) === String(assigneeId));
            state.currentTicket.assignee_id = assigneeId;
            state.currentTicket.assignee = selected ? { id: selected.id, name: selected.name } : null;
            renderDetail(state.currentTicket);
            optimisticRowUpdate();
            showDetailAlert('Asignacion actualizada.', 'success');
        } catch (error) {
            showDetailAlert(error.message);
        }
    });

    els.commentForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearDetailAlert();

        if (!state.currentTicket) return;

        const body = els.commentBody.value.trim();
        const visibility = els.commentVisibility.value;

        if (!body) {
            showDetailAlert('El comentario no puede estar vacio.', 'warning');
            return;
        }

        try {
            const json = await apiFetch(`/tickets/${state.currentTicketId}/comments`, {
                method: 'POST',
                body: JSON.stringify({ body, visibility }),
            });

            const comment = json.data || json;
            const currentActivities = state.currentTicket.activities || [];
            state.currentTicket.activities = [
                {
                    title: `Comentario (${visibility})`,
                    description: comment.body || body,
                    created_at_human: comment.created_at_human || 'Recien',
                },
                ...currentActivities,
            ];

            renderActivities(state.currentTicket.activities);
            els.commentBody.value = '';
            showDetailAlert('Comentario agregado.', 'success');
        } catch (error) {
            showDetailAlert(error.message);
        }
    });

    els.tagForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearDetailAlert();

        if (!state.currentTicket) return;

        const tag = els.tagInput.value.trim();
        if (!tag) {
            showDetailAlert('Ingresa un tag.', 'warning');
            return;
        }

        try {
            await apiFetch(`/tickets/${state.currentTicketId}/tags`, {
                method: 'POST',
                body: JSON.stringify({ tag }),
            });

            const tags = Array.isArray(state.currentTicket.tags) ? state.currentTicket.tags.slice() : [];
            tags.push({ name: tag });
            state.currentTicket.tags = tags;

            renderTags(tags);
            els.tagInput.value = '';
            showDetailAlert('Tag agregado.', 'success');
        } catch (error) {
            showDetailAlert(`${error.message} (tags opcional en MVP).`, 'warning');
        }
    });

    if (state.allTickets.length > 0) {
        loadLocalTickets();
        renderKPIs();
        updateFilterOptions();
        renderTable();
        renderPagination();
    } else {
        loadTickets();
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboardTickets);
} else {
    initDashboardTickets();
}
