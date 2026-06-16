<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnvironmentRequest;
use App\Http\Requests\UpdateEnvironmentRequest;
use App\Http\Resources\EnvironmentResource;
use App\Models\Environment;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EnvironmentController extends Controller
{
    public function index(): ResourceCollection
    {
        $userWorkspaceIds = Auth::user()->workspaces()->pluck('workspaces.id');

        return EnvironmentResource::collection(
            Environment::whereIn('workspace_id', $userWorkspaceIds)
                ->orWhereNull('workspace_id')
                ->orderBy('name')
                ->get()
        );
    }

    public function store(StoreEnvironmentRequest $request)
    {
        $env = Environment::create([
            'id' => (string) Str::uuid(),
            'name' => $request->validated('name'),
            'variables' => $request->validated('variables') ?? [],
            'workspace_id' => $request->validated('workspace_id'),
        ]);

        return new EnvironmentResource($env);
    }

    public function update(UpdateEnvironmentRequest $request, Environment $environment)
    {
        if ($environment->workspace_id && !Auth::user()->workspaces()->where('workspaces.id', $environment->workspace_id)->exists()) {
            abort(403);
        }

        $environment->update($request->validated());

        return new EnvironmentResource($environment);
    }

    public function destroy(Environment $environment)
    {
        if ($environment->workspace_id && !Auth::user()->workspaces()->where('workspaces.id', $environment->workspace_id)->exists()) {
            abort(403);
        }

        $environment->delete();

        return response()->json(['success' => true]);
    }
}
