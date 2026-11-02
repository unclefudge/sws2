<?php

namespace Tests\Unit;

use App\Models\Site\Site;
use App\Models\Site\SiteCc;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class SiteCcRegisterFiltersTest extends TestCase
{
    private Capsule $capsule;
    private $previousApplication;

    protected function setUp(): void
    {
        parent::setUp();
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) $this->markTestSkipped('SQLite PDO required.');
        $this->previousApplication = Facade::getFacadeApplication();
        $container = new Container();
        $this->capsule = new Capsule($container);
        $this->capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $container->instance('db', $this->capsule->getDatabaseManager());
        $container->bind('db.schema', fn () => $this->capsule->getConnection()->getSchemaBuilder());
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($container);
        $schema = $this->capsule->getConnection()->getSchemaBuilder();
        $schema->create('sites', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('status');
            $table->date('construction_rcvd')->nullable();
        });
        $schema->create('site_cc', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('site_id');
            $table->integer('status');
            $table->date('cc_requested')->nullable();
            $table->string('stage');
        });
        foreach ([
            [1, 1, null, null, 1], // Upcoming even though the Site is in construction.
            [2, 1, null, '2026-11-01', 1],
            [3, 1, '2026-11-02', '2026-11-01', 1],
            [4, -1, '2026-11-02', null, 1], // Received takes priority without a requested date.
            [5, 1, null, null, -1], // Disabled CC.
            [6, -2, null, null, 1], // Cancelled Site.
        ] as [$id, $siteStatus, $received, $requested, $ccStatus]) {
            $this->db()->table('sites')->insert(['id' => $id, 'status' => $siteStatus, 'construction_rcvd' => $received]);
            $this->db()->table('site_cc')->insert(['id' => $id, 'site_id' => $id, 'status' => $ccStatus, 'cc_requested' => $requested, 'stage' => 'WBO']);
        }
        (require __DIR__ . '/../../database/migrations/2026_11_02_150000_add_draftsperson_cc_and_refresh_cc_stages.php')->up();
        foreach ([1 => 'Alice', 2 => 'Bob', 3 => 'Alice', 4 => null, 5 => 'Bob', 6 => 'Alice'] as $id => $name) {
            $this->db()->table('sites')->where('id', $id)->update(['draftsperson_cc' => $name]);
        }
    }

    private function db() { return $this->capsule->getConnection(); }

    private function ids(string $stage, string $draftsperson = 'all'): array
    {
        $query = $this->db()->table('site_cc AS m')->join('sites AS s', 's.id', '=', 'm.site_id');
        SiteCc::applyRegisterFilters($query, $stage, $draftsperson);
        return $query->orderBy('m.id')->pluck('m.id')->map(fn ($id) => (int) $id)->all();
    }

    protected function tearDown(): void
    {
        if (isset($this->capsule)) {
            $this->capsule->getDatabaseManager()->disconnect();
            Facade::clearResolvedInstances();
            Facade::setFacadeApplication($this->previousApplication);
        }
        parent::tearDown();
    }

    public function test_date_categories_are_mutually_exclusive_and_ignore_old_stages(): void
    {
        $this->assertSame([1], $this->ids('Upcoming'));
        $this->assertSame([2], $this->ids('CC Requested'));
        $this->assertSame([3, 4], $this->ids('CC Received'));
        $this->assertSame([5, 6], $this->ids('Disabled'));
        // Filtering follows current dates even before any observer/hourly refresh.
        $this->db()->table('sites')->where('id', 1)->update(['construction_rcvd' => '2026-11-02']);
        $this->assertSame([], $this->ids('Upcoming'));
        $this->assertSame([1, 3, 4], $this->ids('CC Received'));
    }

    public function test_draftsperson_filters_combine_with_category_and_do_not_broaden_authorisation(): void
    {
        $this->assertSame([1], $this->ids('Upcoming', 'Alice'));
        $this->assertSame([], $this->ids('Upcoming', 'Bob'));
        $this->assertSame([2], $this->ids('CC Requested', 'Bob'));
        $this->assertSame([3], $this->ids('CC Received', 'Alice'));
        $this->assertSame([4], $this->ids('CC Received', '__unassigned__'));
        $query = $this->db()->table('site_cc AS m')->join('sites AS s', 's.id', '=', 'm.site_id')->whereIn('m.id', [4]);
        SiteCc::applyRegisterFilters($query, 'CC Received', 'Alice');
        $this->assertSame(0, $query->count());
    }

    public function test_migration_reclassifies_records_without_creating_or_changing_dates(): void
    {
        $stages = $this->db()->table('site_cc')->orderBy('id')->pluck('stage')->all();
        $this->assertSame(['Upcoming', 'CC Requested', 'CC Received', 'CC Received', 'Disabled', 'Disabled'], $stages);
        $this->assertSame(6, $this->db()->table('site_cc')->count());
        $this->assertSame('2026-11-01', $this->db()->table('site_cc')->where('id', 2)->value('cc_requested'));
        $this->assertContains('draftsperson_cc', Site::ZOHO_SYNC_TEXT_FIELDS);
        (require __DIR__ . '/../../database/migrations/2026_11_02_150000_add_draftsperson_cc_and_refresh_cc_stages.php')->down();
        $this->assertFalse($this->db()->getSchemaBuilder()->hasColumn('sites', 'draftsperson_cc'));
        $this->assertSame(6, $this->db()->table('site_cc')->count());
    }
}
