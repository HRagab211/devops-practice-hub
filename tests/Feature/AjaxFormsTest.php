<?php

use App\Jobs\GenerateTaskReport;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;

uses(LazilyRefreshDatabase::class);

test('ajax task creation returns the created task and where to go next', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/tasks', ['title' => 'Ship it', 'status' => 'pending', 'due_date' => '2026-12-01']);

    $task = $user->tasks()->sole();
    $response->assertCreated()
        ->assertJsonPath('message', 'Task created.')
        ->assertJsonPath('redirect', route('tasks.show', $task))
        ->assertJsonPath('data.id', $task->id)
        ->assertJsonPath('data.title', 'Ship it')
        ->assertJsonPath('data.due_date', '2026-12-01')
        ->assertSessionHas('status', 'Task created.');
});

test('ajax task creation returns validation errors and creates nothing', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/tasks', ['title' => '', 'status' => 'invalid', 'due_date' => 'tomorrow']);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'title' => 'The title field is required.',
            'status' => 'The selected status is invalid.',
            'due_date' => 'The due date field must match the format Y-m-d.',
        ]);
    $this->assertDatabaseCount('tasks', 0);
});

test('ajax task update and delete respond with json redirects', function () {
    $user = User::factory()->create();
    $task = Task::factory()->for($user)->create(['status' => 'pending']);

    $this->actingAs($user)->putJson('/tasks/'.$task->id, ['title' => 'Renamed', 'status' => 'completed'])
        ->assertOk()
        ->assertJsonPath('data.title', 'Renamed')
        ->assertJsonPath('redirect', route('tasks.show', $task));
    expect($task->fresh()->status)->toBe('completed');

    $this->deleteJson('/tasks/'.$task->id)
        ->assertOk()
        ->assertJson(['message' => 'Task deleted.', 'redirect' => route('tasks.index')]);
    $this->assertModelMissing($task);
});

test('ajax task changes reject another owner', function () {
    $task = Task::factory()->create(['title' => 'Original']);

    $this->actingAs(User::factory()->create())->putJson('/tasks/'.$task->id, ['title' => 'stolen', 'status' => 'completed'])->assertNotFound();

    expect($task->fresh()->title)->toBe('Original');
});

test('ajax profile update returns a redirect back to the profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/profile', ['name' => 'New Name', 'email' => $user->email]);

    $response->assertOk()->assertJson(['message' => 'Profile updated.', 'redirect' => route('profile.edit')]);
    expect($user->fresh()->name)->toBe('New Name');
});

test('ajax password change validates the current password and stays on the page', function () {
    $user = User::factory()->create();
    $payload = ['password' => 'new-password', 'password_confirmation' => 'new-password'];

    $this->actingAs($user)->putJson('/profile/password', $payload + ['current_password' => 'wrong'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['current_password' => 'The password is incorrect.']);
    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();

    $this->putJson('/profile/password', $payload + ['current_password' => 'password'])
        ->assertOk()
        ->assertExactJson(['message' => 'Password changed.']);
    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

test('ajax report requests return the pending report', function () {
    Queue::fake([GenerateTaskReport::class]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/reports');

    $report = $user->reports()->sole();
    $response->assertAccepted()
        ->assertJsonPath('data.id', $report->id)
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.download_url', null);
    Queue::assertPushed(GenerateTaskReport::class, fn ($job) => $job->reportId === $report->id);
});

test('ajax report requests report a queue failure', function () {
    config(['queue.default' => 'missing-connection']);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/reports');

    $response->assertServiceUnavailable()->assertExactJson(['message' => 'Your report could not be queued. Please try again.']);
    expect($user->reports()->sole()->status)->toBe('failed');
});

test('ajax login returns the intended url', function () {
    $user = User::factory()->create();
    $this->get('/tasks')->assertRedirect('/login');

    $response = $this->postJson('/login', ['email' => $user->email, 'password' => 'password']);

    $response->assertOk()->assertJsonPath('redirect', url('/tasks'));
    $this->assertAuthenticatedAs($user);
});
