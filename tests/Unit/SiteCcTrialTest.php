<?php

namespace Tests\Unit;

use App\Models\Site\SiteCc;
use App\Models\Site\SiteCcItem;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class SiteCcTrialTest extends TestCase
{
    private $previousApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousApplication = Facade::getFacadeApplication();
        $container = new Container();
        $container->instance('date', new \Illuminate\Support\DateFactory());
        $container->instance('auth', new class {
            public function check(): bool
            {
                return false;
            }
        });
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($container);
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previousApplication);
        parent::tearDown();
    }

    public function test_cc_stage_uses_construction_certificate_and_not_oc_received_date(): void
    {
        $cc = new SiteCc(['status' => 1]);
        $cc->setDateFormat('Y-m-d H:i:s');
        $site = (object)['construction_rcvd' => null, 'completion_signed' => null, 'oc_rcvd_date' => '2026-10-30', 'status' => -1];
        $cc->setRelation('site', $site);
        $this->assertSame('Upcoming', $cc->calculateStage());
        $site->status = 1;
        $this->assertSame('Jobs in Const', $cc->calculateStage());
        $site->completion_signed = '2026-10-30';
        $this->assertSame("Prac'd Jobs", $cc->calculateStage());
        $cc->cc_requested = '2026-10-29';
        $this->assertSame('CC Booked', $cc->calculateStage());
        $site->construction_rcvd = '2026-10-30';
        $this->assertSame('CC Received', $cc->calculateStage());
        $cc->wbo_waiting = true;
        $this->assertSame('WBO', $cc->calculateStage());
        $site->status = -2;
        $this->assertSame('Disabled', $cc->calculateStage());
        $site->status = 1;
        $cc->status = -1;
        $this->assertSame('Disabled', $cc->calculateStage());
    }

    public function test_trial_notification_methods_have_no_database_or_mail_side_effects(): void
    {
        // No DB or mail services are bound. Any accidental task/email operation fails this test.
        $cc = new SiteCc();
        $item = new SiteCcItem();
        $cc->createAssignSupervisorToDo([108]);
        $cc->createSupervisorAssignedToDo([108]);
        $cc->createManagerSignOffToDo([108]);
        $cc->emailAssigned(null);
        $cc->emailAction(null);
        $cc->closeToDo();
        $item->createAssignSupervisorToDo([108]);
        $item->emailAssigned(null);
        $item->closeToDo();
        $this->assertCount(0, $cc->todos());
        $this->assertCount(0, $item->todos());
    }

}
