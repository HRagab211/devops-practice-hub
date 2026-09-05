<?php

use App\Jobs\GenerateTaskReport;
use App\Mail\WelcomeMail;
use App\Models\SchedulerHeartbeat;
use App\Models\Task;
use App\Models\TaskReport;
use App\Models\User;
use App\Services\DashboardCounts;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

test('registration signs in the user and queues a welcome email', function () {
    Mail::fake();
    $this->post('/register', ['name' => 'Taylor', 'email' => 'taylor@example.com', 'password' => 'secret-pass', 'password_confirmation' => 'secret-pass'])->assertRedirect('/dashboard');
    $user = User::where('email', 'taylor@example.com')->firstOrFail();
    expect(Hash::check('secret-pass', $user->password))->toBeTrue();
    $this->assertAuthenticatedAs($user);
    Mail::assertQueued(WelcomeMail::class, fn ($mail) => $mail->hasTo($user->email));
});

test('invalid registration creates no user or welcome email', function () {
    Mail::fake();
    $this->post('/register', ['name' => 'Taylor', 'email' => 'bad', 'password' => 'tiny', 'password_confirmation' => 'other'])->assertSessionHasErrors(['email', 'password']);
    $this->assertDatabaseCount('users', 0);
    Mail::assertNothingQueued();
});

test('users can log in and log out', function () {
    $user = User::factory()->create();
    $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
    $this->post('/logout')->assertRedirect('/');
    $this->assertGuest();
});

test('login rejects bad credentials and throttles repeated attempts', function () {
    $user = User::factory()->create();
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
    }
    $this->postJson('/login', ['email' => $user->email, 'password' => 'wrong'])->assertTooManyRequests();
    $this->assertGuest();
});

test('password reset email contains a usable single use token', function () {
    Notification::fake();
    $user = User::factory()->create();
    $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');
    $token = null;
    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
        $token = $notification->token;

        return true;
    });
    $data = ['email' => $user->email, 'token' => $token, 'password' => 'replacement-pass', 'password_confirmation' => 'replacement-pass'];
    $this->post('/reset-password', $data)->assertRedirect('/login');
    expect(Hash::check('replacement-pass', $user->fresh()->password))->toBeTrue();
    $this->post('/reset-password', $data)->assertSessionHasErrors('email');
});

test('authentication pages render', function (string $path) {
    $this->get($path)->assertOk()->assertSee('DevOps Practice Hub');
})->with(['/login', '/register', '/forgot-password', '/reset-password/example?email=test@example.com']);

test('guests cannot access workspace routes', function (string $path) {
    $this->get($path)->assertRedirect('/login');
})->with(['/dashboard', '/tasks', '/tasks/create', '/profile', '/reports', '/lab']);

test('task operations reject another owner', function (string $method, string $suffix) {
    $task = Task::factory()->create();
    $this->actingAs(User::factory()->create())->call($method, '/tasks/'.$task->id.$suffix, ['title' => 'stolen', 'status' => 'completed'])->assertNotFound();
    expect($task->fresh()->title)->toBe($task->title);
})->with([['GET', ''], ['GET', '/edit'], ['PUT', ''], ['DELETE', '']]);

test('task changes invalidate only the owners cached dashboard counts', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $counts = app(DashboardCounts::class);
    expect($counts->forUser($user))->toBe(['total' => 0, 'pending' => 0, 'completed' => 0]);
    Cache::put(DashboardCounts::key($other->id), ['total' => 99], 60);
    $this->actingAs($user)->post('/tasks', ['title' => 'My task', 'status' => 'pending', 'user_id' => $other->id])->assertRedirect();
    $task = $user->tasks()->sole();
    expect($counts->forUser($user))->toBe(['total' => 1, 'pending' => 1, 'completed' => 0]);
    $this->put('/tasks/'.$task->id, ['title' => 'My task', 'status' => 'completed'])->assertRedirect();
    expect($counts->forUser($user))->toBe(['total' => 1, 'pending' => 0, 'completed' => 1]);
    $this->delete('/tasks/'.$task->id)->assertRedirect('/tasks');
    expect($counts->forUser($user))->toBe(['total' => 0, 'pending' => 0, 'completed' => 0]);
    expect(Cache::get(DashboardCounts::key($other->id)))->toBe(['total' => 99]);
});

test('tasks are filtered paginated escaped and scoped to the owner', function () {
    $user = User::factory()->create();
    Task::factory()->for($user)->count(13)->create(['status' => 'completed']);
    Task::factory()->for($user)->create(['title' => '<script>alert(1)</script>']);
    Task::factory()->create(['title' => 'Private task']);
    $this->actingAs($user)->get('/tasks?status=completed')->assertOk()->assertDontSee('Private task')->assertViewHas('tasks', fn ($tasks) => $tasks->total() === 13 && $tasks->count() === 12);
    $this->get('/tasks?status=pending')->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
});

