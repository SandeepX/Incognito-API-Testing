<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectionDocumentationController extends Controller
{
    public function show(Collection $collection)
    {
        $collection->load(['allItems' => function ($q) {
            $q->orderBy('order');
        }, 'workspace']);

        // Ensure user has access
        if (!Auth::user()->workspaces()->where('workspaces.id', $collection->workspace_id)->exists()) {
            abort(403);
        }

        // Build a tree from flat items
        $tree = $this->buildTree($collection->allItems);

        return view('apitester.docs', [
            'collection' => $collection,
            'tree' => $tree,
        ]);
    }

    private function buildTree($items, $parentId = null): array
    {
        $branch = [];

        foreach ($items as $item) {
            if ($item->parent_id === $parentId) {
                $children = $this->buildTree($items, $item->id);
                $node = [
                    'id' => $item->id,
                    'type' => $item->type,
                    'name' => $item->name,
                    'method' => $item->method,
                    'url' => $item->url,
                    'request_data' => $item->request_data,
                ];
                if (!empty($children)) {
                    $node['children'] = $children;
                }
                $branch[] = $node;
            }
        }

        return $branch;
    }
}
