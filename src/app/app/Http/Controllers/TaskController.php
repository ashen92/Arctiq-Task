<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Http\Requests\DeleteTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Http\Request;
use App\Http\Resources\TaskResource;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tasks = $request->user()->tasks()->latest()->paginate(10);
        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $task = $request->user()->tasks()->create($request->validated());

        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return response()->json($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task->update($request->validated());

        return response()->json($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteTaskRequest $request, Task $task)
    {
        $task->delete();

        return response()->json(null, 204);
    }

    public function filter(Request $request, $status)
    {
        if ($status !== 'all' && !TaskStatus::tryFrom($status)) {
            return response()->json(['message' => 'Invalid status'], 400);
        }

        $query = $request->user()->tasks();

        if ($status !== 'all') {
            $query->where('status', TaskStatus::from($status));
        }

        $tasks = $query->latest()->paginate(10);
        return TaskResource::collection($tasks);
    }
}