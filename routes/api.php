<?php

use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/tickets', [TicketController::class, 'index'])->name('api.tickets.index');
Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('api.tickets.show');
