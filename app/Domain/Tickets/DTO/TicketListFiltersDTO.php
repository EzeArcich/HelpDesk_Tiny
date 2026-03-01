<?php

namespace App\Domain\Tickets\DTO;

final readonly class TicketListFiltersDTO
{
    public function __construct(
        public ?string $status,
        public ?int $assigneeId,
        public ?string $tag,
        public ?string $q,
        public int $perPage,
        public int $page,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? null,
            assigneeId: isset($data['assignee_id']) ? (int)$data['assignee_id'] : null,
            tag: $data['tag'] ?? null,
            q: $data['q'] ?? null,
            perPage: (int)($data['per_page'] ?? 15),
            page: (int)($data['page'] ?? 1),
        );
    }
}