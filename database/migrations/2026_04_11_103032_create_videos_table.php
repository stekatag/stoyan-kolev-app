<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('bucket_video_key')->nullable();
            $table->string('local_video_path')->nullable();
            $table->string('bucket_thumbnail_key')->nullable();
            $table->string('local_thumbnail_path')->nullable();
            $table->string('video_storage_status')->default('missing');
            $table->string('thumbnail_storage_status')->default('missing');
            $table->string('source_type')->default('imported_asset');
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamps();

            $table->index(['category_id', 'is_visible', 'sort_order']);
            $table->index('source_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('videos');
    }
};
