<?php
// app/Http/Resources/TicketResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,

            'requester' => $this->whenLoaded('requester', fn () => [
                'id' => $this->requester?->id,
                'name' => $this->requester?->name,
                'email' => $this->requester?->email,
            ]),

            'assignee_id' => $this->whenLoaded('assignee_id', fn () => $this->assignee_id ? [
                'id' => $this->assignee_id->id,
                'name' => $this->assignee_id->name,
                'email' => $this->assignee_id->email,
            ] : null),

            'tags' => $this->whenLoaded('tags', fn () =>
                $this->tags->map(fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                ])->values()
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}