<?php
// app/Infrastructure/Persistence/Eloquent/Models/Ticket.php

namespace App\Infrastructure\Persistence\Eloquents\Models;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $fillable = [
        'requester_id',
        'assignee_id',
        'subject',
        'description',
        'status',
        'priority',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assignee_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'ticket_tag',
            'ticket_id',
            'tag_id'
        );
    }

    public function comments()
    {
         return $this->hasMany(Comment::class); 
    }

}