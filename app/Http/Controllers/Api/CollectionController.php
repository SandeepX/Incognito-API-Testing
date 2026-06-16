<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCollectionRequest;
use App\Http\Requests\UpdateCollectionRequest;
use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CollectionController extends Controller
{
    public function index(Request $request): ResourceCollection
    {
        $userWorkspaceIds = Auth::user()->workspaces()->pluck('workspaces.id');

        $query = Collection::with(['items.children' => function ($q) {
            $q->with('children')->orderBy('order');
        }])
            ->whereIn('workspace_id', $userWorkspaceIds)
            ->orderBy('name');

        if ($request->has('workspace_id')) {
            // Ensure user has access to this specific workspace
            if ($userWorkspaceIds->contains($request->workspace_id)) {
                $query->where('workspace_id', $request->workspace_id);
            } else {
                // Return empty collection if user doesn't have access
                return CollectionResource::collection(collect());
            }
        }

        return CollectionResource::collection($query->get());
    }

    public function store(StoreCollectionRequest $request)
    {
        // Ensure user has access to the workspace
        $workspaceId = $request->validated('workspace_id');
        if (!Auth::user()->workspaces()->where('workspaces.id', $workspaceId)->exists()) {
            abort(403, 'You do not have access to this workspace');
        }

        $collection = Collection::create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $workspaceId,
            'name' => $request->validated('name'),
        ]);

        return new CollectionResource($collection);
    }

    public function update(UpdateCollectionRequest $request, Collection $collection)
    {
        // Ensure user has access to the collection's workspace
        if (!Auth::user()->workspaces()->where('workspaces.id', $collection->workspace_id)->exists()) {
            abort(403);
        }

        $collection->update($request->validated());

        return new CollectionResource($collection);
    }

    public function destroy(Collection $collection)
    {
        // Ensure user has access to the collection's workspace
        if (!Auth::user()->workspaces()->where('workspaces.id', $collection->workspace_id)->exists()) {
            abort(403);
        }

        $collection->delete();

        return response()->json(['success' => true]);
    }
}
