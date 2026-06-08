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
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();

            // The full timeline as a JSON array of events
            // Each event: { id, title, description, date, sequence, location, type: sequential|parallel, parallel_group }
            $table->json('timeline')->nullable();

            // Raw sources the user submitted to seed this story
            // [{ type: text|url|prompt, content }]
            $table->json('sources')->nullable();

            // Status of the story generation pipeline
            $table->string('status')->default('pending');
            // pending → processing → ready → failed

            $table->string('period_start')->nullable();
            $table->string('period_end')->nullable();

            // User's current position in the timeline (sequence index)
            // Stored per story so multiple stories maintain independent positions
            $table->integer('current_sequence')->default(0);

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
