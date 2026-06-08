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
        Schema::create('spaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            // Which timeline event sequence this space unlocks at
            $table->integer('unlocks_at_sequence');

            // The historical date this space is anchored to
            $table->string('historical_date');

            // Agent IDs involved in this space conversation
            $table->json('agent_ids');

            // Prerecorded audio file URL (generated in background)
            $table->string('audio_url')->nullable();

            // Duration in seconds of the audio
            $table->integer('duration_seconds')->nullable();

            $table->string('status')->default('pending');
            // pending | generating | ready | failed

            $table->timestamps();

            $table->index(['story_id', 'unlocks_at_sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};
