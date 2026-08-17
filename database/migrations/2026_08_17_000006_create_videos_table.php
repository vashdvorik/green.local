<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table): void {
            $table->id();
            $table->json('title');
            $table->json('description');
            $table->text('youtube_url');
            $table->string('youtube_id', 20)->unique();
            $table->date('event_date');
            $table->string('cover_image', 255)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index('position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
