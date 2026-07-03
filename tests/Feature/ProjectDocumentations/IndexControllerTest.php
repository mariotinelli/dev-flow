<?php

declare(strict_types = 1);

use App\Enums\Permissions\Projects\DocumentPermissions;
use App\Enums\ProjectDocumentationType;
use App\Enums\ProjectDocumentationVisibility;
use App\Models\ProjectDocumentation;
use Inertia\Testing\AssertableInertia as Assert;

test('project members with view permission can view project documentations', function () {
    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::View);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('project-documentations/Index')
            ->has('projectDocumentations.data', 0)
            ->where('can.create', false));
});

test('index passes types and categories to the view', function () {
    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::View);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('types')
            ->has('categories'));
});

test('can filter project documentations by search', function () {
    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::View);

    ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'Setup Guide',
    ]);

    ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'API Reference',
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.index', ['search' => 'Setup']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('projectDocumentations.data', 1)
            ->where('projectDocumentations.data.0.title', 'Setup Guide')
            ->where('filters.search', 'Setup'));
});

test('can filter project documentations by type', function () {
    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::View);

    ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'type'       => ProjectDocumentationType::Link,
        'url'        => 'https://example.com',
    ]);

    ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'type'       => ProjectDocumentationType::File,
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.index', ['type' => ProjectDocumentationType::Link->value]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('projectDocumentations.data', 1)
            ->where('projectDocumentations.data.0.type', ProjectDocumentationType::Link->value)
            ->where('filters.type', (string) ProjectDocumentationType::Link->value));
});

test('admin users can see AdministratorsOnly project documentations', function () {
    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::View);

    $user->assignRole('admin');

    ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'visibility' => ProjectDocumentationVisibility::AdministratorsOnly,
        'type'       => ProjectDocumentationType::Link,
        'url'        => 'https://example.com/admin',
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('projectDocumentations.data', 1)
            ->where('projectDocumentations.data.0.url', 'https://example.com/admin'));
});

test('non-admin users cannot see AdministratorsOnly project documentations', function () {
    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::View);

    ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'visibility' => ProjectDocumentationVisibility::AdministratorsOnly,
        'type'       => ProjectDocumentationType::Link,
        'url'        => 'https://example.com/admin',
    ]);

    ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'visibility' => ProjectDocumentationVisibility::ProjectMembers,
        'type'       => ProjectDocumentationType::Link,
        'url'        => 'https://example.com/public',
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('projectDocumentations.data', 1)
            ->where('projectDocumentations.data.0.url', 'https://example.com/public'));
});

test('can combine search and type filters', function () {
    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::View);

    ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'Setup Guide',
        'type'       => ProjectDocumentationType::Link,
        'url'        => 'https://example.com',
    ]);

    ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'Setup Manual',
        'type'       => ProjectDocumentationType::File,
    ]);

    ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'API Docs',
        'type'       => ProjectDocumentationType::Link,
        'url'        => 'https://api.com',
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.index', [
            'search' => 'Setup',
            'type'   => ProjectDocumentationType::Link->value,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('projectDocumentations.data', 1)
            ->where('projectDocumentations.data.0.title', 'Setup Guide'));
});
