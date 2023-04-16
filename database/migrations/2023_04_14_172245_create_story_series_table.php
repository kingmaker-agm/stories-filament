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
        Schema::create('story_series', function (Blueprint $table) {
            $table->id();
            $table->string('title', 1024);
            $table->string('original_url', 1024)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('story_series');
    }
};
