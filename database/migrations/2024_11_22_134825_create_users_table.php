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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('vk_id')->unique();
            $table->string('name');
            $table->unsignedBigInteger('score')->default(0);
            $table->boolean('abuser')->default(false);
            $table->boolean('active')->default(true);
            $table->foreignId('app_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('reasons')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
