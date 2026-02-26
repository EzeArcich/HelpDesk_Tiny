<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customerIds = User::query()->where('role', 'customer')->pluck('id');
        $agentIds = User::query()->where('role', 'agent')->pluck('id');

        Ticket::query()->chunkById(100, function ($tickets) use ($customerIds, $agentIds): void {
            foreach ($tickets as $ticket) {
                $count = fake()->numberBetween(0, 6);

                for ($i = 0; $i < $count; $i++) {
                    $isInternal = fake()->boolean(30);
                    $visibility = $isInternal ? 'internal' : 'public';

                    $authorId = $visibility === 'internal' || fake()->boolean(35)
                        ? ($agentIds->isNotEmpty() ? $agentIds->random() : $ticket->requester_id)
                        : ($customerIds->isNotEmpty() ? $customerIds->random() : $ticket->requester_id);

                    $createdAt = Carbon::instance(fake()->dateTimeBetween($ticket->created_at ?? '-30 days', 'now'));

                    Comment::factory()->create([
                        'ticket_id' => $ticket->id,
                        'author_id' => $authorId,
                        'visibility' => $visibility,
                        'body' => fake()->sentences(fake()->numberBetween(1, 4), true),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
        });
    }
}
