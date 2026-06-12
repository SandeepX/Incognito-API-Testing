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
            $table->uuid('collection_id');
            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('cascade');
            $table->uuid('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('collection_items')->onDelete('cascade');
            $table->enum('type', ['folder', 'request']);
            $table->string('name');
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['collection_id', 'parent_id']);
            $table->index(['parent_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_items');
    }
};