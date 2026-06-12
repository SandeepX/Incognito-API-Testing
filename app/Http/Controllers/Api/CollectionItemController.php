<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCollectionItemRequest;
use App\Http\Requests\UpdateCollectionItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Collection;
use App\Models\CollectionItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;

class CollectionItemController extends Controller
{
    public function index($collectionId): ResourceCollection
    {
        $collection = Collection::with(['items.children' => function ($q) {
            $q->with('children')->orderBy('order');
        }])->findOrFail($collectionId);

        return ItemResource::collection($collection->items);
    }

    public function store(StoreCollectionItemRequest $request, $collectionId)
    {
        $data = $request->validated();

        $maxOrder =  CollectionItem::where('collection_id', $collectionId)
            ->where('parent_id', $data['parent_id'] ?? null)
            ->max('order') ?? 0;

        $item = CollectionItem::create([
            'id'            => (string) Str::uuid(),
            'collection_id' => $collectionId,
            'parent_id'     => $data['parent_id'] ?? null,
            'type'          => $data['type'],
            'name'          => $data['name'],
            'method'        => $data['request_data']['method'] ?? null,
            'url'           => $data['request_data']['url'] ?? null,
            'request_data'  => $data['request_data'] ?? null,
            'order'         => (int) $maxOrder + 1,
        ]);

        return new ItemResource($item);
    }

    public function update(UpdateCollectionItemRequest $request, CollectionItem $collectionItem)
    {
        $collectionItem->update($request->validated());

        return new ItemResource($collectionItem);
    }

    public function destroy(CollectionItem $collectionItem)
    {
        $collectionItem->delete();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, $collectionId)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|uuid',
            'items.*.parent_id' => 'nullable|uuid',
            'items.*.order' => 'required|integer',
        ]);

        foreach ($validated['items'] as $itemData) {
            CollectionItem::where('id', $itemData['id'])
                ->where('collection_id', $collectionId)
                ->update([
                    'parent_id' => $itemData['parent_id'],
                    'order' => $itemData['order'],
                ]);
        }

        return response()->json(['success' => true]);
    }
}