test('invalid task input creates no records', function () {
    $this->actingAs(User::factory()->create())->post('/tasks', ['title' => '', 'status' => 'invalid', 'due_date' => 'tomorrow'])->assertSessionHasErrors(['title', 'status', 'due_date']);
    $this->assertDatabaseCount('tasks', 0);
});

test('profile supports image replacement removal and safe names', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $data = ['name' => 'New name', 'email' => $user->email, 'bio' => 'A short bio'];
    $this->actingAs($user)->patch('/profile', $data + ['avatar' => UploadedFile::fake()->image('photo.png')])->assertRedirect('/profile');
    $first = $user->fresh()->avatar_path;
    Storage::disk('public')->assertExists($first);
    expect($first)->not->toContain('photo.png');
    $this->patch('/profile', $data + ['avatar' => UploadedFile::fake()->image('second.jpg')])->assertRedirect('/profile');
    Storage::disk('public')->assertMissing($first);
    $second = $user->fresh()->avatar_path;
    Storage::disk('public')->assertExists($second);
    $this->patch('/profile', $data + ['remove_avatar' => 1])->assertRedirect('/profile');
    Storage::disk('public')->assertMissing($second);
    expect($user->fresh())->avatar_path->toBeNull()->bio->toBe('A short bio')->name->toBe('New name');
});

