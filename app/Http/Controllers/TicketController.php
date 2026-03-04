<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function show(Ticket $ticket): View
    {
        $ticket->load([
            'requester:id,name,email',
            'assignee:id,name,email',
            'tags:id,name',
            'comments' => fn ($query) => $query
                ->with('author:id,name')
                ->latest()
                ->limit(20),
            'activities' => fn ($query) => $query
                ->with('actor:id,name')
                ->latest('created_at')
                ->limit(20),
        ]);

        return view('tickets.show', compact('ticket'));
    }

    public function create(): View
    {
        $assignees = User::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('tickets.create', compact('assignees'));
    }
}
