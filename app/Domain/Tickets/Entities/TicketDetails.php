<?php

namespace App\Domain\Tickets\Entities;

final class TicketDetails
{
    public function __construct(
        public readonly string|int $id,
        public readonly string $subject,
        public readonly string $description,
        public readonly string $status,
        public readonly string $priority,
        public readonly ?array $requester, // ['id'=>..., 'name'=>..., 'email'=>...]
        public readonly ?array $assignee,  // ['id'=>..., 'name'=>..., 'email'=>...]
        public readonly array $tags,        // [['id'=>..., 'name'=>..., 'color'=>...], ...]
        // public readonly array $comments,    // ...
        // public readonly array $activities,  // ...
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}
}