<?php

namespace LakM\Comments;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class CommentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->setViews();
        $this->setComponents();
        $this->setBladeDirectives();
        $this->setGates();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/comments.php', 'comments');

        $this->configPublishing();
    }

    protected function setViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'comments');
    }

    protected function setComponents(): void
    {
        Livewire::component('comments', CommentForm::class);
    }

    protected function setBladeDirectives(): void
    {
        Blade::directive('commentsStyles', function () {
            $url = $this->getStyleUrl();
            return "<link rel='stylesheet' href='{$url}'>";
        });

        Blade::directive('commentsScripts', function () {
            $url = $this->getScriptUrl();
            return "<script type='module' src='{$url}'> </script>";
        });
    }

    protected function setGates(): void
    {
        foreach (config('comments.permissions') as $name => $callback) {
            Gate::define($name, $callback);
        }
    }

    protected function configPublishing(): void
    {
       if ($this->app->runningInConsole()) {
           return;
       }

       $this->publishes([
           __DIR__ . '/../config/comments.php' => config_path('comments.php')
       ], 'comments-config');

        $this->publishes([
            __DIR__.'/../database/migrations/create_comments_table.php.stub' => $this->getMigrationFileName('create_comment_table.php'),
        ], 'comments-migrations');

    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     */
    protected function getMigrationFileName(string $migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make([$this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR])
            ->flatMap(fn ($path) => $filesystem->glob($path.'*_'.$migrationFileName))
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }
}