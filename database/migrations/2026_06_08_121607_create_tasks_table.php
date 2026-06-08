<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50); // ingest_timeline, create_agent, schedule_events, fire_event, etc.
            $table->json('payload')->nullable(); // agent_index, event_sequence, etc.
            $table->string('status', 20)->default('pending'); // pending, running, done, failed
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('story_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};