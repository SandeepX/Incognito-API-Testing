<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workspace;
use App\Models\Collection;
use App\Models\CollectionItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $workspace = Workspace::create([
            'id' => (string) Str::uuid(),
            'name' => "Test User's Workspace",
            'description' => 'Default workspace for testing',
            'owner_id' => $user->id,
        ]);

        $workspace->users()->attach($user->id, ['role' => 'owner']);

        // Create a sample collection with requests for testing docs
        $collection = Collection::create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'name' => 'Pet Store API',
        ]);

        // Create a folder
        $folder = CollectionItem::create([
            'id' => (string) Str::uuid(),
            'collection_id' => $collection->id,
            'type' => 'folder',
            'name' => 'Pets',
            'order' => 1,
        ]);

        // Create requests inside the folder
        CollectionItem::create([
            'id' => (string) Str::uuid(),
            'collection_id' => $collection->id,
            'parent_id' => $folder->id,
            'type' => 'request',
            'name' => 'List all pets',
            'method' => 'GET',
            'url' => 'https://petstore.example.com/api/pets',
            'description' => 'Returns a list of all pets in the store. You can filter by status and control the number of results returned.',
            'examples' => [
                [
                    'name' => 'Success response',
                    'status' => 200,
                    'body' => '[{"id": 1,"name": "Buddy","species": "dog","age": 3,"status": "available"},{"id": 2,"name": "Whiskers","species": "cat","age": 5,"status": "available"}]',
                ],
                [
                    'name' => 'Empty results',
                    'status' => 200,
                    'body' => '[]',
                ],
            ],
            'request_data' => [
                'method' => 'GET',
                'url' => 'https://petstore.example.com/api/pets',
                'headers' => [
                    ['key' => 'Accept', 'value' => 'application/json'],
                ],
                'params' => [
                    ['key' => 'status', 'value' => 'available'],
                    ['key' => 'limit', 'value' => '20'],
                ],
                'bodyType' => 'none',
                'auth' => ['type' => 'bearer', 'bearer' => '{{api_token}}'],
            ],
            'order' => 1,
        ]);

        CollectionItem::create([
            'id' => (string) Str::uuid(),
            'collection_id' => $collection->id,
            'parent_id' => $folder->id,
            'type' => 'request',
            'name' => 'Add new pet',
            'method' => 'POST',
            'url' => 'https://petstore.example.com/api/pets',
            'description' => 'Add a new pet to the store. The request body must include the pet\'s name and species. Age is optional.',
            'examples' => [
                [
                    'name' => 'Created successfully',
                    'status' => 201,
                    'body' => '{"id": 1,"name": "Buddy","species": "dog","age": 3,"status": "available"}',
                ],
                [
                    'name' => 'Validation error',
                    'status' => 422,
                    'body' => '{"error": "Validation failed","messages": {"name": ["The name field is required"]}}',
                ],
            ],
            'request_data' => [
                'method' => 'POST',
                'url' => 'https://petstore.example.com/api/pets',
                'headers' => [
                    ['key' => 'Content-Type', 'value' => 'application/json'],
                ],
                'bodyType' => 'json',
                'body' => '{"name": "Buddy","species": "dog","age": 3}',
                'auth' => ['type' => 'bearer', 'bearer' => '{{api_token}}'],
            ],
            'order' => 2,
        ]);

        // Create a request at root level
        CollectionItem::create([
            'id' => (string) Str::uuid(),
            'collection_id' => $collection->id,
            'type' => 'request',
            'name' => 'Get store inventory',
            'method' => 'GET',
            'url' => 'https://petstore.example.com/api/store/inventory',
            'request_data' => [
                'method' => 'GET',
                'url' => 'https://petstore.example.com/api/store/inventory',
                'bodyType' => 'none',
                'auth' => ['type' => 'apikey', 'keyName' => 'X-API-Key', 'keyValue' => '{{api_key}}'],
            ],
            'order' => 2,
        ]);

        // Create another collection
        $collection2 = Collection::create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'name' => 'GitHub API',
        ]);

        CollectionItem::create([
            'id' => (string) Str::uuid(),
            'collection_id' => $collection2->id,
            'type' => 'request',
            'name' => 'Get user repos',
            'method' => 'GET',
            'url' => 'https://api.github.com/users/octocat/repos',
            'request_data' => [
                'method' => 'GET',
                'url' => 'https://api.github.com/users/octocat/repos',
                'headers' => [
                    ['key' => 'Accept', 'value' => 'application/vnd.github.v3+json'],
                ],
                'bodyType' => 'none',
                'auth' => ['type' => 'none'],
            ],
            'order' => 1,
        ]);

        CollectionItem::create([
            'id' => (string) Str::uuid(),
            'collection_id' => $collection2->id,
            'type' => 'request',
            'name' => 'Create a repository',
            'method' => 'POST',
            'url' => 'https://api.github.com/user/repos',
            'request_data' => [
                'method' => 'POST',
                'url' => 'https://api.github.com/user/repos',
                'headers' => [
                    ['key' => 'Accept', 'value' => 'application/vnd.github.v3+json'],
                    ['key' => 'Content-Type', 'value' => 'application/json'],
                ],
                'bodyType' => 'raw',
                'rawBody' => '{"name":"hello-world","description":"A test repo","private":false}',
                'auth' => ['type' => 'bearer', 'bearer' => 'github_pat_xxxx'],
            ],
            'order' => 2,
        ]);
    }
}
