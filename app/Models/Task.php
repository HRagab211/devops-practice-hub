<?php

namespace App\Models;

use App\Services\DashboardCounts;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

#[Fillable(['title', 'description', 'status', 'due_date'])]
class Task extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function (Task $task): void {
            DB::afterCommit(fn () => Cache::forget(DashboardCounts::key($task->user_id)));
        });
        static::deleted(function (Task $task): void {
            DB::afterCommit(fn () => Cache::forget(DashboardCounts::key($task->user_id)));
        });
    }

    /** @return array<string,string> */
    protected function casts(): array
    {
        return ['due_date' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
