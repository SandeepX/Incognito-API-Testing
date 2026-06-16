<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkspaceRequest;
use App\Http\Requests\UpdateWorkspaceRequest;
use App\Http\Resources\WorkspaceResource;
use App\Models\Workspace;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    public function index(): ResourceCollection
    {
        return WorkspaceResource::collection(
            Auth::user()->workspaces()->with('collections')->orderBy('name')->get()
        );
    }

    public function store(StoreWorkspaceRequest $request)
    {
        $workspace = Workspace::create([
            'id' => (string) Str::uuid(),
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'owner_id' => Auth::id(),
        ]);

        // Attach the creator as owner
        $workspace->users()->attach(Auth::id(), ['role' => 'owner']);

        return new WorkspaceResource($workspace);
    }

    public function update(UpdateWorkspaceRequest $request, Workspace $workspace)
    {
        // Ensure user belongs to this workspace
        if (!Auth::user()->workspaces()->where('workspace_id', $workspace->id)->exists()) {
            abort(403);
        }

        $workspace->update($request->validated());

        return new WorkspaceResource($workspace);
    }

    public function destroy(Workspace $workspace)
    {
        // Only owner can delete
        if ($workspace->owner_id !== Auth::id()) {
            abort(403);
        }

        $workspace->delete();

        return response()->json(['success' => true]);
    }
}
