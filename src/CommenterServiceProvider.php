<?php

namespace LakM\Commenter;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use LakM\Commenter\Abstracts\AbstractQueries;
use LakM\Commenter\Console\InstallCommand;
use LakM\Commenter\Livewire\Comments\CreateForm;
use LakM\Commenter\Livewire\Comments\ItemView;
use LakM\Commenter\Livewire\Comments\ListView;
use LakM\Commenter\Livewire\Comments\UpdateForm;
use LakM\Commenter\Livewire\Editor;
use LakM\Commenter\Livewire\PinMessageHandler;
use LakM\Commenter\Livewire\ReactionManager;
use LakM\Commenter\Livewire\Replies\ItemView as ReplyItemView;
use LakM\Commenter\Livewire\Replies\ListView as RepliesListView;
use LakM\Commenter\Livewire\Replies\ReplyForm;
use LakM\Commenter\Livewire\Replies\UpdateForm as ReplyUpdateForm;
use LakM\Commenter\Livewire\UserList;
use LakM\Commenter\Models\Guest;
use Livewire\Livewire;

class CommenterServiceProvider extends ServiceProvider
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
        $this->mergeConfigFrom(__DIR__ . '/../config/commenter.php', 'commenter');
    }

    public function setRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    protected function setViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'commenter');
    }

    protected function setComponents(): void
    {
        Blade::componentNamespace('LakM\\Commenter\\Views\\Components', 'commenter');

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
        Livewire::component('pin-message', PinMessageHandler::class);
    }

    protected function setBladeDirectives(): void
    {
        if (!(file_exists(public_path('vendor/lakm/commenter/build/manifest.json')) ||
            file_exists(public_path('vendor/lakm/commenter/commenter.hot')))) {
            return;
        }

        $styles = Vite::useBuildDirectory("vendor/lakm/commenter/build")
            ->useHotFile('vendor/lakm/commenter/commenter.hot')
            ->withEntryPoints(['resources/css/app.css'])
            ->toHtml();

        $scripts = Vite::useBuildDirectory("vendor/lakm/commenter/build")
            ->useHotFile('vendor/lakm/commenter/commenter.hot')
            ->withEntryPoints(['resources/js/app.js'])
            ->toHtml();

        Vite::useHotFile(public_path('/hot'))
            ->useBuildDirectory('build');

        Blade::directive('commenterStyles', function () use ($styles) {
            return $styles;
        });

        Blade::directive('commenterScripts', function () use ($scripts) {
            return $scripts;
        });
    }

    protected function setGates(): void
    {
        foreach (config('commenter.permissions') as $name => $callback) {
            Gate::define($name, $callback);
        }
    }

    protected function configPublishing(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/commenter.php' => config_path('commenter.php')
        ], 'commenter-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/create_comments_table.php.stub' => $this->getMigrationFileName('create_comments_table.php'),
            __DIR__ . '/../database/migrations/create_reactions_table.php.stub' => $this->getMigrationFileName('create_reactions_table.php'),
            __DIR__ . '/../database/migrations/create_guests_table.php.stub' => $this->getMigrationFileName('create_guests_table.php'),
            __DIR__ . '/../database/migrations/drop_guest_columns_from_comments_table.php.stub' => $this->getMigrationFileName('drop_guest_columns_from_comments_table.php'),
            __DIR__ . '/../database/migrations/add_is_pinned_column_to_comments_table.php.stub' => $this->getMigrationFileName('add_is_pinned_column_to_comments_table.php'),
        ], 'commenter-migrations');

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/lakm/commenter')
        ], 'commenter-assets');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/commenter'),
        ], 'commenter-views');
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
