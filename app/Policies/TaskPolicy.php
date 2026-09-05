<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    public function view(User $user, Task $record): Response
    {
        return $user->id === $record->user_id ? Response::allow() : Response::denyAsNotFound();
    }

    public function update(User $user, Task $record): Response
    {
        return $user->id === $record->user_id ? Response::allow() : Response::denyAsNotFound();
    }

    public function delete(User $user, Task $record): Response
    {
        return $user->id === $record->user_id ? Response::allow() : Response::denyAsNotFound();
    }
}
