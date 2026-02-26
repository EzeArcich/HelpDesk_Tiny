<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketActivity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TicketActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (TicketActivity::query()->exists()) {
            return;
        }

        Ticket::query()
            ->with(['comments:id,ticket_id,author_id,visibility,created_at', 'tags:id'])
            ->chunkById(100, function ($tickets): void {
                foreach ($tickets as $ticket) {
                    $activities = [
                        [
                            'ticket_id' => $ticket->id,
                            'actor_id' => $ticket->requester_id,
                            'type' => 'created',
                            'meta' => ['subject' => $ticket->subject],
                            'created_at' => $ticket->created_at,
                        ],
                    ];

                    if ($ticket->assignee_id !== null) {
                        $activities[] = [
                            'ticket_id' => $ticket->id,
                            'actor_id' => $ticket->assignee_id,
                            'type' => 'assigned',
                            'meta' => ['assignee_id' => $ticket->assignee_id],
                            'created_at' => Carbon::parse($ticket->created_at)->addMinutes(10),
                        ];
                    }

                    if ($ticket->status !== 'open') {
                        $activities[] = [
                            'ticket_id' => $ticket->id,
                            'actor_id' => $ticket->assignee_id ?? $ticket->requester_id,
                            'type' => 'status_changed',
                            'meta' => [
                                'from' => 'open',
                                'to' => $ticket->status,
                            ],
                            'created_at' => Carbon::parse($ticket->updated_at)->subMinutes(5),
                        ];
                    }

                    if ($ticket->tags->isNotEmpty()) {
                        $activities[] = [
                            'ticket_id' => $ticket->id,
                            'actor_id' => $ticket->assignee_id ?? $ticket->requester_id,
                            'type' => 'tagged',
                            'meta' => ['tag_ids' => $ticket->tags->pluck('id')->values()->all()],
                            'created_at' => Carbon::parse($ticket->updated_at)->subMinutes(15),
                        ];
                    }

                    foreach ($ticket->comments as $comment) {
                        $activities[] = [
                            'ticket_id' => $ticket->id,
                            'actor_id' => $comment->author_id,
                            'type' => 'commented',
                            'meta' => ['visibility' => $comment->visibility],
                            'created_at' => $comment->created_at,
                        ];
                    }

                    foreach ($activities as $activity) {
                        TicketActivity::factory()->create($activity);
                    }
                }
            });
    }
}
