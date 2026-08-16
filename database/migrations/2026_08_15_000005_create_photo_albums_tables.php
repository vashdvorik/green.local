<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_albums', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 191)->unique();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->string('cover_image', 255)->nullable();
            $table->json('title');
            $table->json('excerpt')->nullable();
            $table->timestamps();
        });

        Schema::create('photos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('photo_album_id')->constrained()->cascadeOnDelete();
            $table->string('path', 255);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['photo_album_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos');
        Schema::dropIfExists('photo_albums');
    }
};
