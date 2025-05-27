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
        Schema::create('todo_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('todo_id')->constrained('todos')->onDelete('cascade');
            $table->string('meta_key');
            $table->text('meta_value')->nullable();
            $table->timestamps();
            
            // Add a unique index on todo_id and meta_key to prevent duplicate meta entries
            $table->unique(['todo_id', 'meta_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todo_meta');
    }
};
