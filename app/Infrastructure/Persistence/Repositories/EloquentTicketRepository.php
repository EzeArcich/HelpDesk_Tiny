<?php
// app/Infrastructure/Persistence/Repositories/EloquentTicketRepository.php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Tickets\Contracts\TicketRepository;
use App\Domain\Tickets\DTO\TicketListFiltersDTO;
use App\Domain\Tickets\Entities\TicketDetails;
use App\Infrastructure\Persistence\Eloquents\Models\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    public function getDetailsById(string|int $id): TicketDetails
    {
        $ticket = Ticket::query()
            ->with([
                'requester:id,name,email',
                'assignee:id,name,email',
                'tags:id,name', // color se podría sumar
                // 'comments.author:id,name,email',
                // 'activities.actor:id,name,email',
            ])
            ->find($id);

        if (!$ticket) {
            throw new ModelNotFoundException("Ticket {$id} not found.");
        }

        return new TicketDetails(
            id: $ticket->id,
            subject: $ticket->subject,
            description: $ticket->description,
            status: (string) $ticket->status,
            priority: (string) $ticket->priority,
            requester: $ticket->requester
                ? ['id' => $ticket->requester->id, 'name' => $ticket->requester->name, 'email' => $ticket->requester->email]
                : null,
            assignee: $ticket->assignee
                ? ['id' => $ticket->assignee->id, 'name' => $ticket->assignee->name, 'email' => $ticket->assignee->email]
                : null,
            tags: $ticket->tags->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                // 'color' => $t->color,
            ])->values()->all(),
            // comments: $ticket->comments->map(fn ($c) => [
            //     'id' => $c->id,
            //     'body' => $c->body,
            //     'visibility' => $c->visibility,
            //     'author' => $c->author ? [
            //         'id' => $c->author->id,
            //         'name' => $c->author->name,
            //         'email' => $c->author->email,
            //     ] : null,
            //     'created_at' => $c->created_at?->toISOString(),
            // ])->values()->all(),
            // activities: $ticket->activities->map(fn ($a) => [
            //     'id' => $a->id,
            //     'type' => $a->type,
            //     'meta' => $a->meta,
            //     'actor' => $a->actor ? [
            //         'id' => $a->actor->id,
            //         'name' => $a->actor->name,
            //         'email' => $a->actor->email,
            //     ] : null,
            //     'created_at' => $a->created_at?->toISOString(),
            // ])->values()->all(),
            createdAt: $ticket->created_at?->toISOString() ?? now()->toISOString(),
            updatedAt: $ticket->updated_at?->toISOString() ?? now()->toISOString(),
        );
    }
}