<?php

namespace LakM\Comments;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use LakM\Comments\Livewire\CommentReplyList;
use LakM\Comments\Livewire\CreateCommentReplyForm;
use LakM\Comments\Livewire\CreateCommentForm;
use LakM\Comments\Livewire\CommentList;
use LakM\Comments\Livewire\ReactionsManager;
use LakM\Comments\Livewire\UpdateCommentForm;
use LakM\Comments\Livewire\UpdateCommentReplyForm;
use LakM\Comments\Livewire\UserList;
use LakM\Comments\Models\Reply;
use Livewire\Livewire;

class CommentServiceProvider extends ServiceProvider
{
    public const MANIFEST_PATH = __DIR__ . '/../public/build/manifest.json';

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
        Blade::componentNamespace('LakM\\Comments\\Views\\Components', 'comments');

        Livewire::component('comments-create-form', CreateCommentForm::class);
        Livewire::component('comments-update-form', UpdateCommentForm::class);
        Livewire::component('comments-list', CommentList::class);
        Livewire::component('comments-reactions-manager', ReactionsManager::class);
        Livewire::component('comments-reply-form', CreateCommentReplyForm::class);
        Livewire::component('comments-reply-list', CommentReplyList::class);
        Livewire::component('comments-reply-update-form', UpdateCommentReplyForm::class);
        Livewire::component('comments-user-list', UserList::class);
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
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/comments.php' => config_path('comments.php')
        ], 'comments-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/create_comments_table.php.stub' => $this->getMigrationFileName('create_comment_table.php'),
        ], 'comments-migrations');

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/lakm/laravel-comments')
        ], 'comments-assets');
    }

    protected function getStyleUrl(): string
    {
        $stylePath = $this->getManifestData()['resources/js/app.js']['css'][0];

        return asset("vendor/lakm/laravel-comments/build/{$stylePath}");
    }

    protected function getScriptUrl(): string
    {
        $scriptPath = $this->getManifestData()['resources/js/app.js']['file'];

        return asset("vendor/lakm/laravel-comments/build/{$scriptPath}");
    }

    protected function getManifestData(): array
    {
        return json_decode(file_get_contents(self::MANIFEST_PATH), true);
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     */
    protected function getMigrationFileName(string $migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make([$this->app->databasePath() . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR])
            ->flatMap(fn ($path) => $filesystem->glob($path . '*_' . $migrationFileName))
            ->push($this->app->databasePath() . "/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }
}
