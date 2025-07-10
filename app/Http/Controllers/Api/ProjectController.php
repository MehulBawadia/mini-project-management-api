<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * List all of the projects that are added by the auth user.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $projects = Project::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate();

        return ProjectResource::collection($projects);
    }

    public function show($id)
    {
        $project = Project::query()
            ->where('user_id', auth()->id())
            ->find($id);
        if (! $project) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project not found.',
            ], 404);
        }

        if (request()->user()->cannot('view', $project)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot view the project.',
                'data' => [],
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Project fetched successfully.',
            'data' => new ProjectResource($project),
        ]);
    }

    /**
     * Store the new projet details.
     *
     * @param \App\Http\Requests\Projects\StoreProjectRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreProjectRequest $request)
    {
        DB::beginTransaction();

        try {
            $project = Project::create([
                'user_id' => auth()->id(),
                'name' => $request->name,
                'description' => $request->description,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Project was created successfully.',
                'data' => new ProjectResource($project),
            ], 201);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            logger()->error($e->getTraceAsString());

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Could not create a project.',
                'data' => [],
            ], 500);
        }
    }

    /**
     * Update the project details of the given id.
     *
     * @param int $id
     * @param \App\Http\Requests\Projects\UpdateProjectRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, UpdateProjectRequest $request)
    {
        $project = Project::query()
            ->where('user_id', auth()->id())
            ->find($id);
        if (! $project) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project not found.',
            ], 404);
        }

        if ($request->user()->cannot('update', $project)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot update the project.',
                'data' => [],
            ], 403);
        }

        DB::beginTransaction();

        try {
            $project = $project->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Project was updated successfully.',
            ]);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            logger()->error($e->getTraceAsString());

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Could not update a project.',
                'data' => [],
            ], 500);
        }
    }

    /**
     * Delete the project of the given id.
     *
     * @param int $id
     * @param \App\Http\Requests\Projects\UpdateProjectRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $project = Project::query()
            ->where('user_id', auth()->id())
            ->find($id);
        if (! $project) {
            return response()->json([
                'status' => 'error',
                'message' => 'Project not found.',
            ], 404);
        }

        if (request()->user()->cannot('delete', $project)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot delete the project.',
                'data' => [],
            ], 403);
        }

        DB::beginTransaction();

        try {
            $project->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Project was deleted successfully.',
                'data' => [],
            ]);
        } catch (\Exception $e) {
            logger()->error($e->getMessage());
            logger()->error($e->getTraceAsString());

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Could not delete a project.',
                'data' => [],
            ], 500);
        }
    }
}
