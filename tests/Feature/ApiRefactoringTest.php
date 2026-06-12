<?php

namespace Tests\Feature;

use App\Models\Environment;
use App\Models\Workspace;
use App\Models\Collection;
use App\Models\CollectionItem;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiRefactoringTest extends TestCase
{
    use DatabaseMigrations;

    public function test_environment_resource_serialization(): void
    {
        $env = Environment::create([
            'id' => (string) Str::uuid(),
            'name' => 'Test Env',
            'variables' => [['key' => 'url', 'value' => 'https://test.com']],
        ]);

        $response = $this->getJson('/api/environments');
        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonStructure([
            '*' => ['id', 'name', 'variables', 'created_at', 'updated_at']
        ]);
    }

    public function test_store_environment_with_form_request(): void
    {
        $response = $this->postJson('/api/environments', [
            'name' => 'Production',
            'variables' => [['key' => 'api_key', 'value' => 'secret']],
        ]);

        $response->assertCreated();
        $response->assertJsonStructure(['id', 'name', 'variables']);
        $this->assertDatabaseCount('environments', 1);
    }

    public function test_update_environment_with_form_request(): void
    {
        $env = Environment::create([
            'id' => (string) Str::uuid(),
            'name' => 'Test Env',
            'variables' => [],
        ]);

        $response = $this->putJson("/api/environments/{$env->id}", [
            'name' => 'Updated Env',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['id', 'name', 'variables']);
        $this->assertDatabaseHas('environments', ['name' => 'Updated Env']);
    }

    public function test_workspace_resource_with_relations(): void
    {
        $workspace = Workspace::create([
            'id' => (string) Str::uuid(),
            'name' => 'My Workspace',
        ]);

        $response = $this->getJson('/api/workspaces');
        $response->assertOk();
        $response->assertJsonStructure([
            '*' => ['id', 'name', 'description', 'collections']
        ]);
    }

    public function test_collection_resource_serialization(): void
    {
        $workspace = Workspace::create([
            'id' => (string) Str::uuid(),
            'name' => 'My Workspace',
        ]);

        $collection = Collection::create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'name' => 'My Collection',
        ]);

        $response = $this->getJson('/api/collections');
        $response->assertOk();
        $response->assertJsonStructure([
            '*' => ['id', 'name', 'workspace_id', 'items']
        ]);
    }

    public function test_collection_item_resource_with_children(): void
    {
        $collection = Collection::create([
            'id' => (string) Str::uuid(),
            'name' => 'My Collection',
        ]);

        $item = CollectionItem::create([
            'id' => (string) Str::uuid(),
            'collection_id' => $collection->id,
            'type' => 'folder',
            'name' => 'API Requests',
            'order' => 1,
        ]);

        $response = $this->getJson("/api/collections/{$collection->id}/items");
        $response->assertOk();
        $response->assertJsonStructure([
            '*' => ['id', 'name', 'type', 'children']
        ]);
    }

    public function test_store_collection_item_with_form_request(): void
    {
        $collection = Collection::create([
            'id' => (string) Str::uuid(),
            'name' => 'My Collection',
        ]);

        $response = $this->postJson("/api/collections/{$collection->id}/items", [
            'collection_id' => $collection->id,
            'type' => 'request',
            'name' => 'Get Users',
            'method' => 'GET',
            'url' => 'https://api.example.com/users',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('collection_items', ['name' => 'Get Users']);
    }

    public function test_model_relationships(): void
    {
        $workspace = Workspace::create([
            'id' => (string) Str::uuid(),
            'name' => 'Test Workspace',
        ]);

        $collection = Collection::create([
            'id' => (string) Str::uuid(),
            'workspace_id' => $workspace->id,
            'name' => 'Test Collection',
        ]);

        $item = CollectionItem::create([
            'id' => (string) Str::uuid(),
            'collection_id' => $collection->id,
            'type' => 'request',
            'name' => 'Test Request',
            'method' => 'GET',
            'url' => 'https://test.com',
            'order' => 1,
        ]);

        $this->assertEquals($workspace->id, $collection->workspace->id);
        $this->assertEquals($collection->id, $item->collection->id);
        $this->assertTrue($item->isRequest());
        $this->assertFalse($item->isFolder());
    }
}
