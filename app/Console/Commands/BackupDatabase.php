<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Throwable;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database
        {--compress=0 : Compress backup output to .gz (1=true, 0=false)}
        {--keep=14 : Number of recent backups to retain (older ones will be deleted)}';

    protected $description = 'Create a database backup and store it under storage/app/backups, with optional compression and retention pruning.';

    public function handle(): int
    {
        $connection = config('database.default');
        $config = (array) config("database.connections.{$connection}", []);
        $driver = (string) ($config['driver'] ?? $connection);
        $compress = (bool) ((int) $this->option('compress'));
        $keep = max(1, (int) $this->option('keep'));

        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $timestamp = now()->format('Ymd_His');
        $basename = "{$connection}_{$timestamp}";
        $outputPath = '';
        $success = false;

        try {
            switch ($driver) {
                case 'sqlite':
                    $outputPath = $this->backupSqlite($config, $dir, $basename, $compress);
                    $success = true;
                    break;

                case 'mysql':
                case 'mariadb':
                    $outputPath = $this->backupMysql($config, $dir, $basename, $compress);
                    $success = true;
                    break;

                case 'pgsql':
                case 'postgres':
                    $outputPath = $this->backupPostgres($config, $dir, $basename, $compress);
                    $success = true;
                    break;

                default:
                    $this->error("Unsupported DB driver: {$driver}");
                    return Command::FAILURE;
            }
        } catch (Throwable $e) {
            $this->error("Backup failed: {$e->getMessage()}");
            report($e);

            return Command::FAILURE;
        }

        if ($success) {
            $this->info("Backup created: {$outputPath}");
            $this->pruneOldBackups($dir, $keep);
        }

        return Command::SUCCESS;
    }

    protected function backupSqlite(array $config, string $dir, string $basename, bool $compress): string
    {
        $dbPath = (string) ($config['database'] ?? '');
        if ($dbPath === '' || $dbPath === ':memory:') {
            // Fallback to conventional sqlite file
            $dbPath = database_path('database.sqlite');
        }

        if (! is_file($dbPath)) {
            throw new \RuntimeException("SQLite database file not found: {$dbPath}");
        }

        $dest = "{$dir}/{$basename}.sqlite";
        if (! @copy($dbPath, $dest)) {
            throw new \RuntimeException("Failed to copy sqlite DB from {$dbPath} to {$dest}");
        }

        if ($compress) {
            $gz = $this->compressFile($dest);
            return $gz;
        }

        return $dest;
    }

    protected function backupMysql(array $config, string $dir, string $basename, bool $compress): string
    {
        $mysqldump = (string) env('DB_DUMP_MYSQLDUMP_PATH', 'mysqldump');

        $host = (string) ($config['host'] ?? '127.0.0.1');
        $port = (string) ($config['port'] ?? '3306');
        $user = (string) ($config['username'] ?? '');
        $pass = (string) ($config['password'] ?? '');
        $db = (string) ($config['database'] ?? '');

        if ($db === '') {
            throw new \InvalidArgumentException('MySQL database name is empty.');
        }

        // Verify mysqldump is available; provide a clear hint for Windows/Laragon if not.
        $check = new Process([$mysqldump, '--version']);
        $check->run();
        if (! $check->isSuccessful()) {
            throw new \RuntimeException(
                "mysqldump not found. Set DB_DUMP_MYSQLDUMP_PATH in your .env to the absolute path of mysqldump (e.g., C:\\laragon\\bin\\mysql\\mysql-8.x\\bin\\mysqldump.exe) or add mysqldump to your system PATH."
            );
        }

        $args = [
            $mysqldump,
            "--host={$host}",
            "--port={$port}",
            "--user={$user}",
            "--password={$pass}",
            '--default-character-set=utf8mb4',
            '--routines',
            '--events',
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            $db,
        ];

        $process = new Process($args, null, null, null, 3600);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('mysqldump failed: ' . $process->getErrorOutput());
        }

        $dest = "{$dir}/{$basename}.sql";
        File::put($dest, $process->getOutput());

        if ($compress) {
            $gz = $this->compressFile($dest);
            return $gz;
        }

        return $dest;
    }

    protected function backupPostgres(array $config, string $dir, string $basename, bool $compress): string
    {
        $pgDump = (string) env('DB_DUMP_PGDUMP_PATH', 'pg_dump');

        $host = (string) ($config['host'] ?? '127.0.0.1');
        $port = (string) ($config['port'] ?? '5432');
        $user = (string) ($config['username'] ?? '');
        $pass = (string) ($config['password'] ?? '');
        $db = (string) ($config['database'] ?? '');

        if ($db === '') {
            throw new \InvalidArgumentException('PostgreSQL database name is empty.');
        }

        // Use custom format (-Fc), better for restore with pg_restore
        $dest = "{$dir}/{$basename}.dump";

        $args = [
            $pgDump,
            "-h",
            $host,
            "-p",
            $port,
            "-U",
            $user,
            "-d",
            $db,
            "-Fc",
            "-f",
            $dest,
        ];

        $env = ['PGPASSWORD' => $pass];
        $process = new Process($args, null, $env, null, 3600);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('pg_dump failed: ' . $process->getErrorOutput());
        }

        if ($compress) {
            $gz = $this->compressFile($dest);
            return $gz;
        }

        return $dest;
    }

    protected function compressFile(string $path): string
    {
        $contents = File::get($path);
        $gz = $path . '.gz';
        File::put($gz, gzencode($contents, 9));
        File::delete($path);

        return $gz;
    }

    protected function pruneOldBackups(string $dir, int $keep): void
    {
        $files = collect(File::files($dir))
            ->filter(function (\SplFileInfo $file) {
                $name = $file->getFilename();
                return preg_match('/\.(sql|sqlite|dump)(\.gz)?$/i', $name) === 1;
            })
            ->sortByDesc(fn(\SplFileInfo $file) => $file->getMTime())
            ->values();

        if ($files->count() <= $keep) {
            return;
        }

        $toDelete = $files->slice($keep);
        foreach ($toDelete as $file) {
            @File::delete($file->getPathname());
        }

        $this->info(sprintf('Pruned %d old backup(s).', $toDelete->count()));
    }
}
