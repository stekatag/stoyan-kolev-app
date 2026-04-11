<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('homepage_hotspot_key')->nullable();
            $table->string('modal_preview_image_path')->nullable();
            $table->timestamps();

            $table->index(['is_visible', 'sort_order']);
            $table->index('homepage_hotspot_key');
        });

        DB::statement('create unique index categories_visible_hotspot_unique on categories (homepage_hotspot_key) where homepage_hotspot_key is not null and is_visible = true');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        DB::statement('drop index if exists categories_visible_hotspot_unique');
        Schema::dropIfExists('categories');
    }
};
