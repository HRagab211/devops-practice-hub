<?php

namespace App\Policies;

use App\Models\TaskReport;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskReportPolicy
{
    public function view(User $user, TaskReport $record): Response
    {
        return $user->id === $record->user_id ? Response::allow() : Response::denyAsNotFound();
    }
}
