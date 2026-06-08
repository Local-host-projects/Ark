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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();

            // Which event in the timeline this post belongs to (event id from the JSON timeline)
            $table->integer('timeline_event_sequence');

            // Reply chain — null means this is a standalone post
            $table->foreignId('parent_post_id')
                  ->nullable()
                  ->constrained('posts')
                  ->nullOnDelete();

            $table->string('status')->default('standalone');
            // standalone | reply

            $table->text('content');
            $table->string('content_type')->default('text');
            // text | image | mixed

            // Media attachments [{ type, url, caption }]
            $table->json('media')->nullable();

            $table->string('historical_date');

            // Global feed order within the story
            $table->integer('sequence');

            $table->string('location_name')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['story_id', 'sequence']);
            $table->index(['story_id', 'timeline_event_sequence']);
            $table->index(['agent_id', 'story_id']);
            $table->index('parent_post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
