<?php

namespace App\Http\Resources;

use App\Domain\Tickets\Entities\TicketDetails;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketDetailsResource extends JsonResource
{
    /** @var TicketDetails */
    public $resource;

    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'subject' => $this->resource->subject,
            'description' => $this->resource->description,
            'status' => $this->resource->status,
            'priority' => $this->resource->priority,
            'requester' => $this->resource->requester,
            'assignee' => $this->resource->assignee,
            'tags' => $this->resource->tags,
            // 'comments' => $this->resource->comments,
            // 'activities' => $this->resource->activities,
            'created_at' => $this->resource->createdAt,
            'updated_at' => $this->resource->updatedAt,
        ];
    }
}