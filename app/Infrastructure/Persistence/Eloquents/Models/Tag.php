<?php
// app/Infrastructure/Persistence/Eloquent/Models/Ticket.php

namespace App\Infrastructure\Persistence\Eloquents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Tag extends Model
{
    protected $table = 'tags';

    protected $fillable = [
        'name',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assignee_id');
    }

}