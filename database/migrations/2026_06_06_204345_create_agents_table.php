<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('person');
            // person | organisation | media | government

            $table->string('role')->nullable();
            $table->string('affiliation')->nullable();
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->text('system_prompt');
            $table->text('background')->nullable();

            $table->json('goals')->nullable();
            $table->json('concerns')->nullable();
            $table->json('tools')->nullable();
            // tools: [text, image]

            $table->string('avatar_url')->nullable();

            // Rolling memory — last 50 things this agent said or witnessed
            $table->json('memory')->nullable();

            $table->timestamps();

            $table->index('story_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
