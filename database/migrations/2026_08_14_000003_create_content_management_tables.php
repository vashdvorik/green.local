<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 191)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('color', 20);
            $table->timestamps();
        });

        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 191)->unique();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->string('cover_image', 255)->nullable();
            $table->json('title');
            $table->json('excerpt');
            $table->json('content');
            $table->timestamps();
        });

        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 191)->unique();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->date('application_deadline')->nullable();
            $table->string('cover_image', 255)->nullable();
            $table->foreignId('tag_id')->nullable()->constrained('tags')->nullOnDelete();
            $table->json('title');
            $table->json('excerpt');
            $table->json('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
        Schema::dropIfExists('news');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('site_settings');
    }
};
