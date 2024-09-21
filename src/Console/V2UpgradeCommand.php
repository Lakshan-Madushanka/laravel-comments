<?php

namespace LakM\Comments\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LakM\Comments\Models\Comment;

class V2UpgradeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string.example
     */
    protected $signature = 'commenter:upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update commenter package to version 2.*';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info("🛠️ Preparing Commenter to update to version 2.*");

        DB::beginTransaction();

        try {
            $this->info('Migrating data from comments table...');
            $this->migrateCommentsData();

            $this->info('Dropping redundant columns from comments table...');
            $this->dropColumnsFromCommentsTable();
        } catch (\Exception $exception) {
            DB::rollBack();
            $this->error('Error ' . $exception->getMessage());
            exit(1);
        }

        DB::commit();

        $this->newLine();

        $this->info("✅  Commenter is ready to update");
        $this->info("Run below command to update commenter");
        $this->info("composer update lakm/laravel-comments:^2.0");

        exit(0);
    }

    protected function migrateCommentsData(): void
    {
        $fileName = $this->getMigrationFileName('create_guests_table.php');

        if (!($path = glob(database_path('migrations/' . '*guests*')))) {
            copy(__DIR__ . '/stubs/create_guests_table.php.stub', database_path('migrations/' . $fileName));
            Artisan::call("migrate", ['--path' => 'database/migrations/' . $fileName]);
        } else {
            Artisan::call("migrate", ['--path' => 'database/migrations/' . Str::after($path[0], 'migrations/')]);
        }

        // We need to move guest data to guests table

        // First we pick distinct guest_email columns
        $emails = Comment::query()
            ->groupBy(['guest_email'])
            ->get();

        $emails->each(function (Comment $comment) {
            // Create a new guest record in guests table
            if (!is_null($comment->commenter_type)) {
                return;
            }

            $guest = $this->guestModel()::query()
                ->createOrFirst(
                    ['email' => $comment->guest_email],
                    [
                        'email' => $comment->guest_email,
                        'name' => $comment->guest_name,
                        'ip_address' => $comment->ip_address,
                    ]
                );

            // Update commenter morph type in comments table
            Comment::query()
                ->where('guest_email', $comment->guest_email)
                ->update([
                    'commenter_id' => $guest->getKey(),
                    'commenter_type' => 'LakM\Comments\Models\Guest'
                ]);
        });
    }

    public function dropColumnsFromCommentsTable(): void
    {
        $fileName = $this->getMigrationFileName('drop_guest_columns_from_comments_table.php');

        if (!($path = glob(database_path('migrations/' . '*drop_guest*')))) {
            copy(__DIR__ . '/stubs/drop_guest_columns_from_comments_table.php.stub', database_path('migrations/' . $fileName));
            Artisan::call("migrate", ['--path' => 'database/migrations/' . $fileName]);
        } else {
            Artisan::call("migrate", ['--path' => 'database/migrations/' . Str::after($path[0], 'migrations/')]);
        }
    }

    public function guestModel(): Model
    {
        return new class () extends Model {
            protected $table = 'guests';
            protected $guarded = [];
        };
    }

    protected function getMigrationFileName(string $migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = app()->make(Filesystem::class);

        return Collection::make([app()->databasePath() . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR])
            ->flatMap(fn ($path) => $filesystem->glob($path . '*_' . $migrationFileName))
            ->push("{$timestamp}_{$migrationFileName}")
            ->first();
    }
}
