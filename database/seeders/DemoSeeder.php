<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo seeding is only allowed locally or in tests.');
        }
        $user = User::firstOrCreate(['email' => 'demo@example.com'], ['name' => 'Alex Morgan', 'password' => 'password', 'bio' => 'Learning by building.']);
        foreach (['Create my first task' => 'completed', 'Explore task reports' => 'in_progress', 'Plan the next practice session' => 'pending'] as $title => $status) {
            $user->tasks()->firstOrCreate(['title' => $title], ['status' => $status, 'description' => 'A small step toward a reliable application.', 'due_date' => now()->addDays(3)]);
        }
    }
}
