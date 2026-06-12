<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkspaceRequest;
use App\Http\Requests\UpdateWorkspaceRequest;
use App\Http\Resources\WorkspaceResource;
use App\Models\Workspace;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    public function index(): ResourceCollection
    {
        return WorkspaceResource::collection(
            Workspace::with('collections')->orderBy('name')->get()
        );
    }

    public function store(StoreWorkspaceRequest $request)
    {
        $workspace = Workspace::create([
            'id' => (string) Str::uuid(),
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
        ]);

        return new WorkspaceResource($workspace);
    }

    public function update(UpdateWorkspaceRequest $request, Workspace $workspace)
    {
        $workspace->update($request->validated());

        return new WorkspaceResource($workspace);
    }

    public function destroy(Workspace $workspace)
    {
        $workspace->delete();

        return response()->json(['success' => true]);
    }
}
