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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('primary');
            $table->string('secondary')->nullable();
            $table->string('name', 512)
                ->virtualAs("IF(`secondary` is null, `primary`, CONCAT(`primary`, ':', `secondary`))")
                ->index();
            $table->timestamps();
            $table->unique(['primary', 'secondary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
