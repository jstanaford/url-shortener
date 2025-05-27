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
        Schema::create('short_url_views', function (Blueprint $table) {
            $table->id();
            $table->string('short_uri');
            $table->foreign('short_uri')->references('short_uri')->on('short_urls')->onDelete('cascade');
            $table->timestamp('time_visited');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_url_views');
    }
};
