<?php

namespace App\Console\Commands;

use App\Models\Site\Site;
use App\Models\Site\SiteCc;
use Illuminate\Console\Command;

class SyncSiteCc extends Command
{
    protected $signature = 'site:sync-cc {--apply : Create missing CC records and reconcile existing ones}';
    protected $description = 'Preview or populate CC Requirements for existing Cape Cod upcoming/active sites';

    public function handle(): int
    {
        $created = $existing = 0;
        Site::where('company_id', 3)->where(function ($query) {
            $query->where('status', -1)->orWhere('status', '>', 0);
        })->chunkById(200, function ($sites) use (&$created, &$existing) {
            foreach ($sites as $site) {
                $exists = SiteCc::where('site_id', $site->id)->exists();
                $exists ? $existing++ : $created++;
                if ($this->option('apply')) SiteCc::ensureForSite($site);
            }
        });
        $this->info("Missing CC records: {$created}; existing CC records: {$existing}.");
        $this->info($this->option('apply')
            ? 'Applied: created missing CC records and reconciled stages/CC received dates. No emails or ToDos sent.'
            : 'PREVIEW ONLY: no changes. Run again with --apply to populate CC Requirements.');
        return self::SUCCESS;
    }
}
