<?php

namespace LakM\Comments\Tests;

use GrahamCampbell\Security\SecurityServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use LakM\Comments\CommentServiceProvider;
use LakM\Comments\Models\Comment;
use LakM\Comments\Tests\Fixtures\User;
use Livewire\LivewireServiceProvider;
use Spatie\Honeypot\HoneypotServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        config(['honeypot.enabled' => false]);
    }

    public function setUpDatabase($app)
    {
        $schema = $app['db']->connection()->getSchemaBuilder();

        $schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        $schema->create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $schema->create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $schema->create('comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable');
            $table->nullableMorphs('commenter');
            $table->text('text');
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->boolean('approved')->default(false);
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        $schema->create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->nullable();
            $table->foreignIdFor(Comment::class);
            $table->string('type');
            $table->string('ip_address');
            $table->timestamps();
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            CommentServiceProvider::class,
            LivewireServiceProvider::class,
            HoneypotServiceProvider::class,
            SecurityServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.debug', true);
        $app['config']->set('app.env', 'local');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
