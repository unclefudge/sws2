<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            foreach (['view' => 'View', 'edit' => 'Edit', 'add' => 'Add', 'del' => 'Delete', 'sig' => 'Sign Off'] as $action => $label) {
                $slug = "$action.site.precon";
                $permission = DB::table('permissions')->where('slug', $slug)->first();
                $permissionId = $permission?->id ?? DB::table('permissions')->insertGetId([
                    'slug' => $slug, 'name' => "$label Site Pre Con Review", 'model' => 'c',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                // Direct user grants only. No existing FOC roles/permissions are copied.
                foreach ([3, 108] as $userId) {
                    if (!DB::table('users')->where('id', $userId)->exists()) continue;
                    DB::table('permission_user')->updateOrInsert(
                        ['user_id' => $userId, 'company_id' => 3, 'permission_id' => $permissionId],
                        ['level' => in_array($action, ['view', 'edit'], true) ? 99 : 1, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

            if (!DB::table('categories')->where('type', 'precon_item')->exists()) {
                $categories = ['Scaffold', 'Living through the Build', 'Electrical Pre Con works', 'Ausgrid Requirements', 'Concrete', 'Structural', 'Estimating Checks', 'Drafting Checks', 'Construction Critical Comments', 'Prior to Construction', 'During Construction'];
                foreach ($categories as $index => $name) {
                    DB::table('categories')->insert([
                        'type' => 'precon_item', 'name' => $name, 'order' => $index + 1, 'status' => 1,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')->whereIn('slug', [
            'view.site.precon', 'edit.site.precon', 'add.site.precon', 'del.site.precon', 'sig.site.precon',
        ])->pluck('id');
        DB::table('permission_user')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        DB::table('categories')->where('type', 'precon_item')->delete();
    }
};
