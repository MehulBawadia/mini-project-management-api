<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * List all of the tasks of the provided task id.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index($projectId)
    {
        $project = Project::query()
            ->where('user_id', auth()->id())
            ->find($projectId);
        if (! $project) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project not found.',
                'data' => [],
            ], 404);
        }

        $dueDate = request()->due_date;
        $status = request()->status;
        $sort = request()->sort ?? 'desc';

        $tasks = Task::query()
            ->where('project_id', $projectId)
            ->when($dueDate, function ($query) use ($dueDate) {
                $query->where('due_date', $dueDate);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($sort === 'asc', function ($query) use ($status) {
                $query->oldest();
            })
            ->when($sort === 'desc', function ($query) use ($status) {
                $query->latest();
            })
            ->paginate();

        return TaskResource::collection($tasks);
    }

    public function show($projectId, $taskId)
    {
        $task = Task::query()
            ->where('project_id', $projectId)
            ->find($taskId);
        if (! $task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task not found.',
            ], 404);
        }

        if (request()->user()->cannot('view', $task)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot view the task.',
                'data' => [],
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Task fetched successfully.',
            'data' => new TaskResource($task),
        ]);
    }

    /**
     * Store the new projet details.
     *
     * @param \App\Http\Requests\Tasks\StoreTaskRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($projectId, StoreTaskRequest $request)
    {
        $project = Project::query()
            ->where('user_id', auth()->id())
            ->find($projectId);
        if (! $project) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project not found.',
                'data' => [],
            ], 404);
        }

        DB::beginTransaction();

        try {
            $task = Task::create([
                'project_id' => $projectId,
                'title' => $request->title,
                'status' => $request->status,
                'due_date' => $request->due_date,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Task was created successfully.',
                'data' => new TaskResource($task),
            ], 201);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            logger()->error($e->getTraceAsString());

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Could not create a task.',
                'data' => [],
            ], 500);
        }
    }

    /**
     * Update the task details of the given id.
     *
     * @param int $id
     * @param \App\Http\Requests\Tasks\UpdateTaskRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($projectId, $taskId, UpdateTaskRequest $request)
    {
        $task = Task::query()
            ->where('project_id', $projectId)
            ->find($taskId);
        if (! $task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task not found.',
            ], 404);
        }

        if ($request->user()->cannot('update', $task)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot update the task.',
                'data' => [],
            ], 403);
        }

        DB::beginTransaction();

        try {
            $task = $task->update([
                'title' => $request->title,
                'status' => $request->status,
                'due_date' => $request->due_date,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Task was updated successfully.',
                'data' => [],
            ]);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            logger()->error($e->getTraceAsString());

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Could not update a task.',
                'data' => [],
            ], 500);
        }
    }

    /**
     * Update the task details of the given id.
     *
     * @param int $id
     * @param \App\Http\Requests\Tasks\UpdateTaskRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatusToDone($projectId, $taskId)
    {
        $task = Task::query()
            ->where('project_id', $projectId)
            ->find($taskId);
        if (! $task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task not found.',
            ], 404);
        }

        if (request()->user()->cannot('update', $task)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot update the task.',
                'data' => [],
            ], 403);
        }

        DB::beginTransaction();

        try {
            $task = $task->update([
                'status' => 'done',
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Task was successfully marked as done.',
                'data' => [],
            ]);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            logger()->error($e->getTraceAsString());

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Could not mark the task as done.',
                'data' => [],
            ], 500);
        }
    }

    /**
     * Delete the task of the given id.
     *
     * @param int $id
     * @param \App\Http\Requests\Tasks\UpdateTaskRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($projectId, $taskId)
    {
        $task = Task::query()
            ->where('project_id', $projectId)
            ->find($taskId);
        if (! $task) {
            return response()->json([
                'status' => 'error',
                'message' => 'Task not found.',
            ], 404);
        }

        if (request()->user()->cannot('delete', $task)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot delete the task.',
                'data' => [],
            ], 403);
        }

        DB::beginTransaction();

        try {
            $task->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Task was deleted successfully.',
                'data' => [],
            ]);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            logger()->error($e->getTraceAsString());

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Could not delete a task.',
                'data' => [],
            ], 500);
        }
    }
}
