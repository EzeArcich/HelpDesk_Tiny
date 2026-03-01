<?php

namespace App\Application\Tickets\UseCases;

use App\Domain\Tickets\Contracts\TicketRepository;
use App\Domain\Tickets\Entities\TicketDetails;

class GetTicketDetailsUseCase
{
    public function __construct(
        private readonly TicketRepository $tickets
    ){}

    public function execute (string|int $id): TicketDetails
    {
        return $this->tickets->getDetailsById($id);
    }
}