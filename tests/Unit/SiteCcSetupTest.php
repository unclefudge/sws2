<?php

namespace Tests\Unit;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class SiteCcSetupTest extends TestCase
{
    private $previousApplication;
    private Capsule $capsule;
    private $previousResolver;
    private $previousDispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('SQLite PDO is required for the isolated CC setup test.');
        }
        $this->previousApplication = Facade::getFacadeApplication();
        $container = new Container();
        $this->capsule = new Capsule($container);
        $this->capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $this->capsule->setAsGlobal();
        $container->instance('date', new \Illuminate\Support\DateFactory());
        $container->instance('db', $this->capsule->getDatabaseManager());
        $container->bind('db.schema', fn () => $this->capsule->schema());
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($container);
        $this->previousResolver = \Illuminate\Database\Eloquent\Model::getConnectionResolver();
        $this->previousDispatcher = \Illuminate\Database\Eloquent\Model::getEventDispatcher();
        $container->instance('auth', new class {
            public function check(): bool { return false; }
            public function id() { return null; }
        });
        $this->capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher($container));
        $this->capsule->bootEloquent();
        \Illuminate\Database\Eloquent\Model::clearBootedModels();
        \App\Models\Site\SiteCc::observe(\App\Observers\SiteCcObserver::class);
        \App\Models\Site\Site::observe(\App\Observers\SiteObserver::class);
        $schema = $this->capsule->schema();
        foreach (['users', 'sites'] as $name) {
            $schema->create($name, fn (Blueprint $table) => $table->increments('id'));
        }
        $schema->table('sites', function (Blueprint $table) {
            $table->integer('company_id')->default(3);
            $table->integer('status')->default(-1);
            $table->dateTime('construction_rcvd')->nullable();
            $table->dateTime('completion_signed')->nullable();
            $table->dateTime('oc_rcvd_date')->nullable();
            $table->timestamps();
        });
        $schema->create('actions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('table');
            $table->integer('table_id');
            $table->text('action');
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
        });
        $schema->create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('model');
            $table->timestamps();
        });
        foreach (['permission_user', 'permission_role'] as $name) {
            $schema->create($name, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('permission_id');
                $table->integer('user_id')->nullable();
                $table->integer('role_id')->nullable();
                $table->integer('company_id');
                $table->integer('level');
                $table->timestamps();
            });
        }
        $schema->create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->string('name');
            $table->integer('status');
            $table->integer('order');
            $table->integer('parent')->nullable();
            $table->integer('notify')->nullable();
            $table->text('notify_users')->nullable();
            $table->timestamps();
        });
        $schema->create('site_foc', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('site_id');
        });
        DB::table('users')->insert([['id' => 3], ['id' => 108], ['id' => 999]]);
        DB::table('categories')->insert(['type' => 'foc_item', 'name' => 'Inspections', 'order' => 1, 'status' => 1, 'notify' => 1, 'notify_users' => '999']);
    }

    protected function tearDown(): void
    {
        if (isset($this->capsule)) {
            $this->capsule->getDatabaseManager()->disconnect();
            \Illuminate\Database\Eloquent\Model::clearBootedModels();
            if ($this->previousResolver) \Illuminate\Database\Eloquent\Model::setConnectionResolver($this->previousResolver);
            else \Illuminate\Database\Eloquent\Model::unsetConnectionResolver();
            if ($this->previousDispatcher) \Illuminate\Database\Eloquent\Model::setEventDispatcher($this->previousDispatcher);
            else \Illuminate\Database\Eloquent\Model::unsetEventDispatcher();
            Facade::clearResolvedInstances();
            Facade::setFacadeApplication($this->previousApplication);
        }
        parent::tearDown();
    }

    public function test_setup_grants_only_the_two_users_and_keeps_categories_independent(): void
    {
        $migration = require __DIR__ . '/../../database/migrations/2026_10_30_120100_add_site_cc_permissions_and_categories.php';
        $migration->up();
        $migration->up(); // Safe to re-enter without duplicate grants or categories.
        $this->assertSame(5, DB::table('permissions')->count());
        $this->assertSame(10, DB::table('permission_user')->count());
        $this->assertSame([3, 108], DB::table('permission_user')->distinct()->orderBy('user_id')->pluck('user_id')->all());
        $this->assertSame(0, DB::table('permission_role')->count());
        $this->assertSame(1, DB::table('categories')->where('type', 'cc_item')->count());
        $this->assertSame(99, (int) DB::table('permission_user')->join('permissions', 'permissions.id', '=', 'permission_user.permission_id')->where('slug', 'view.site.cc')->value('level'));
        $this->assertSame(1, (int) DB::table('permission_user')->join('permissions', 'permissions.id', '=', 'permission_user.permission_id')->where('slug', 'sig.site.cc')->value('level'));
        DB::table('categories')->where('type', 'cc_item')->update(['name' => 'CC only']);
        $this->assertSame('Inspections', DB::table('categories')->where('type', 'foc_item')->value('name'));
        $this->assertSame('999', DB::table('categories')->where('type', 'foc_item')->value('notify_users'));
        $this->assertNull(DB::table('categories')->where('type', 'cc_item')->value('notify_users'));
        $migration->down();
        $this->assertSame(0, DB::table('permissions')->count());
        $this->assertSame(0, DB::table('permission_user')->count());
        $this->assertSame('Inspections', DB::table('categories')->where('type', 'foc_item')->value('name'));
    }

    public function test_new_tables_are_independent_and_roll_back_without_touching_sites(): void
    {
        $migration = require __DIR__ . '/../../database/migrations/2026_10_30_120000_create_site_cc_tables.php';
        $migration->up();
        $this->assertTrue($this->capsule->schema()->hasColumn('site_cc', 'cc_received'));
        $this->assertTrue($this->capsule->schema()->hasColumn('site_cc_items', 'cc_id'));
        $migration->down();
        $this->assertFalse($this->capsule->schema()->hasTable('site_cc'));
        $this->assertTrue($this->capsule->schema()->hasTable('sites'));
        $this->assertTrue($this->capsule->schema()->hasTable('categories'));
    }
    public function test_automatic_creation_is_idempotent_and_imports_the_construction_date(): void
    {
        (require __DIR__ . '/../../database/migrations/2026_10_30_120000_create_site_cc_tables.php')->up();
        $siteId = DB::table('sites')->insertGetId(['company_id' => 3, 'status' => -1, 'construction_rcvd' => '2026-10-30 00:00:00']);
        $site = \App\Models\Site\Site::findOrFail($siteId);
        $cc = \App\Models\Site\SiteCc::ensureForSite($site);
        $again = \App\Models\Site\SiteCc::ensureForSite($site);
        $this->assertSame($cc->id, $again->id);
        $this->assertSame(1, DB::table('site_cc')->count());
        $this->assertSame(1, DB::table('actions')->count());
        $this->assertSame('CC Received', $cc->stage);
        $this->assertSame('2026-10-30', $cc->cc_received->format('Y-m-d'));
        $cc->update(['notes' => 'Keep this note', 'status' => -1]);
        $site->update(['construction_rcvd' => '2026-11-01 00:00:00']);
        $cc->refresh();
        $this->assertSame('Disabled', $cc->stage);
        $this->assertSame('Keep this note', $cc->notes);
        $this->assertSame('2026-11-01', $cc->cc_received->format('Y-m-d'));
        $cc->update(['status' => 1]);
        $this->assertSame('CC Received', $cc->fresh()->stage);
    }

    public function test_cancelled_and_other_company_sites_do_not_get_new_cc_records(): void
    {
        (require __DIR__ . '/../../database/migrations/2026_10_30_120000_create_site_cc_tables.php')->up();
        foreach ([['company_id' => 3, 'status' => -2], ['company_id' => 210, 'status' => 1]] as $attributes) {
            $id = DB::table('sites')->insertGetId($attributes);
            $this->assertNull(\App\Models\Site\SiteCc::ensureForSite(\App\Models\Site\Site::findOrFail($id)));
        }
        $this->assertSame(0, DB::table('site_cc')->count());
    }
}
