<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photo_albums', function (Blueprint $table): void {
            $table->json('photo_content')->nullable()->after('content');
        });

        DB::table('photo_albums')
            ->select(['id', 'content'])
            ->whereNull('photo_content')
            ->orderBy('id')
            ->get()
            ->each(function (object $album): void {
                $content = is_string($album->content)
                    ? json_decode($album->content, true)
                    : $album->content;
                $photoContent = is_array($content) ? data_get($content, 'ru', []) : [];

                if (is_array($photoContent) && count($photoContent)) {
                    DB::table('photo_albums')
                        ->where('id', $album->id)
                        ->update(['photo_content' => json_encode(array_values($photoContent), JSON_UNESCAPED_UNICODE)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('photo_albums', function (Blueprint $table): void {
            $table->dropColumn('photo_content');
        });
    }
};
