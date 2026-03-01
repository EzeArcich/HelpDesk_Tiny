<?php

namespace App\Domain\Tickets\Contracts;

use App\Domain\Tickets\DTO\TicketListFiltersDTO;
use Illuminate\Pagination\LengthAwarePaginator;

interface TicketRepository
{
    public function paginate(TicketListFiltersDTO $filters): LengthAwarePaginator;
}