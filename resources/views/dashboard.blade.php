@extends('layouts.app')

@section('content')
@php
    $initialTickets = ($tickets ?? collect())->map(fn ($ticket) => [
        'id' => $ticket->id,
        'show_url' => route('tickets.show', $ticket),
        'subject' => $ticket->subject,
        'status' => $ticket->status,
        'priority' => $ticket->priority,
        'requester_id' => $ticket->requester_id,
        'assignee_id' => $ticket->assignee_id,
        'updated_at' => optional($ticket->updated_at)?->toDateTimeString(),
        'updated_at_human' => optional($ticket->updated_at)?->diffForHumans(),
        'requester' => $ticket->requester ? ['id' => $ticket->requester->id, 'name' => $ticket->requester->name] : null,
        'assignee' => $ticket->assignee ? ['id' => $ticket->assignee->id, 'name' => $ticket->assignee->name] : null,
        'tags' => $ticket->tags->map(fn ($tag) => ['id' => $tag->id, 'name' => $tag->name])->values()->all(),
    ])->values();
@endphp
<div class="container py-4" id="ticketsDashboard" data-current-user-id="{{ auth()->id() }}" data-api-base="/api" data-ticket-show-base="{{ route('tickets.show', ['ticket' => '__ID__']) }}" data-initial-tickets='@json($initialTickets)'>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Dashboard</h1>
            <p class="text-secondary mb-0">Gestion de tickets, asignaciones y seguimiento operativo.</p>
        </div>
        <a href="{{ route('tickets.create') }}" class="btn btn-primary" id="btnNewTicket">
            Nuevo ticket
        </a>
    </div>

    <div class="row g-3 mb-4" id="kpiRow">
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-secondary d-block mb-1">Open</small>
                    <div class="fs-4 fw-semibold" data-kpi="open">0</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-secondary d-block mb-1">In progress</small>
                    <div class="fs-4 fw-semibold" data-kpi="in_progress">0</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-secondary d-block mb-1">Closed</small>
                    <div class="fs-4 fw-semibold" data-kpi="closed">0</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-secondary d-block mb-1">My tickets</small>
                    <div class="fs-4 fw-semibold" data-kpi="mine">0</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form class="row g-2 align-items-end mb-3" id="filtersForm" autocomplete="off">
                <div class="col-12 col-md-3">
                    <label class="form-label mb-1" for="filterQ">Buscar</label>
                    <input type="search" class="form-control" id="filterQ" name="q" placeholder="Subject, requester...">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" for="filterStatus">Status</label>
                    <select class="form-select" id="filterStatus" name="status">
                        <option value="">Todos</option>
                        <option value="open">Open</option>
                        <option value="in_progress">In progress</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" for="filterPriority">Priority</label>
                    <select class="form-select" id="filterPriority" name="priority">
                        <option value="">Todas</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" for="filterAssignee">Assignee</label>
                    <select class="form-select" id="filterAssignee" name="assignee">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1" for="filterTag">Tag</label>
                    <select class="form-select" id="filterTag" name="tag">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-12 col-md-1 d-grid">
                    <button class="btn btn-outline-secondary" type="button" id="btnClearFilters">Limpiar</button>
                </div>
            </form>

            <div class="alert alert-danger d-none" id="ticketsError" role="alert"></div>
            <div class="d-flex align-items-center gap-2 text-secondary d-none mb-3" id="ticketsLoading">
                <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                <span>Cargando tickets...</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="ticketsTable">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Subject</th>
                            <th scope="col">Requester</th>
                            <th scope="col">Assignee</th>
                            <th scope="col">Status</th>
                            <th scope="col">Priority</th>
                            <th scope="col">Updated</th>
                        </tr>
                    </thead>
                    <tbody id="ticketsTableBody">
                        @forelse (($tickets ?? collect()) as $ticket)
                            <tr data-ticket-id="{{ $ticket->id }}" data-ticket-url="{{ route('tickets.show', $ticket) }}" role="button">
                                <td>#{{ $ticket->id }}</td>
                                <td class="fw-semibold">{{ $ticket->subject }}</td>
                                <td>{{ $ticket->requester?->name ?? '-' }}</td>
                                <td>{{ $ticket->assignee?->name ?? 'Unassigned' }}</td>
                                <td>
                                    <span class="badge {{ $ticket->status === 'open' ? 'text-bg-warning' : ($ticket->status === 'in_progress' ? 'text-bg-primary' : 'text-bg-success') }}">
                                        {{ $ticket->status === 'in_progress' ? 'In progress' : ucfirst($ticket->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $ticket->priority === 'urgent' ? 'text-bg-danger' : ($ticket->priority === 'high' ? 'text-bg-warning' : ($ticket->priority === 'medium' ? 'text-bg-secondary' : 'text-bg-light')) }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </td>
                                <td class="text-secondary">{{ $ticket->updated_at?->diffForHumans() ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-5">No hay tickets cargados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                <small class="text-secondary" id="paginationSummary">0 tickets</small>
                <div class="btn-group" role="group" aria-label="Paginacion">
                    <button class="btn btn-outline-secondary" type="button" id="btnPrevPage">Anterior</button>
                    <button class="btn btn-outline-secondary" type="button" id="btnNextPage">Siguiente</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="ticketDetailCanvas" aria-labelledby="ticketDetailCanvasLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="ticketDetailCanvasLabel">Detalle de ticket</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="ticketDetailBody">
        <div class="d-flex align-items-center gap-2 text-secondary" id="detailLoading">
            <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
            <span>Cargando detalle...</span>
        </div>

        <div id="detailContent" class="d-none">
            <div class="mb-3">
                <h6 class="mb-1" id="detailSubject">-</h6>
                <p class="text-secondary mb-2" id="detailDescription">-</p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge text-bg-secondary" id="detailStatus">status</span>
                    <span class="badge text-bg-warning" id="detailPriority">priority</span>
                    <span class="badge text-bg-light" id="detailRequester">requester</span>
                    <span class="badge text-bg-light" id="detailAssignee">assignee</span>
                </div>
            </div>

            <div class="mb-4">
                <h6>Tags</h6>
                <div class="d-flex flex-wrap gap-2 mb-2" id="detailTags"></div>
                <form class="d-flex gap-2" id="tagForm">
                    <input type="text" class="form-control" id="tagInput" placeholder="Agregar tag">
                    <button type="submit" class="btn btn-outline-primary">Agregar</button>
                </form>
                <small class="text-secondary">Si endpoint de tags no esta disponible, se mostrara aviso.</small>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="mb-3">Cambiar estado</h6>
                    <form class="row g-2" id="statusForm">
                        <div class="col-8">
                            <select class="form-select" id="statusSelect" required>
                                <option value="open">Open</option>
                                <option value="in_progress">In progress</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="col-4 d-grid">
                            <button class="btn btn-primary" type="submit">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="mb-3">Asignar agente</h6>
                    <form class="row g-2" id="assignForm">
                        <div class="col-8">
                            <select class="form-select" id="assignSelect" required></select>
                        </div>
                        <div class="col-4 d-grid">
                            <button class="btn btn-primary" type="submit">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="mb-3">Agregar comentario</h6>
                    <form id="commentForm">
                        <div class="mb-2">
                            <textarea class="form-control" id="commentBody" rows="3" placeholder="Escribe un comentario..." required></textarea>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <select class="form-select" id="commentVisibility" style="max-width: 180px;">
                                <option value="public">Public</option>
                                <option value="internal">Internal</option>
                            </select>
                            <button class="btn btn-primary" type="submit">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div>
                <h6>Timeline / Activity</h6>
                <ul class="list-group" id="detailActivities">
                    <li class="list-group-item text-secondary">Sin actividad por el momento.</li>
                </ul>
            </div>

            <div class="alert d-none mt-3" id="detailAlert" role="alert"></div>
        </div>
    </div>
</div>
@endsection
