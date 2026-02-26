<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketActivity>
 */
class TicketActivityFactory extends Factory
{
    protected $model = TicketActivity::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['created', 'assigned', 'status_changed', 'commented', 'tagged']);

        return [
            'ticket_id' => Ticket::query()->inRandomOrder()->value('id') ?? Ticket::factory()->create()->id,
            'actor_id' => User::query()->inRandomOrder()->value('id'),
            'type' => $type,
            'meta' => $this->metaForType($type),
            'created_at' => fake()->dateTimeBetween('-45 days', 'now'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function metaForType(string $type): array
    {
        return match ($type) {
            'assigned' => ['assignee_id' => User::query()->where('role', 'agent')->inRandomOrder()->value('id')],
            'status_changed' => ['from' => fake()->randomElement(['open', 'in_progress']), 'to' => fake()->randomElement(['in_progress', 'closed'])],
            'commented' => ['visibility' => fake()->randomElement(['public', 'internal'])],
            'tagged' => ['tags' => []],
            default => ['note' => 'ticket_created'],
        };
    }
}
