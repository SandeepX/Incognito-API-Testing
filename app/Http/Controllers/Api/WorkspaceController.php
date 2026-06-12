<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    public function index()
    {
        return Workspace::with('collections')->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $ws = Workspace::create([
            'id' => (string) Str::uuid(),
            'name' => $validated['name'],
        ]);
        return response()->json($ws, 201);
    }

    public function update(Request $request, $id)
    {
        $ws = Workspace::findOrFail($id);
        $ws->update($request->validate(['name' => 'string|max:255']));
        return response()->json($ws);
    }

    public function destroy($id)
    {
        Workspace::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
