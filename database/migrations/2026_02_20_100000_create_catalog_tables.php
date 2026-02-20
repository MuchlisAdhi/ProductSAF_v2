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
        Schema::create('auth_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id');
            $table->string('refresh_token_hash');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index('user_id');
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->string('icon');
            $table->integer('order_number')->default(0);
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('original_file_name');
            $table->string('system_path');
            $table->string('mime_type');
            $table->unsignedInteger('size');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description');
            $table->string('sack_color');
            $table->string('category_id');
            $table->string('image_id')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
            $table->foreign('image_id')->references('id')->on('assets')->nullOnDelete();
            $table->index('category_id');
            $table->index('image_id');
        });

        Schema::create('nutritions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('product_id');
            $table->string('label');
            $table->string('value');

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutritions');
        Schema::dropIfExists('products');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('auth_sessions');
    }
};
