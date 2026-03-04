<?php
// app/Infrastructure/Persistence/Eloquent/Models/Ticket.php

namespace App\Infrastructure\Persistence\Eloquents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $table = 'comments';

    protected $fillable = [
        'body',
        'visibility',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'author_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Ticket::class, 'ticket_id');
    }

}