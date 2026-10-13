<?php

use App\Models\Task;
use App\Models\TaskReport;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('task pages are shells that load their data from json endpoints', function (string $path) {
    $user = User::factory()->create();
    $task = Task::factory()->for($user)->create(['title' => 'Shell only task']);

    $response = $this->actingAs($user)->get(str_replace('{task}', $task->id, $path));

    $response->assertOk()->assertDontSee('Shell only task');
})->with(['/tasks', '/tasks/{task}', '/tasks/{task}/edit', '/dashboard']);

test('the task list json is paginated newest first with page links', function () {
    $user = User::factory()->create();
    Task::factory()->for($user)->count(12)->create(['updated_at' => now()->subDay()]);
    $latest = Task::factory()->for($user)->create(['title' => 'Latest', 'updated_at' => now()]);

    $response = $this->actingAs($user)->getJson('/tasks');

    $response->assertOk()
        ->assertJsonPath('data.0.id', $latest->id)
        ->assertJsonPath('data.0.url', route('tasks.show', $latest))
        ->assertJsonPath('meta.last_page', 2)
        ->assertJsonPath('links.next', route('tasks.index', ['page' => 2]));
});

test('the task list json ignores an unknown status filter', function () {
    $user = User::factory()->create();
    Task::factory()->for($user)->create(['status' => 'pending']);
    Task::factory()->for($user)->create(['status' => 'completed']);

    $this->actingAs($user)->getJson('/tasks?status=bogus')->assertJsonCount(2, 'data');
});

test('a task json hides tasks owned by someone else', function () {
    $task = Task::factory()->create();

    $this->actingAs(User::factory()->create())->getJson('/tasks/'.$task->id)->assertNotFound();
});

test('guests get a json 401 from browsing endpoints', function (string $path) {
    $this->getJson($path)->assertUnauthorized();
})->with(['/tasks', '/dashboard', '/reports', '/profile']);

test('the report list json is scoped to the owner and exposes downloads only when complete', function () {
    $user = User::factory()->create();
    $completed = TaskReport::factory()->for($user)->create(['status' => 'completed', 'path' => 'reports/a.csv']);
    $pending = TaskReport::factory()->for($user)->create(['status' => 'pending']);
    TaskReport::factory()->create(['status' => 'completed']);

    $response = $this->actingAs($user)->getJson('/reports');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $pending->id)
        ->assertJsonPath('data.0.download_url', null)
        ->assertJsonPath('data.1.download_url', route('reports.download', $completed));
});

test('the dashboard json returns counts recent tasks and the latest report', function () {
    $user = User::factory()->create();
    Task::factory()->for($user)->count(6)->create(['status' => 'pending']);
    Task::factory()->for($user)->create(['status' => 'completed']);
    Task::factory()->create();
    TaskReport::factory()->for($user)->create(['status' => 'failed']);
    $latestReport = TaskReport::factory()->for($user)->create(['status' => 'pending']);

    $response = $this->actingAs($user)->getJson('/dashboard');

    $response->assertOk()
        ->assertJsonPath('data.counts', ['total' => 7, 'pending' => 6, 'completed' => 1])
        ->assertJsonCount(5, 'data.recent_tasks')
        ->assertJsonPath('data.latest_report.id', $latestReport->id);
});

test('the dashboard json has no latest report before one is requested', function () {
    $this->actingAs(User::factory()->create())->getJson('/dashboard')->assertJsonPath('data.latest_report', null);
});
