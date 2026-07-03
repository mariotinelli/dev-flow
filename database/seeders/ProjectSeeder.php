<?php

declare(strict_types = 1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('projects')->upsert(
            $this->projects()->map(fn (array $project, int $index): array => [
                'name'        => $project['name'],
                'key'         => $project['key'],
                'slug'        => Str::slug($project['name']),
                'description' => $project['description'],
                'color'       => $project['color'],
                'starts_at'   => $now->copy()->addDays($index)->toDateString(),
                'due_at'      => $now->copy()->addDays($index + 60)->toDateString(),
                'deleted_at'  => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ])->all(),
            ['key'],
            ['name', 'slug', 'description', 'color', 'starts_at', 'due_at', 'deleted_at', 'updated_at']
        );
    }

    /**
     * @return Collection<int, array{name: string, key: string, description: string, color: string}>
     */
    private function projects(): Collection
    {
        return collect([
            ['name' => 'Dev Flow Platform', 'key' => 'DFP-001', 'description' => 'Core platform for internal workflow management.', 'color' => '#2563EB'],
            ['name' => 'Client Portal', 'key' => 'CP-001', 'description' => 'Self-service portal for client communication.', 'color' => '#16A34A'],
            ['name' => 'Mobile Companion', 'key' => 'MC-001', 'description' => 'Mobile companion app for field updates.', 'color' => '#9333EA'],
            ['name' => 'Analytics Hub', 'key' => 'AH-001', 'description' => 'Dashboards and reports for product metrics.', 'color' => '#EA580C'],
            ['name' => 'Billing Engine', 'key' => 'BE-001', 'description' => 'Subscription and invoice automation.', 'color' => '#0891B2'],
            ['name' => 'Support Desk', 'key' => 'SD-001', 'description' => 'Ticket triage and support operations.', 'color' => '#DB2777'],
            ['name' => 'Knowledge Base', 'key' => 'KB-001', 'description' => 'Documentation and team knowledge sharing.', 'color' => '#65A30D'],
            ['name' => 'API Gateway', 'key' => 'AG-001', 'description' => 'Unified gateway for public integrations.', 'color' => '#4F46E5'],
            ['name' => 'Automation Studio', 'key' => 'AS-001', 'description' => 'Low-code automation tools for operations.', 'color' => '#CA8A04'],
            ['name' => 'Design System', 'key' => 'DS-001', 'description' => 'Reusable interface patterns and components.', 'color' => '#7C3AED'],
            ['name' => 'Notification Center', 'key' => 'NC-001', 'description' => 'Email and in-app notification delivery.', 'color' => '#DC2626'],
            ['name' => 'Search Service', 'key' => 'SS-001', 'description' => 'Search indexing and discovery features.', 'color' => '#0D9488'],
            ['name' => 'Audit Trail', 'key' => 'AT-001', 'description' => 'Compliance logs and activity history.', 'color' => '#475569'],
            ['name' => 'Onboarding Flow', 'key' => 'OF-001', 'description' => 'Guided account and workspace onboarding.', 'color' => '#0284C7'],
            ['name' => 'Integration Hub', 'key' => 'IH-001', 'description' => 'Third-party connector management.', 'color' => '#059669'],
            ['name' => 'Data Pipeline', 'key' => 'DP-001', 'description' => 'ETL workflows for operational data.', 'color' => '#B45309'],
            ['name' => 'Security Center', 'key' => 'SC-001', 'description' => 'Security controls and access monitoring.', 'color' => '#BE123C'],
            ['name' => 'Roadmap Board', 'key' => 'RB-001', 'description' => 'Product roadmap planning and prioritization.', 'color' => '#4338CA'],
            ['name' => 'Quality Lab', 'key' => 'QL-001', 'description' => 'QA experiments and regression coverage.', 'color' => '#15803D'],
            ['name' => 'Release Tracker', 'key' => 'RT-001', 'description' => 'Release planning and deployment visibility.', 'color' => '#A21CAF'],
        ]);
    }
}
