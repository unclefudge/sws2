<?php

namespace App\Console\Commands;

use App\Models\Site\Site;
use App\Models\Site\SiteCdc;
use Illuminate\Console\Command;

class SyncSiteCdc extends Command
{
    protected $signature = 'site:sync-cdc {--apply : Create missing CDC records and reconcile existing ones}';
    protected $description = 'Preview or populate CDC Requirements for existing Cape Cod CDC lodgement sites';

    public function handle(): int
    {
        $created = $existing = 0;
        Site::where('company_id', 3)->where('status', '!=', -2)->whereRaw('UPPER(TRIM(lodgement_type)) = ?', ['CDC'])->chunkById(200, function ($sites) use (&$created, &$existing) {
            foreach ($sites as $site) {
                $exists = SiteCdc::where('site_id', $site->id)->exists();
                $exists ? $existing++ : $created++;
                if (!$exists) $this->line($site->code . ' — ' . $site->name);
                if ($this->option('apply')) SiteCdc::ensureForSite($site);
            }
        });
        $this->info("CDC lodgement sites — missing CDC records: {$created}; existing CDC records: {$existing}.");
        $this->info($this->option('apply')
            ? 'Applied: created missing CDC records and reconciled stages/CDC received dates. No emails or ToDos sent.'
            : 'PREVIEW ONLY: no changes. Run again with --apply to populate CDC Requirements.');
        return self::SUCCESS;
    }
}
