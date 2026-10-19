<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseRestoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:restore {file? : The backup file or filename to restore} {--force : Force restore without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore database from an existing backup in storage/app/backups';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $backupDir = storage_path('app/backups');
        $fileArg = $this->argument('file');

        if ($fileArg) {
            $filepath = File::exists($fileArg) ? $fileArg : $backupDir.DIRECTORY_SEPARATOR.$fileArg;
        } else {
            // Find latest backup
            if (! File::exists($backupDir)) {
                $this->error("No backup directory exists at [{$backupDir}].");

                return self::FAILURE;
            }

            $files = File::files($backupDir);
            if (empty($files)) {
                $this->error('No backup files found in backup directory.');

                return self::FAILURE;
            }

            usort($files, fn ($a, $b) => $b->getMTime() <=> $a->getMTime());
            $filepath = $files[0]->getRealPath();
        }

        if (! File::exists($filepath)) {
            $this->error("Backup file not found at: [{$filepath}]");

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Are you sure you want to restore database from ['.basename($filepath).']? This will overwrite existing data.', false)) {
            $this->warn('Database restore cancelled.');

            return self::SUCCESS;
        }

        $connection = config('database.default');
        $this->info("Restoring database connection [{$connection}] from [{$filepath}]...");

        try {
            if ($connection === 'sqlite') {
                $this->restoreSqlite($filepath);
            } else {
                $this->restoreSqlDump($filepath);
            }

            $this->info('Database restoration completed successfully.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Database restoration failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    protected function restoreSqlite(string $filepath): void
    {
        $databasePath = config('database.connections.sqlite.database');

        if ($databasePath !== ':memory:' && ! str_ends_with($filepath, '.sql')) {
            File::copy($filepath, $databasePath);

            return;
        }

        $contents = File::get($filepath);
        DB::connection()->unprepared($contents);
    }

    protected function restoreSqlDump(string $filepath): void
    {
        $contents = File::get($filepath);

        DB::connection()->unprepared($contents);
    }
}
