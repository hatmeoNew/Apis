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
        Schema::table('site_config', function (Blueprint $table) {
            $table->string('template_banner')->nullable()->after('recommend')->comment('推荐banner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_config', function (Blueprint $table) {
            $table->dropColumn('template_banner');
        });
    }
};
