<?php

namespace App\Http\Controllers;

use App\Actions\Task\CreateTaskAction;
use App\Actions\Task\UpdateTaskAction;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['pending', 'in_progress', 'completed'], true) ? $request->query('status') : null;

        return view('tasks.index', [
            'tasks' => $request->user()->tasks()
                ->when($status, fn ($query) => $query->where('status', $status))
                ->latest('updated_at')->orderByDesc('id')
                ->paginate(12)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('tasks.form', ['task' => new Task]);
    }

    public function store(TaskRequest $request, CreateTaskAction $createTaskAction): JsonResponse|RedirectResponse
    {
        $task = $createTaskAction->execute($request->validated());

        if ($request->wantsJson()) {
            session()->flash('status', 'Task created.');

            return TaskResource::make($task)
                ->additional(['message' => 'Task created.', 'redirect' => route('tasks.show', $task)])
                ->response()
                ->setStatusCode(201);
        }

        return to_route('tasks.show', $task)->with('status', 'Task created.');
    }

    public function show(Task $task): View
    {
        Gate::authorize('view', $task);

        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task): View
    {
        Gate::authorize('update', $task);

        return view('tasks.form', compact('task'));
    }

    public function update(TaskRequest $request, Task $task, UpdateTaskAction $updateTask): JsonResponse|RedirectResponse
    {
        $updateTask->execute($task, $request->validated());

        if ($request->wantsJson()) {
            session()->flash('status', 'Task updated.');

            return TaskResource::make($task)
                ->additional(['message' => 'Task updated.', 'redirect' => route('tasks.show', $task)])
                ->response();
        }

        return to_route('tasks.show', $task)->with('status', 'Task updated.');
    }

    public function destroy(Request $request, Task $task): JsonResponse|RedirectResponse
    {
        Gate::authorize('delete', $task);
        $task->delete();

        if ($request->wantsJson()) {
            session()->flash('status', 'Task deleted.');

            return response()->json(['message' => 'Task deleted.', 'redirect' => route('tasks.index')]);
        }

        return to_route('tasks.index')->with('status', 'Task deleted.');
    }
}
