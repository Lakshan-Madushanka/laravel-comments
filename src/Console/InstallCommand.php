<?php

namespace LakM\Comments\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Input\ArrayInput;

class InstallCommand extends Command
{
    protected $signature = 'commenter:install';

    protected $description = 'This will install the package';

    public function handle()
    {
        $this->info("❤️ Commants installer");

        $this->publishConfigs();
        $this->publishAssets();
        $this->publishMigrations();
        $migrated = $this->runMigrations();

        $this->showStatus($migrated);

        $this->askSupport();
    }

    private function publishConfigs(): void
    {
        $this->callSilent('vendor:publish', ['--tag' => 'comments-config']);
    }

    private function publishAssets(): void
    {
        $this->callSilent('vendor:publish', ['--tag' => 'comments-assets']);
    }

    private function publishMigrations(): void
    {
        $this->callSilent('vendor:publish', ['--tag' => 'comments-migrations']);
    }

    private function runMigrations(): bool
    {
        if ($confirmed = $this->confirm(
            'Do you wish to run migrations (if not you have to manually do that) ?',
            false
            )) {
            $this->callSilent('migrate');
        }

        return $confirmed;
    }

    private function showStatus(bool $migrated): void
    {
       $this->info("✅  Config published");
       $this->info("✅  Assets published");
       $this->info("✅  Migrations published");

       if ($migrated) {
           $this->info("✅  Ran Migrations");
           $this->newLine();
           $this->warn("All set! Simply add assets to your layout files to finish the installation");
           return;
       }

       $this->error("❌  Ran Migrations");

       $this->newLine();

       $this->warn("Run 'php artisan migrate' command and add assets to your layout files to finish the installation");
    }

    private function askSupport(): void
    {
        $this->newLine();

        $wantsToSupport = (new SymfonyQuestionHelper())->ask(
            new ArrayInput([]),
            $this->output,
            new ConfirmationQuestion(
                ' <options=bold>❤️ Wanna encourage us by starring it on GitHub?</>',
                false,
            )
        );

        $link = "https://github.com/Lakshan-Madushanka/laravel-comments";

        if ($wantsToSupport === true) {
            if (PHP_OS_FAMILY == 'Darwin') {
                exec('open ' . $link);
            }
            if (PHP_OS_FAMILY == 'Windows') {
                exec('start ' . $link);
            }
            if (PHP_OS_FAMILY == 'Linux') {
                exec('xdg-open ' . $link);
            }
        }
    }
}
