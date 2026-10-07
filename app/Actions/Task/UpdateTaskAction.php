<?php

namespace App\Actions\Task;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class UpdateTaskAction
{
    public function execute(Task $task, array $data): Task
    {
        if (! Auth::user() && $task->isBelongToCurrentUser()) {
            abort(401);
        }

        $task->update($data);

        return $task;
    }
}
