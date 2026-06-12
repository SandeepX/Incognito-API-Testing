<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCollectionRequest;
use App\Http\Requests\UpdateCollectionRequest;
use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;

class CollectionController extends Controller
{
    public function index(Request $request): ResourceCollection
    {
        $query = Collection::with(['items.children' => function ($q) {
            $q->with('children')->orderBy('order');
        }])->orderBy('name');

        if ($request->has('workspace_id')) {
            $query->where('workspace_id', $request->workspace_id);
        }

        return CollectionResource::collection($query->get());
    }

    public function store(StoreCollectionRequest $request)
    {
        $collection = Collection::create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $request->validated('workspace_id'),
            'name' => $request->validated('name'),
        ]);

        return new CollectionResource($collection);
    }

    public function update(UpdateCollectionRequest $request, Collection $collection)
    {
        $collection->update($request->validated());

        return new CollectionResource($collection);
    }

    public function destroy(Collection $collection)
    {
        $collection->delete();

        return response()->json(['success' => true]);
    }
}
