<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Demo for MVP - Soon change implementation
        $tickets = Ticket::query()
            ->with(['requester:id,name', 'assignee:id,name', 'tags:id,name'])
            ->latest('updated_at')
            ->limit(300)
            ->get();

        return view('dashboard', compact('tickets'));
    }
}
