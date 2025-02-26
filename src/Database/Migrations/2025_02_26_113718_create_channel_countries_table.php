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
        Schema::create('channel_countries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('channel_id')->comment('The channel ID.');
            $table->unsignedBigInteger('country_id')->comment('The country ID.');
            $table->unique(['channel_id', 'country_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_countries');
    }
};
