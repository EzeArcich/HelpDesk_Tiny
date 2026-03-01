<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListTicketsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ajustalo si tenés policies/roles. Para MVP: autenticado.
        // return $this->user() !== null;

        // Para dev
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'string',
                Rule::in(['open', 'in_progress', 'closed']),
            ],

            'assignee_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:users,id',
            ],

            // Tags (opcional en el MVP). Si no implementaste tags todavía,
            // podés comentar este bloque.
            'tag_id' => [
                'sometimes',
                'integer',
                'exists:tags,id',
            ],

            // Búsqueda simple (subject/description/etc. en repo/usecase)
            'q' => [
                'sometimes',
                'string',
                'max:200',
            ],

            // Paginación
            'page' => [
                'sometimes',
                'integer',
                'min:1',
            ],
            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('q')) {
            $this->merge([
                'q' => trim((string) $this->input('q')),
            ]);
        }
    }

    /**
     * Helper para el Controller/UseCase: devuelve solo filtros presentes y válidos.
     */
    public function filters(): array
    {
        $data = $this->validated();

        return array_filter([
            'status'      => $data['status'] ?? null,
            'assignee_id' => $data['assignee_id'] ?? null,
            'tag_id'      => $data['tag_id'] ?? null,
            'q'           => $data['q'] ?? null,
        ], static fn ($v) => $v !== null && $v !== '');
    }

    public function pagination(): array
    {
        $data = $this->validated();

        return [
            'page'     => (int) ($data['page'] ?? 1),
            'per_page' => (int) ($data['per_page'] ?? 15),
        ];
    }
}