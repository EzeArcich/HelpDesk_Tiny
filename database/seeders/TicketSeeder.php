<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customerIds = User::query()->where('role', 'customer')->pluck('id');
        $agentIds = User::query()->where('role', 'agent')->pluck('id');
        $tagIds = \App\Models\Tag::query()->pluck('id');

        if ($customerIds->isEmpty()) {
            return;
        }

        $ticketCount = 220;
        $statuses = array_merge(
            array_fill(0, (int) floor($ticketCount * 0.60), 'open'),
            array_fill(0, (int) floor($ticketCount * 0.25), 'in_progress'),
            array_fill(0, $ticketCount - ((int) floor($ticketCount * 0.60) + (int) floor($ticketCount * 0.25)), 'closed')
        );

        shuffle($statuses);

        $subjectPool = [
            'No puedo iniciar sesion en el portal',
            'Error al actualizar metodo de pago',
            'La sincronizacion con el CRM falla',
            'Solicitud de cambio de plan',
            'Adjuntos no se cargan correctamente',
            'Recibo correos duplicados',
            'Consulta sobre limites de API',
            'Dashboard con datos desactualizados',
            'Problema con permisos de usuario',
            'Necesito exportar tickets cerrados',
        ];

        for ($i = 0; $i < $ticketCount; $i++) {
            $status = $statuses[$i];
            $createdAt = Carbon::instance(fake()->dateTimeBetween('-60 days', '-1 day'));
            $updatedAt = Carbon::instance(fake()->dateTimeBetween($createdAt, 'now'));

            $ticket = Ticket::factory()->create([
                'requester_id' => $customerIds->random(),
                'assignee_id' => $agentIds->isNotEmpty() && fake()->boolean(75) ? $agentIds->random() : null,
                'subject' => $subjectPool[array_rand($subjectPool)],
                'description' => fake()->paragraphs(2, true),
                'status' => $status,
                'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
                'closed_at' => $status === 'closed' ? Carbon::instance(fake()->dateTimeBetween($createdAt, 'now')) : null,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);

            if ($tagIds->isEmpty()) {
                continue;
            }

            $randomTagIds = $tagIds->shuffle()->take(fake()->numberBetween(0, 3))->all();

            if ($randomTagIds !== []) {
                $ticket->tags()->syncWithoutDetaching($randomTagIds);
            }
        }
    }
}
