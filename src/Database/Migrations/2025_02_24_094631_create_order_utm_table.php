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
        Schema::create('order_utm', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->comment('The order ID.');
            $table->string('utm_source')->nullable()->comment('The referrer: google, facebook, etc.');
            $table->string('utm_medium')->nullable()->comment('The marketing medium: cpc, banner, email, etc.');
            $table->string('utm_campaign')->nullable()->comment('The individual campaign name, slogan, promo code, etc.');
            $table->string('utm_term')->nullable()->comment('The keywords used in the referrer.');
            $table->string('utm_content')->nullable()->comment('The content of the ad.');
            $table->string('gclid')->nullable()->comment('Google Click ID.');
            $table->string('yclid')->nullable()->comment('Yandex Click ID.');
            $table->string('msclkid')->nullable()->comment('Microsoft Click ID.');
            $table->string('fbclid')->nullable()->comment('Facebook Click ID.');
            $table->string('dclid')->nullable()->comment('DoubleClick Click ID.');
            $table->string('mcid')->nullable()->comment('Mailchimp Click ID.');
            $table->string('gclsrc')->nullable()->comment('Google Click Source.');
            $table->string('utmcsr')->nullable()->comment('The referrer: google, facebook, etc.');
            $table->string('utmccn')->nullable()->comment('The individual campaign name, slogan, promo code, etc.');
            $table->timestamps();

            $table->unique('order_id');

            $table->index('utm_source');
            $table->index('utm_medium');
            $table->index('utm_campaign');
            $table->index('utm_term');
            $table->index('utm_content');
            $table->index('gclid');
            $table->index('yclid');
            $table->index('msclkid');
            $table->index('fbclid');
            $table->index('dclid');
            $table->index('mcid');
            $table->index('gclsrc');
            $table->index('utmcsr');
            $table->index('utmccn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_utm');
    }
};
