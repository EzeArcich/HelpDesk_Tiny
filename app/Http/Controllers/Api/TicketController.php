<?php

namespace App\Http\Controllers\Api;

use App\Application\Tickets\UseCases\GetTicketDetailsUseCase;
use App\Application\Tickets\UseCases\ListTicketsUseCase;
use App\Domain\Tickets\DTO\TicketListFiltersDTO;
use App\Http\Requests\ListTicketsRequest;
use App\Http\Resources\TicketDetailsResource;
use App\Http\Resources\TicketResource;

class TicketController {

    public function __construct(
        private ListTicketsUseCase $listTickets)
    {}

    public function index(ListTicketsRequest $request)
    {

        $filters = TicketListFiltersDTO::fromArray($request->validated());

        $result = $this->listTickets->handle($filters);

        // Collectiom/LengthAwarePaginator of models/DTO's
        return TicketResource::collection($result);

        // Json custom, maybe for DataTable plugin/library
        // return response()->json($result);
    }

    public function show(string|int $id, GetTicketDetailsUseCase $useCase)
    {
        $ticket = $this->listTickets->execute($id);

        return new TicketDetailsResource($ticket);
    }

}