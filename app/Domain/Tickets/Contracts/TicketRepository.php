<?php

namespace App\Domain\Tickets\Contracts;

use App\Domain\Tickets\DTO\TicketListFiltersDTO;
use App\Domain\Tickets\Entities\TicketDetails;
use Illuminate\Pagination\LengthAwarePaginator;

interface TicketRepository
{
    public function paginate(TicketListFiltersDTO $filters): LengthAwarePaginator;

    public function getDetailsById(string|int $id): TicketDetails;
}