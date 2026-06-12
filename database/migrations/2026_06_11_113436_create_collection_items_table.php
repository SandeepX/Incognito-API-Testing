<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('collection_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('parent_id')->nullable()->constrained('collection_items')->cascadeOnDelete();
            $table->enum('type', ['folder', 'request']);
            $table->string('name');
            $table->string('method')->nullable()->default('GET');
            $table->text('url')->nullable();
            $table->json('request_data')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['collection_id', 'parent_id']);
            $table->index(['parent_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_items');
    }
};