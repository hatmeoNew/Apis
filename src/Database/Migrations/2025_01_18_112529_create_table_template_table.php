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
        // check the site_config table exists
        if (!Schema::hasTable('site_config')) {
            
            Schema::create('site_config', function (Blueprint $table) {
                $table->id();
                $table->integer('template_id')->default(0)->comment('id');
                $table->string('site_log')->nullable()->comment('Logo');
                $table->string('site_ico')->nullable()->comment('ico');
                $table->text('home_banner')->nullable()->comment('home banner');
                $table->text('recommend')->nullable()->comment('recommend');
                $table->string('template_banner')->nullable()->comment('template banner');
                $table->timestamps();
            });

            if (!Schema::hasColumn('site_config', 'template_banner')) {
                Schema::table('site_config', function (Blueprint $table) {
                    $table->string('template_banner')->nullable()->after('recommend')->comment('banner');
                });
            }
        }else{
            if (!Schema::hasColumn('site_config', 'template_banner')) {
                Schema::table('site_config', function (Blueprint $table) {
                    $table->string('template_banner')->nullable()->after('recommend')->comment('banner');
                });
            }
        }

        // add template table
        if (!Schema::hasTable('template')) {

            Schema::create('template', function (Blueprint $table) {
                $table->id();
                $table->string('template_name')->comment('template name');
                $table->string('template_banner')->nullable()->comment('template banner');
                $table->string('des')->nullable()->comment('description');
                $table->string('template_link')->nullable()->comment('template link');
                $table->text('template_countent')->nullable()->comment('template content');
                $table->timestamps();
            });

            if (!Schema::hasColumn('template', 'template_banner')) {
                Schema::table('template', function (Blueprint $table) {
                    $table->string('template_banner')->nullable()->comment('banner');
                });
            }

        }else{
            if (!Schema::hasColumn('template', 'template_banner')) {
                Schema::table('template', function (Blueprint $table) {
                    $table->string('template_banner')->nullable()->comment('banner');
                });
            }
        }

        
        

        // Schema::table('site_config', function (Blueprint $table) {
        //     $table->string('template_banner')->nullable()->after('recommend')->comment('推荐banner');
        // });
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
