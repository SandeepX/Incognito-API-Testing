<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Collection::with(['items.children' => function ($q) {
            $q->with('children')->orderBy('sort_order');
        }])->orderBy('name');

        if ($request->has('workspace_id')) {
            $query->where('workspace_id', $request->workspace_id);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'workspace_id' => 'nullable|uuid|exists:workspaces,id',
        ]);
        $coll = Collection::create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $validated['workspace_id'] ?? null,
            'name' => $validated['name'],
        ]);
        return response()->json($coll, 201);
    }

    public function update(Request $request, $id)
    {
        $coll = Collection::findOrFail($id);
        $coll->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'workspace_id' => 'nullable|uuid|exists:workspaces,id',
        ]));
        return response()->json($coll);
    }

    public function destroy($id)
    {
        Collection::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
