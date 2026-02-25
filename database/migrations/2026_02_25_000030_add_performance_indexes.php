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
        Schema::table('products', function (Blueprint $table): void {
            $table->index('sack_color');
            $table->index('created_at');
            $table->index(['category_id', 'sack_color']);
            $table->index(['category_id', 'created_at']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->index('order_number');
            $table->index(['order_number', 'name']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->index('role');
            $table->index(['role', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['role']);
            $table->dropIndex(['role', 'created_at']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropIndex(['order_number']);
            $table->dropIndex(['order_number', 'name']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['sack_color']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['category_id', 'sack_color']);
            $table->dropIndex(['category_id', 'created_at']);
        });
    }
};
