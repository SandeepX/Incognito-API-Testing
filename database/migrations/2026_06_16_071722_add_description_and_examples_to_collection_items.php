<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collection_items', function (Blueprint $table) {
            $table->text('description')->nullable()->after('order');
            $table->json('examples')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('collection_items', function (Blueprint $table) {
            $table->dropColumn(['description', 'examples']);
        });
    }
};
