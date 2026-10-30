<?php

namespace Tests\Unit;

use App\Traits\UserRolesPermissions;
use PHPUnit\Framework\TestCase;

class SiteCcPermissionsTest extends TestCase
{
    private function user(array $levels, int $id = 108): object
    {
        return new class($levels, $id) {
            use UserRolesPermissions;
            public int $id;
            public int $company_id = 3;
            public object $company;
            private array $levels;

            public function __construct(array $levels, int $id)
            {
                $this->levels = $levels;
                $this->id = $id;
                $this->company = new class {
                    public int $id = 3;
                    public function reportsTo(): self { return $this; }
                };
            }

            public function permissionLevel($permission, $company_id)
            {
                return $this->levels[$permission] ?? 0;
            }
        };
    }

    private function record(int $supervisor = 108): object
    {
        return new class($supervisor) {
            public int $super_id;
            public int $status = 1;
            public int $site_id = 10;
            public function __construct(int $supervisor) { $this->super_id = $supervisor; }
            public function assignedTo(): array { return [3]; }
        };
    }

    public function test_foc_permissions_do_not_grant_cc_access_even_to_the_assigned_supervisor(): void
    {
        $user = $this->user(['view.site.foc' => 99, 'edit.site.foc' => 99]);
        $this->assertFalse($user->hasAnyPermissionType('site.cc'));
        $this->assertFalse($user->allowed2('view.site.cc', $this->record()));
        $this->assertFalse($user->allowed2('edit.site.cc', $this->record()));
    }

    public function test_cc_permissions_support_all_five_actions_without_foc_permissions(): void
    {
        $levels = [];
        foreach (['view', 'edit', 'add', 'del', 'sig'] as $action) $levels["$action.site.cc"] = in_array($action, ['view', 'edit'], true) ? 99 : 1;
        $user = $this->user($levels);
        $this->assertTrue($user->hasAnyPermissionType('site.cc'));
        $this->assertFalse($user->hasAnyPermissionType('site.foc'));
        foreach (array_keys($levels) as $permission) {
            $this->assertTrue($user->allowed2($permission, $this->record(999)), $permission);
        }
    }

    public function test_supervisor_scope_requires_the_new_permission_and_the_matching_supervisor(): void
    {
        $user = $this->user(['view.site.cc' => 40, 'edit.site.cc' => 40]);
        $this->assertTrue($user->allowed2('view.site.cc', $this->record()));
        $this->assertTrue($user->allowed2('edit.site.cc', $this->record()));
        $this->assertFalse($user->allowed2('view.site.cc', $this->record(999)));
        $this->assertFalse($user->allowed2('edit.site.cc', $this->record(999)));
    }

    public function test_assigned_company_view_permission_does_not_grant_edit_permission(): void
    {
        $user = $this->user(['view.site.cc' => 30]);
        $this->assertTrue($user->allowed2('view.site.cc', $this->record()));
        $this->assertFalse($user->allowed2('edit.site.cc', $this->record()));
    }
}
