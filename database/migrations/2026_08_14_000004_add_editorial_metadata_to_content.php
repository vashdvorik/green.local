<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['news', 'opportunities'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->json('translation_meta')->nullable()->after('content');
                $table->string('author', 191)->nullable()->after('translation_meta');
                $table->string('seo_title', 191)->nullable()->after('author');
                $table->text('seo_description')->nullable()->after('seo_title');
            });
        }
    }

    public function down(): void
    {
        foreach (['news', 'opportunities'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn(['translation_meta', 'author', 'seo_title', 'seo_description']);
            });
        }
    }
};
