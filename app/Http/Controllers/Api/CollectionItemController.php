<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\CollectionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CollectionItemController extends Controller
{
    public function index($collectionId)
    {
        $collection = Collection::with(['items.children' => function ($q) {
            $q->with('children')->orderBy('sort_order');
        }])->findOrFail($collectionId);

        return $collection->items;
    }

    public function store(Request $request, $collectionId)
    {
        $validated = $request->validate([
            'type' => 'required|in:folder,request',
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|uuid|exists:collection_items,id',
            'request_data' => 'nullable|array',
            'response_data' => 'nullable|array',
        ]);

        $maxOrder = CollectionItem::where('collection_id', $collectionId)
            ->where('parent_id', $validated['parent_id'] ?? null)
            ->max('sort_order') ?? 0;

        $item = CollectionItem::create([
            'id' => (string) Str::uuid(),
            'collection_id' => $collectionId,
            'parent_id' => $validated['parent_id'] ?? null,
            'type' => $validated['type'],
            'name' => $validated['name'],
            'request_data' => $validated['request_data'] ?? null,
            'response_data' => $validated['response_data'] ?? null,
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json($item, 201);
    }

    public function update(Request $request, $id)
    {
        $item = CollectionItem::findOrFail($id);
        $item->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'request_data' => 'sometimes|array',
            'response_data' => 'sometimes|array',
            'parent_id' => 'nullable|uuid|exists:collection_items,id',
            'sort_order' => 'sometimes|integer',
        ]));
        return response()->json($item);
    }

    public function destroy($id)
    {
        CollectionItem::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, $collectionId)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|uuid',
            'items.*.parent_id' => 'nullable|uuid',
            'items.*.sort_order' => 'required|integer',
        ]);

        foreach ($validated['items'] as $itemData) {
            CollectionItem::where('id', $itemData['id'])
                ->where('collection_id', $collectionId)
                ->update([
                    'parent_id' => $itemData['parent_id'],
                    'sort_order' => $itemData['sort_order'],
                ]);
        }

        return response()->json(['success' => true]);
    }
}
