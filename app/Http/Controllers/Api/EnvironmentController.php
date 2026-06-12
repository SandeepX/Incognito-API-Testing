<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnvironmentRequest;
use App\Http\Requests\UpdateEnvironmentRequest;
use App\Http\Resources\EnvironmentResource;
use App\Models\Environment;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;

class EnvironmentController extends Controller
{
    public function index(): ResourceCollection
    {
        return EnvironmentResource::collection(
            Environment::query()->orderBy('name')->get()
        );
    }

    public function store(StoreEnvironmentRequest $request)
    {
        $env = Environment::create([
            'id' => (string) Str::uuid(),
            'name' => $request->validated('name'),
            'variables' => $request->validated('variables') ?? [],
        ]);

        return new EnvironmentResource($env);
    }

    public function update(UpdateEnvironmentRequest $request, Environment $environment)
    {
        $environment->update($request->validated());

        return new EnvironmentResource($environment);
    }

    public function destroy(Environment $environment)
    {
        $environment->delete();

        return response()->json(['success' => true]);
    }
}
