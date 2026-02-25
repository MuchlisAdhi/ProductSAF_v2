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
        if (! Schema::hasColumn('assets', 'thumbnail_path')) {
            Schema::table('assets', function (Blueprint $table): void {
                $table->string('thumbnail_path')->nullable()->after('system_path');
                $table->index('thumbnail_path');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('assets', 'thumbnail_path')) {
            Schema::table('assets', function (Blueprint $table): void {
                $table->dropIndex(['thumbnail_path']);
                $table->dropColumn('thumbnail_path');
            });
        }
    }
};
