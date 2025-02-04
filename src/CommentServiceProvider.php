<?php

namespace LakM\Comments;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use LakM\Comments\Abstracts\AbstractQueries;
use LakM\Comments\Console\InstallCommand;
use LakM\Comments\Livewire\CommentItem;
use LakM\Comments\Livewire\CommentList;
use LakM\Comments\Livewire\CommentReplyItem;
use LakM\Comments\Livewire\CommentReplyList;
use LakM\Comments\Livewire\CreateCommentForm;
use LakM\Comments\Livewire\CreateCommentReplyForm;
use LakM\Comments\Livewire\Editor;
use LakM\Comments\Livewire\ReactionsManager;
use LakM\Comments\Livewire\UpdateCommentForm;
use LakM\Comments\Livewire\UpdateCommentReplyForm;
use LakM\Comments\Livewire\UserList;
use LakM\Comments\Models\Guest;
use Livewire\Livewire;

class CommentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->setRoutes();
        $this->setViews();
        $this->setComponents();
        $this->setBladeDirectives();
        $this->setGates();
        $this->registerGuards();
        $this->registerCommands();

        $this->configPublishing();
        $this->configBindings();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/comments.php', 'comments');
    }

    public function setRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    protected function setViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'comments');
    }

    protected function setComponents(): void
    {
        Blade::componentNamespace('LakM\\Comments\\Views\\Components', 'comments');

        Livewire::component('comments-editor', Editor::class);

        Livewire::component('comments-create-form', CreateCommentForm::class);
        Livewire::component('comments-update-form', UpdateCommentForm::class);
        Livewire::component('comments-list', CommentList::class);
        Livewire::component('comments-reactions-manager', ReactionsManager::class);
        Livewire::component('comments-reply-form', CreateCommentReplyForm::class);
        Livewire::component('comments-reply-list', CommentReplyList::class);
        Livewire::component('comments-reply-update-form', UpdateCommentReplyForm::class);
        Livewire::component('comments-user-list', UserList::class);
        Livewire::component('comments-item', CommentItem::class);
        Livewire::component('comments-reply-item', CommentReplyItem::class);
    }

    protected function setBladeDirectives(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        if (!(file_exists(public_path('vendor/lakm/laravel-comments/build/manifest.json')) ||
            file_exists(public_path('vendor/lakm/laravel-comments/laravel-comments.hot')))) {
            return;
        }

        $styles = Vite::useBuildDirectory("vendor/lakm/laravel-comments/build")
            ->useHotFile('vendor/lakm/laravel-comments/laravel-comments.hot')
            ->withEntryPoints(['resources/css/app.css'])
            ->toHtml();

        $scripts = Vite::useBuildDirectory("vendor/lakm/laravel-comments/build")
            ->useHotFile('vendor/lakm/laravel-comments/laravel-comments.hot')
            ->withEntryPoints(['resources/js/app.js'])
            ->toHtml();

        Vite::useHotFile(public_path('/hot'))
            ->useBuildDirectory('build');

        Blade::directive('commentsStyles', function () use ($styles) {
            return $styles;
        });

        Blade::directive('commentsScripts', function () use ($scripts) {
            return $scripts;
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
            __DIR__ . '/../database/migrations/create_comments_table.php.stub' => $this->getMigrationFileName('create_comments_table.php'),
            __DIR__ . '/../database/migrations/create_reactions_table.php.stub' => $this->getMigrationFileName('create_reactions_table.php'),
            __DIR__ . '/../database/migrations/create_guests_table.php.stub' => $this->getMigrationFileName('create_guests_table.php'),
            __DIR__ . '/../database/migrations/drop_guest_columns_from_comments_table.php.stub' => $this->getMigrationFileName('drop_guest_columns_from_comments_table.php'),
        ], 'comments-migrations');

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/lakm/laravel-comments')
        ], 'comments-assets');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/comments'),
        ], 'comments-views');
    }

    public function configBindings(): void
    {
        $this->app->bind(AbstractQueries::class, Queries::class);
    }

    public function registerGuards(): void
    {
        config()->set('auth.guards.guest', [
            'driver' => 'session',
            'provider' => 'guests',
        ]);

        config()->set('auth.providers.guests', [
            'driver' => 'eloquent',
            'model' => Guest::class,
        ]);
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
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