test('profile rejects unsafe image content formats and size', function (string $kind) {
    Storage::fake('public');
    $user = User::factory()->create();
    $file = match ($kind) {
        'fake' => UploadedFile::fake()->createWithContent('fake.jpg', '<?php echo "bad";'),
        'svg' => UploadedFile::fake()->createWithContent('image.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
        'gif' => UploadedFile::fake()->image('image.gif'),
        'large' => UploadedFile::fake()->image('large.png')->size(2049),
    };
    $this->actingAs($user)->patch('/profile', ['name' => $user->name, 'email' => $user->email, 'avatar' => $file])->assertSessionHasErrors('avatar');
    expect($user->fresh()->avatar_path)->toBeNull();
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
})->with(['fake', 'svg', 'gif', 'large']);

test('password changes require the current password', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->put('/profile/password', ['current_password' => 'wrong', 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertSessionHasErrors('current_password');
    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
    $this->put('/profile/password', ['current_password' => 'password', 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertRedirect('/profile');
    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

test('report requests dispatch the recorded owners report', function () {
    Queue::fake([GenerateTaskReport::class]);
    $user = User::factory()->create();
    $this->actingAs($user)->post('/reports', ['user_id' => 999])->assertRedirect('/reports');
    $report = $user->reports()->sole();
    expect($report->status)->toBe('pending');
    Queue::assertPushed(GenerateTaskReport::class, fn ($job) => $job->reportId === $report->id);
});

test('report execution exports only owned tasks neutralizes formulas and is idempotent', function () {
    Storage::fake('reports');
    $report = TaskReport::factory()->create();
    Task::factory()->create(['user_id' => $report->user_id, 'title' => ' =HYPERLINK("bad")', 'description' => "hello,\nworld"]);
    Task::factory()->create(['title' => 'Other owners secret']);
    $job = new GenerateTaskReport($report->id);
    $job->handle();
    $report->refresh();
    expect($report->status)->toBe('completed');
    $content = Storage::disk('reports')->get($report->path);
    expect($content)->toContain("' =HYPERLINK")->toContain('hello,')->not->toContain('Other owners secret');
    $job->handle();
    $this->assertDatabaseCount('task_reports', 1);
    expect(Storage::disk('reports')->allFiles())->toHaveCount(1);
    $this->actingAs($report->user)->get('/reports/'.$report->id.'/download')->assertOk()->assertDownload('task-report-'.$report->id.'.csv');
});

test('reports remain pending until a real database queue worker executes them', function () {
    Storage::fake('reports');
    config(['queue.default' => 'database']);
    $user = User::factory()->create();
    $this->actingAs($user)->post('/reports')->assertRedirect('/reports');
    $report = $user->reports()->sole();
    expect($report->status)->toBe('pending');
    $this->assertDatabaseCount('jobs', 1);
    $this->artisan('queue:work', ['connection' => 'database', '--once' => true, '--tries' => 3])->assertSuccessful();
    expect($report->fresh()->status)->toBe('completed');
    $this->assertDatabaseCount('jobs', 0);
});

test('failed jobs persist a safe terminal status', function () {
    $report = TaskReport::factory()->create();
    (new GenerateTaskReport($report->id))->failed(new RuntimeException('private error'));
    expect($report->fresh()->status)->toBe('failed');
    $this->actingAs($report->user)->get('/reports')->assertSee('This report failed')->assertDontSee('private error');
});

test('reports cannot be downloaded by another user or before completion', function () {
    $report = TaskReport::factory()->create();
    $this->actingAs(User::factory()->create())->get('/reports/'.$report->id.'/download')->assertNotFound();
    $this->get('/reports')->assertDontSee('Task report #'.$report->id);
    $this->actingAs($report->user)->get('/reports/'.$report->id.'/download')->assertNotFound();
});

test('readiness checks the database and cache without starting a session', function () {
    DB::select('SELECT 1');
    $this->get('/health/ready')->assertOk()->assertExactJson(['ready' => true])->assertCookieMissing(config('session.cookie'));
});

test('readiness fails safely when a dependency is unavailable', function (string $dependency) {
    if ($dependency === 'database') {
        DB::shouldReceive('connection')->once()->andThrow(new RuntimeException('secret credentials'));
    } else {
        Cache::shouldReceive('put')->once()->andThrow(new RuntimeException('secret credentials'));
    }
    $this->get('/health/ready')->assertStatus(503)->assertExactJson(['ready' => false]);
})->with(['database', 'cache']);

test('liveness does not check database or cache', function () {
    DB::shouldReceive('connection')->never();
    Cache::shouldReceive('get')->never();
    $this->get('/up')->assertOk();
});

test('heartbeat command updates one persisted record', function () {
    $this->travelTo(now()->startOfSecond());
    $this->artisan('lab:heartbeat')->assertSuccessful();
    $this->travel(2)->minutes();
    $this->artisan('lab:heartbeat')->assertSuccessful();
    $this->assertDatabaseCount('scheduler_heartbeats', 1);
    expect(SchedulerHeartbeat::find('scheduler')->last_ran_at->equalTo(now()))->toBeTrue();
});

test('workspace pages render persisted information and lab respects its flag', function () {
    config(['hub.lab_enabled' => true]);
    $user = User::factory()->create();
    $task = Task::factory()->for($user)->create();
    $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee($task->title);
    $this->get('/profile')->assertOk()->assertSee($user->email);
    $this->get('/tasks/create')->assertOk();
    $this->get('/tasks/'.$task->id)->assertOk()->assertSee($task->title);
    $this->get('/tasks/'.$task->id.'/edit')->assertOk();
    $this->get('/lab')->assertOk()->assertSee('No heartbeat recorded');
    config(['hub.lab_enabled' => false]);
    $this->get('/lab')->assertNotFound();
});

test('valid webp profile images are accepted', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $this->actingAs($user)->patch('/profile', [
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->image('photo.webp'),
    ])->assertRedirect('/profile');
    Storage::disk('public')->assertExists($user->fresh()->avatar_path);
});

test('profile validation preserves the existing image and email', function () {
    Storage::fake('public');
    $other = User::factory()->create();
    $user = User::factory()->create(['avatar_disk' => 'public', 'avatar_path' => 'avatars/original.png']);
    Storage::disk('public')->put('avatars/original.png', 'original');
    $this->actingAs($user)->patch('/profile', [
        'name' => $user->name,
        'email' => $other->email,
        'avatar' => UploadedFile::fake()->image('replacement.png'),
    ])->assertSessionHasErrors('email');
    expect($user->fresh()->email)->toBe($user->email);
    expect(Storage::disk('public')->allFiles())->toBe(['avatars/original.png']);
});

test('a report can recover after storage failure using the same record', function () {
    Storage::fake('reports');
    $report = TaskReport::factory()->create(['disk' => 'unconfigured']);
    $job = new GenerateTaskReport($report->id);
    expect(fn () => $job->handle())->toThrow(InvalidArgumentException::class);
    expect($report->fresh()->status)->toBe('processing');
    $report->update(['disk' => 'reports']);
    $job->handle();
    expect($report->fresh()->status)->toBe('completed');
    $this->assertDatabaseCount('task_reports', 1);
    Storage::disk('reports')->assertExists($report->fresh()->path);
});

test('welcome mail escapes the registered name', function () {
    (new WelcomeMail('<script>alert(1)</script>'))
        ->assertSeeInHtml('&lt;script&gt;alert(1)&lt;/script&gt;', false)
        ->assertDontSeeInHtml('<script>alert(1)</script>', false);
});
