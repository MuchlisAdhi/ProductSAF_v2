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
        Schema::create('tracker_visits', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('session_id')->nullable()->index();
            $table->string('visitor_hash', 64)->index();
            $table->string('user_id')->nullable()->index();
            $table->boolean('is_guest')->default(true)->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('method', 10)->default('GET');
            $table->string('path', 255)->index();
            $table->string('full_url', 2048)->nullable();
            $table->string('referer', 2048)->nullable();
            $table->string('user_agent', 1024)->nullable();
            $table->timestamp('visited_at')->index();
            $table->timestamp('created_at')->nullable();

            $table->index(['is_guest', 'visited_at']);
            $table->index(['path', 'visited_at']);
            $table->index(['user_id', 'visited_at']);
            $table->index(['visitor_hash', 'visited_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracker_visits');
    }
};
