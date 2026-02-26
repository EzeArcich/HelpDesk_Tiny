<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement([
            ...array_fill(0, 60, 'open'),
            ...array_fill(0, 25, 'in_progress'),
            ...array_fill(0, 15, 'closed'),
        ]);

        return [
            'requester_id' => $this->resolveUserId('customer'),
            'assignee_id' => fake()->boolean(80) ? $this->resolveUserId('agent') : null,
            'subject' => fake()->randomElement([
                'No puedo acceder a mi cuenta',
                'Error al procesar el pago',
                'La integracion con API falla',
                'Necesito actualizar plan',
                'La app muestra pantalla en blanco',
                'Solicitud de nueva funcionalidad',
                'Problema con notificaciones por correo',
            ]),
            'description' => fake()->paragraphs(2, true),
            'status' => $status,
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'closed_at' => $status === 'closed' ? fake()->dateTimeBetween('-30 days', 'now') : null,
            'created_at' => fake()->dateTimeBetween('-60 days', '-1 day'),
            'updated_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    private function resolveUserId(string $role): int
    {
        $user = User::query()->where('role', $role)->inRandomOrder()->first();

        if ($user !== null) {
            return $user->id;
        }

        return User::factory()->create(['role' => $role])->id;
    }
}
