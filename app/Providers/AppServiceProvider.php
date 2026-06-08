<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            \App\Services\ImageService::class,
            fn() => new \App\Services\ImageService()
        );

        $this->app->singleton(
            \App\Services\VoiceAssignmentService::class,
            fn() => new \App\Services\VoiceAssignmentService()
        );

        $this->app->singleton(
            \App\Services\ResearchService::class,
            fn() => new \App\Services\ResearchService()
        );

        $this->app->singleton(
            \App\Services\IngestionService::class,
            fn($app) => new \App\Services\IngestionService(
                $app->make(\App\Services\VoiceAssignmentService::class)
            )
        );

        $this->app->singleton(
            \App\Services\PostGeneratorService::class,
            fn($app) => new \App\Services\PostGeneratorService(
                $app->make(\App\Services\ImageService::class)
            )
        );

        $this->app->singleton(
            \App\Services\SpaceAudioService::class,
            fn($app) => new \App\Services\SpaceAudioService(
                $app->make(\App\Services\VoiceAssignmentService::class)
            )
        );
    }

    public function boot(): void
    {
        // Force HTTPS on Render — Render terminates TLS at the load balancer
        if (env('APP_ENV') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
