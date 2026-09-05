<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardCounts
{
    public static function key(int $userId): string
    {
        return "dashboard:counts:{$userId}";
    }

    /** @return array{total:int,pending:int,completed:int} */
    public function forUser(User $user): array
    {
        return Cache::remember(self::key($user->id), 60, function () use ($user): array {
            $counts = $user->tasks()->selectRaw('status, COUNT(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');

            return ['total' => (int) $counts->sum(), 'pending' => (int) $counts->get('pending', 0), 'completed' => (int) $counts->get('completed', 0)];
        });
    }
}
