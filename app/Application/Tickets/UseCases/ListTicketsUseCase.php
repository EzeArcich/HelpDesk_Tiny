<?php

namespace App\Application\Tickets\UseCases;

use App\Domain\Tickets\Contracts\TicketRepository;
use App\Domain\Tickets\DTO\TicketListFiltersDTO;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class ListTicketsUseCase
{
    public function __construct(private TicketRepository $tickets)
    {
        
    }

    public function handle (TicketListFiltersDTO $filters): LengthAwarePaginator
    {
        return $this->tickets->paginate($filters);
    }
}