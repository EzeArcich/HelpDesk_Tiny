@php
    $mode = $mode ?? 'show';
    $isShow = $mode === 'show';
    $ticket = $ticket ?? null;
    $assignees = $assignees ?? collect();

    $statusClass = match ($ticket?->status) {
        'open' => 'text-bg-warning',
        'in_progress' => 'text-bg-primary',
        'closed' => 'text-bg-success',
        default => 'text-bg-secondary',
    };

    $priorityClass = match ($ticket?->priority) {
        'urgent' => 'text-bg-danger',
        'high' => 'text-bg-warning',
        'medium' => 'text-bg-secondary',
        'low' => 'text-bg-light',
        default => 'text-bg-secondary',
    };
@endphp

<div class="hd-ticket-shell position-relative">
    <div class="hd-ticket-glow hd-ticket-glow-left"></div>
    <div class="hd-ticket-glow hd-ticket-glow-right"></div>

    <div class="card border-0 shadow-lg hd-ticket-modal">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
            <div>
                <p class="text-secondary text-uppercase mb-1 small">{{ $isShow ? 'Ticket detail' : 'New ticket' }}</p>
                <h1 class="h4 mb-0">
                    {{ $isShow ? '#'.$ticket->id.' · '.$ticket->subject : 'Crear ticket' }}
                </h1>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Volver</a>
        </div>

        <div class="card-body p-4 p-md-5">
            @if ($isShow)
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="badge {{ $statusClass }}">
                        {{ $ticket->status === 'in_progress' ? 'In progress' : ucfirst((string) $ticket->status) }}
                    </span>
                    <span class="badge {{ $priorityClass }}">{{ ucfirst((string) $ticket->priority) }}</span>
                    <span class="badge text-bg-light">Requester: {{ $ticket->requester?->name ?? '-' }}</span>
                    <span class="badge text-bg-light">Assignee: {{ $ticket->assignee?->name ?? 'Unassigned' }}</span>
                </div>

                <div class="row g-4">
                    <div class="col-12 col-lg-7">
                        <section class="mb-4">
                            <h2 class="h6 text-uppercase text-secondary mb-2">Descripcion</h2>
                            <p class="mb-0">{{ $ticket->description ?: 'Sin descripcion.' }}</p>
                        </section>

                        <section class="mb-4">
                            <h2 class="h6 text-uppercase text-secondary mb-2">Tags</h2>
                            <div class="d-flex flex-wrap gap-2">
                                @forelse ($ticket->tags as $tag)
                                    <span class="badge text-bg-light">{{ $tag->name }}</span>
                                @empty
                                    <span class="text-secondary">Sin tags.</span>
                                @endforelse
                            </div>
                        </section>

                        <section>
                            <h2 class="h6 text-uppercase text-secondary mb-2">Comentarios</h2>
                            <div class="list-group">
                                @forelse ($ticket->comments as $comment)
                                    <div class="list-group-item">
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <strong>{{ $comment->author?->name ?? 'Usuario' }}</strong>
                                            <small class="text-secondary">{{ $comment->created_at?->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-1 mt-2">{{ $comment->body }}</p>
                                        <small class="text-secondary">{{ ucfirst((string) $comment->visibility) }}</small>
                                    </div>
                                @empty
                                    <div class="list-group-item text-secondary">Sin comentarios.</div>
                                @endforelse
                            </div>
                        </section>
                    </div>

                    <div class="col-12 col-lg-5">
                        <section>
                            <h2 class="h6 text-uppercase text-secondary mb-2">Actividad</h2>
                            <div class="list-group">
                                @forelse ($ticket->activities as $activity)
                                    <div class="list-group-item">
                                        <div class="fw-semibold">
                                            {{ str_replace('_', ' ', ucfirst((string) $activity->type)) }}
                                        </div>
                                        <small class="text-secondary d-block">
                                            {{ $activity->actor?->name ?? 'Sistema' }} · {{ $activity->created_at?->diffForHumans() }}
                                        </small>
                                    </div>
                                @empty
                                    <div class="list-group-item text-secondary">Sin actividad registrada.</div>
                                @endforelse
                            </div>
                        </section>
                    </div>
                </div>
            @else
                <form action="#" method="post" class="row g-3" onsubmit="event.preventDefault();">
                    <div class="col-12">
                        <label for="ticketSubject" class="form-label">Subject</label>
                        <input id="ticketSubject" type="text" class="form-control" placeholder="Ej: Login caido para clientes premium">
                    </div>

                    <div class="col-12">
                        <label for="ticketDescription" class="form-label">Descripcion</label>
                        <textarea id="ticketDescription" class="form-control" rows="5" placeholder="Describe el problema, impacto y pasos de reproduccion"></textarea>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="ticketPriority" class="form-label">Priority</label>
                        <select id="ticketPriority" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="ticketAssignee" class="form-label">Assignee</label>
                        <select id="ticketAssignee" class="form-select">
                            <option value="">Sin asignar</option>
                            @foreach ($assignees as $assignee)
                                <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            Este modal/panel ya queda listo para reutilizar en `tickets.show` y en el futuro `POST /tickets`.
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit" disabled>Crear ticket (proximamente)</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
