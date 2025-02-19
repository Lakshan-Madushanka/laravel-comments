<?php

namespace LakM\Comments\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LakM\Comments\Abstracts\AbstractQueries;
use LakM\Comments\CommentServiceProvider;
use LakM\Comments\Models\Comment;
use LakM\Comments\Tests\Fixtures\User;
use Livewire\Livewire;
use Livewire\LivewireServiceProvider;
use Mews\Purifier\PurifierServiceProvider;
use Spatie\Honeypot\HoneypotServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        config(['honeypot.enabled' => false]);
        config(['comments.user_model' => User::class]);

        AbstractQueries::$guest = null;

        Livewire::withoutLazyLoading();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
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

        $schema->create('guests', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        $schema->create('comments', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('commentable');
            $table->nullableMorphs('commenter');
            $table->unsignedBigInteger('reply_id')->nullable()->index();

            $table->text('text');
            $table->boolean('approved')->default(false)->index();
            $table->timestamps();
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->foreign('reply_id')->references('id')->on('comments')->cascadeOnDelete();
        });

        $schema->create('reactions', function (Blueprint $table) {
            $table->id();
            $table->morphs('owner');
            $table->foreignIdFor(Comment::class);

            $table->string('type');
            $table->timestamps();
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            CommentServiceProvider::class,
            HoneypotServiceProvider::class,
            PurifierServiceProvider::class,
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
