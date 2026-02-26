<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $visibility = fake()->randomElement(['public', 'internal']);

        return [
            'ticket_id' => Ticket::query()->inRandomOrder()->value('id') ?? Ticket::factory()->create()->id,
            'author_id' => $this->resolveAuthorId($visibility),
            'body' => fake()->sentences(fake()->numberBetween(1, 3), true),
            'visibility' => $visibility,
            'created_at' => fake()->dateTimeBetween('-45 days', 'now'),
            'updated_at' => now(),
        ];
    }

    private function resolveAuthorId(string $visibility): int
    {
        $role = $visibility === 'internal' ? 'agent' : fake()->randomElement(['customer', 'agent']);

        $userId = User::query()->where('role', $role)->inRandomOrder()->value('id');

        if ($userId !== null) {
            return $userId;
        }

        return User::factory()->create(['role' => $role])->id;
    }
}
