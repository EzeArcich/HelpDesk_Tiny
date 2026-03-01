<?php

use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/tickets', [TicketController::class, 'index'])->name('ticket.index');
Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
