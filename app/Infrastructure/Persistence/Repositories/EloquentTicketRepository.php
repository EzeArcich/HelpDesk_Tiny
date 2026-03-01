<?php
// app/Infrastructure/Persistence/Repositories/EloquentTicketRepository.php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Tickets\Contracts\TicketRepository;
use App\Domain\Tickets\DTO\TicketListFiltersDTO;
use App\Infrastructure\Persistence\Eloquents\Models\Ticket;
use Illuminate\Pagination\LengthAwarePaginator;

final class EloquentTicketRepository implements TicketRepository
{
    public function paginate(TicketListFiltersDTO $filters): LengthAwarePaginator
    {
        $query = Ticket::query()
            ->with(['requester', 'assignee', 'tags'])
            ->orderByDesc('id');

        if ($filters->status) {
            $query->where('status', $filters->status);
        }

        if ($filters->assigneeId) {
            $query->where('assignee_id', $filters->assigneeId);
        }

        if ($filters->tag) {
            $query->whereHas('tags', function ($q) use ($filters) {
                // Si usás slug, cambialo a ->where('slug', $filters->tag)
                $q->where('name', $filters->tag);
            });
        }

        if ($filters->q) {
            $q = $filters->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('subject', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        return $query->paginate(
            perPage: $filters->perPage,
            page: $filters->page
        );
    }
}