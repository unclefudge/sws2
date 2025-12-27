<?php

namespace App\Console\Commands;

use App\Services\FileBank;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FileBankMigrate extends Command
{
    protected $signature = 'filebank:migrate
        {path? : Top-level path (e.g. site, company)}
        {--pattern= : Optional substring filter}
        {--dry-run : Perform a dry run only}';

    protected $description = 'Migrate FileBank files from local disk to Spaces';

    public function handle(): int
    {
        $path = $this->argument('path') ?? '';
        $pattern = $this->option('pattern');
        $dryRun = (bool)$this->option('dry-run');

        $this->info('Scanning files…');

        $files = collect(
            Storage::disk('filebank_local')->allFiles($path)
        );

        if ($pattern) {
            $files = $files->filter(fn($p) => str_contains($p, $pattern));
        }

        $count = $files->count();

        if ($count === 0) {
            $this->warn('No files found.');
            return self::SUCCESS;
        }

        $this->info("Found {$count} files");
        $this->info($dryRun ? 'DRY RUN — no files will be migrated' : 'Migrating…');

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $results = [
            'migrated' => [],
            'skipped' => [],
            'failed' => [],
        ];

        foreach ($files as $file) {
            try {
                if ($dryRun) {
                    $results['skipped'][] = $file;
                } else {
                    if (FileBank::migrateOne($file)) {
                        $results['migrated'][] = $file;
                    } else {
                        $results['skipped'][] = $file;
                    }
                }
            } catch (\Throwable $e) {
                $results['failed'][$file] = $e->getMessage();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Migration complete');
        $this->table(
            ['Result', 'Count'],
            [
                ['Migrated', count($results['migrated'])],
                ['Skipped', count($results['skipped'])],
                ['Failed', count($results['failed'])],
            ]
        );

        if (!empty($results['failed'])) {
            $this->error('Failures:');
            foreach ($results['failed'] as $file => $error) {
                $this->line("- {$file}: {$error}");
            }
        }

        return self::SUCCESS;
    }
}
