<?php

namespace Tests\Unit;

use App\Models\Site\Site;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class SiteCcPlanningFieldsTest extends TestCase
{
    public function test_reference_fields_are_included_in_the_existing_zoho_text_sync(): void
    {
        foreach (['da_number', 'pan_number', 'planning_officer', 'cft_number', 'cc_number'] as $field) {
            $this->assertContains($field, Site::ZOHO_SYNC_TEXT_FIELDS);
            $this->assertNotContains($field, Site::ZOHO_SYNC_EXCLUDED_UPDATE_FIELDS);
        }
        $this->assertContains('construction_rcvd', Site::ZOHO_SYNC_DATE_FIELDS);
    }

    public function test_migration_preserves_reference_zeros_and_existing_site_data(): void
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('SQLite PDO is required for the isolated planning field migration test.');
        }
        $previous = Facade::getFacadeApplication();
        $container = new Container();
        $capsule = new Capsule($container);
        $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $container->instance('db', $capsule->getDatabaseManager());
        $container->bind('db.schema', fn () => $capsule->getConnection()->getSchemaBuilder());
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($container);
        try {
            $schema = $capsule->getConnection()->getSchemaBuilder();
            $schema->create('sites', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->date('construction_rcvd')->nullable();
            });
            $id = DB::table('sites')->insertGetId(['name' => 'Existing job', 'construction_rcvd' => '2026-10-30']);
            $migration = require __DIR__ . '/../../database/migrations/2026_10_30_150000_add_planning_reference_fields_to_sites.php';
            $migration->up();
            $values = ['da_number' => '000123', 'pan_number' => 'PAN-000045', 'planning_officer' => 'Jane Officer', 'cft_number' => '000067', 'cc_number' => 'CC-000089'];
            DB::table('sites')->where('id', $id)->update($values);
            $row = DB::table('sites')->where('id', $id)->first();
            foreach ($values as $field => $value) $this->assertSame($value, $row->{$field});
            $this->assertSame('2026-10-30', $row->construction_rcvd);
            $migration->down();
            $this->assertSame('Existing job', DB::table('sites')->where('id', $id)->value('name'));
            $this->assertSame('2026-10-30', DB::table('sites')->where('id', $id)->value('construction_rcvd'));
            foreach (array_keys($values) as $field) $this->assertFalse($schema->hasColumn('sites', $field));
        } finally {
            $capsule->getDatabaseManager()->disconnect();
            Facade::clearResolvedInstances();
            Facade::setFacadeApplication($previous);
        }
    }
}
