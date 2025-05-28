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
        Schema::create('short_urls', function (Blueprint $table) {
            $table->id();
            $table->text('og_url');
            $table->string('short_uri', 6)->unique();
            $table->index('short_uri');
            $table->timestamps();
            
            // Add an index on the hash of the original URL to speed up lookups
            $table->string('og_url_hash', 64)->nullable();
            $table->index('og_url_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_urls');
    }
};
