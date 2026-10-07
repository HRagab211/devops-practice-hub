<?php

namespace App\Actions\Task;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class CreateTaskAction
{
    public function execute(array $data): Task
    {
        if (! Auth::user()) {
            abort(401);
        }

        return Auth::user()->tasks()->create($data);
    }
}
