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
use LakM\Comments\Livewire\Comments\CreateForm;
use LakM\Comments\Livewire\Comments\ItemView;
use LakM\Comments\Livewire\Comments\ListView;
use LakM\Comments\Livewire\Comments\UpdateForm;
use LakM\Comments\Livewire\Editor;
use LakM\Comments\Livewire\ReactionManager;
use LakM\Comments\Livewire\Replies\ListView as RepliesListView;
use LakM\Comments\Livewire\Replies\ReplyForm;
use LakM\Comments\Livewire\Replies\ItemView as ReplyItemView;
use LakM\Comments\Livewire\Replies\UpdateForm as ReplyUpdateForm;
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

        Livewire::component('comments.create-form', CreateForm::class);
        Livewire::component('comments.update-form', UpdateForm::class);
        Livewire::component('comments.list-view', ListView::class);
        Livewire::component('comments.item-view', ItemView::class);

        Livewire::component('replies.create-form', ReplyForm::class);
        Livewire::component('replies.list-view', RepliesListView::class);
        Livewire::component('replies.update-form', ReplyUpdateForm::class);
        Livewire::component('replies.item-view', ReplyItemView::class);

        Livewire::component('user-list', UserList::class);
        Livewire::component('reaction-manager', ReactionManager::class);
    }

    protected function setBladeDirectives(): void
    {
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
