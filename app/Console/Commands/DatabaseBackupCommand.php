<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--filename= : Specific filename for the backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a full database backup and store it securely in storage/app/backups';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $backupDir = storage_path('app/backups');

        if (! File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0750, true);
        }

        $connection = config('database.default');
        $timestamp = now()->format('Y-m-d_His');
        $databasePath = config('database.connections.sqlite.database');
        $isSqliteFile = $connection === 'sqlite' && $databasePath !== ':memory:';
        $ext = $isSqliteFile ? 'sqlite' : 'sql';
        $filename = $this->option('filename') ?: "backup-{$connection}-{$timestamp}.{$ext}";
        $filepath = $backupDir.DIRECTORY_SEPARATOR.$filename;

        $this->info("Initiating database backup for connection [{$connection}]...");

        try {
            if ($connection === 'sqlite') {
                $this->backupSqlite($filepath);
            } elseif ($connection === 'mysql' || $connection === 'mariadb') {
                $this->backupMysql($filepath);
            } else {
                $this->backupGenericPdo($filepath);
            }

            $sizeKb = round(File::size($filepath) / 1024, 2);
            $this->info("Database backup created successfully: [{$filepath}] ({$sizeKb} KB)");

            $this->rotateBackups($backupDir, 10);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Backup failed: '.$e->getMessage());

            if (File::exists($filepath) && File::size($filepath) === 0) {
                File::delete($filepath);
            }

            return self::FAILURE;
        }
    }

    protected function backupSqlite(string $filepath): void
    {
        $databasePath = config('database.connections.sqlite.database');

        if ($databasePath !== ':memory:' && File::exists($databasePath)) {
            File::copy($databasePath, $filepath);

            return;
        }

        // For in-memory or SQLite SQL dump export
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        $handle = fopen($filepath, 'w');
        if (! $handle) {
            throw new \RuntimeException("Unable to open backup file for writing: {$filepath}");
        }

        fwrite($handle, "-- SQLite Database Backup\nPRAGMA foreign_keys=OFF;\n\n");

        foreach ($tables as $t) {
            $tableName = $t->name;
            $createSql = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$tableName]);

            fwrite($handle, "DROP TABLE IF EXISTS \"{$tableName}\";\n");
            if ($createSql && ! empty($createSql->sql)) {
                fwrite($handle, $createSql->sql.";\n");
            }

            $rows = DB::table($tableName)->get();
            foreach ($rows as $row) {
                $arr = (array) $row;
                $cols = '"'.implode('", "', array_keys($arr)).'"';
                $vals = implode(', ', array_map(fn ($v) => is_null($v) ? 'NULL' : DB::getPdo()->quote((string) $v), array_values($arr)));
                fwrite($handle, "INSERT INTO \"{$tableName}\" ({$cols}) VALUES ({$vals});\n");
            }

            fwrite($handle, "\n");
        }

        fwrite($handle, "PRAGMA foreign_keys=ON;\n");
        fclose($handle);
    }

    protected function backupMysql(string $filepath): void
    {
        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', '3306');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        // Check if mysqldump is available
        $checkProcess = new Process(['mysqldump', '--version']);
        $checkProcess->run();

        if ($checkProcess->isSuccessful()) {
            $cmd = [
                'mysqldump',
                "--host={$host}",
                "--port={$port}",
                "--user={$username}",
                '--single-transaction',
                '--quick',
                '--skip-lock-tables',
            ];

            if (! empty($password)) {
                $cmd[] = "--password={$password}";
            }

            $cmd[] = $database;

            $process = new Process($cmd);
            $process->setTimeout(300);
            $process->run();

            if ($process->isSuccessful() && strlen($process->getOutput()) > 0) {
                File::put($filepath, $process->getOutput());

                return;
            }
        }

        // Fallback to pure PHP schema & data dump via PDO
        $this->backupGenericPdo($filepath);
    }

    protected function backupGenericPdo(string $filepath): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $this->backupSqlite($filepath);

            return;
        }

        $tables = DB::connection()->select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
        $databaseName = DB::connection()->getDatabaseName();
        $tableKey = "Tables_in_{$databaseName}";

        $handle = fopen($filepath, 'w');
        if (! $handle) {
            throw new \RuntimeException("Unable to open backup file for writing: {$filepath}");
        }

        fwrite($handle, "-- CustomCommerce Database Backup\n");
        fwrite($handle, '-- Generated: '.now()->toIso8601String()."\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n\n");

        foreach ($tables as $tableRow) {
            $tableName = $tableRow->$tableKey ?? reset($tableRow);

            // Structure
            $createTable = DB::selectOne("SHOW CREATE TABLE `{$tableName}`");
            $createStatement = $createTable->{'Create Table'} ?? reset($createTable);

            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
            fwrite($handle, $createStatement.";\n\n");

            // Data
            DB::table($tableName)->orderBy(DB::raw('1'))->chunk(100, function ($rows) use ($handle, $tableName) {
                foreach ($rows as $row) {
                    $array = (array) $row;
                    $columns = array_keys($array);
                    $values = array_map(function ($val) {
                        if (is_null($val)) {
                            return 'NULL';
                        }

                        return DB::getPdo()->quote((string) $val);
                    }, array_values($array));

                    $colList = '`'.implode('`, `', $columns).'`';
                    $valList = implode(', ', $values);

                    fwrite($handle, "INSERT INTO `{$tableName}` ({$colList}) VALUES ({$valList});\n");
                }
            });

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    protected function rotateBackups(string $directory, int $keepCount): void
    {
        $files = File::files($directory);

        if (count($files) <= $keepCount) {
            return;
        }

        usort($files, fn ($a, $b) => $b->getMTime() <=> $a->getMTime());

        $toDelete = array_slice($files, $keepCount);
        foreach ($toDelete as $oldFile) {
            File::delete($oldFile->getRealPath());
        }
    }
}
