<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Environment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EnvironmentController extends Controller
{
    public function index()
    {
        return Environment::query()
            ->orderBy('name')
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'variables' => 'nullable|array',
        ]);

        $env = Environment::create([
            'id' => (string) Str::uuid(),
            'name' => $validated['name'],
            'variables' => $validated['variables'] ?? [],
        ]);
        return response()->json($env, 201);
    }

    public function update(Request $request, $id)
    {
        $env = Environment::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'variables' => 'sometimes|array',
        ]);
        $env->update($validated);
        return response()->json($env);
    }

    public function destroy($id)
    {
        Environment::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
