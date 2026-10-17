<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('source');
            $table->string('external_id');
            $table->string('title');
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('cover_url')->nullable();
            $table->string('creator')->nullable();
            $table->timestamps();

            $table->unique(['source', 'type', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_items');
    }
};
