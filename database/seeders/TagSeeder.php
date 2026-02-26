<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $tags = [
            'billing',
            'bug',
            'feature',
            'urgent',
            'account',
            'integration',
            'authentication',
            'email',
            'reporting',
            'ui',
            'performance',
            'api',
            'security',
            'onboarding',
            'mobile',
        ];

        Tag::query()->upsert(
            array_map(fn (string $name) => ['name' => $name, 'created_at' => $now, 'updated_at' => $now], $tags),
            ['name'],
            ['updated_at']
        );
    }
}
