<?php

namespace LakM\Comments\Console\Concerns;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Finder\Finder;

trait BuildAssets
{
    protected function buildAssets(): bool
    {
        $basePath = base_path('vendor/lakm/laravel-comments/');

        try {
            // Clear build directory if exists
            $this->clearBuildDirectory($basePath);

            /**
             * If need dark mode support replace no-dark with dark else replace dark with no-dark
             * and set dark mode strategy in tailwind.config.js
             */
            $this->toggleDarkModeSupport($basePath);

            // Build assets
            $this->build($basePath);
        } catch (Exception $e) {
            $this->error("❌  Failed to build assets", $e);
            return false;
        } finally {
            if (File::exists($basePath . 'node_modules')) {
                File::deleteDirectory($basePath . 'node_modules');
            }
        }

        return true;
    }

    private function clearBuildDirectory(string $basePath): void
    {
        if (File::exists($buildPath = ($basePath . 'public/build'))) {
            File::deleteDirectory($buildPath);
        }
    }

    private function toggleDarkModeSupport(string $basePath): void
    {
        $needDarkMode = $this->confirm('Would you like dark mode support?');
        $strategy = 'media';
        $selector = '.dark';

        if ($needDarkMode) {
            $strategy = $this->choice('Choose dark mode strategy', ['media', 'selector'], 0);

            if ($strategy === 'selector') {
                $selector = $this->ask('Enter the selector to toggle dark mode', '.dark');
            }
        }

        $resources = (new Finder())
            ->in($basePath . 'resources/views')
            ->name('*.blade.php');

        foreach ($resources as $resource) {
            if ($needDarkMode) {
                file_put_contents($resource->getPathname(), preg_replace('/(?<=^|\s)no-dark:(?=\w|!)/', 'dark:', $resource->getContents()));
            } else {
                file_put_contents($resource->getPathname(), preg_replace('/(?<=^|\s)dark:(?=\w|!)/', 'no-dark:', $resource->getContents()));
            }
        }

        if ($needDarkMode) {
            $darkMode = "'media',";

            if ($strategy === 'selector') {
                $darkMode = "['selector', '$selector'],";
            }

            $tailwindConfig = preg_replace("/darkMode:\s*(\[[^\]]*\]|\s*'[^']*')\s*,?/", "darkMode: $darkMode", file_get_contents($basePath . 'tailwind.config.js'));

            file_put_contents($basePath . 'tailwind.config.js', $tailwindConfig);
        }
    }

    private function build(string $basePath): void
    {
        $this->info('🛠️  Building assets this might take a while...');
        $this->newLine();

        Process::path($basePath)
            ->timeout(300)
            ->run('npm install')
            ->throw();

        Process::path($basePath)
            ->timeout(300)
            ->run('npm run build')
            ->throw();
    }
}
