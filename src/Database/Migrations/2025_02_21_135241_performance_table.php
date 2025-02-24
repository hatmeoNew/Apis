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
        // edit attribute_options
        Schema::table('attribute_options', function (Blueprint $table) {
            $table->string('admin_name',100)->change();
            $table->string('swatch_value',100)->change();
        });

        // edit attribute_option_translations
        Schema::table('attribute_option_translations', function (Blueprint $table) {
            $table->string('locale',10)->change();
            $table->string('label',100)->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
