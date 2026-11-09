<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            foreach (['view' => 'View', 'edit' => 'Edit', 'add' => 'Add', 'del' => 'Delete', 'sig' => 'Sign Off'] as $action => $label) {
                $slug = "$action.site.cdc";
                $permission = DB::table('permissions')->where('slug', $slug)->first();
                $permissionId = $permission?->id ?? DB::table('permissions')->insertGetId([
                    'slug' => $slug, 'name' => "$label Site CDC", 'model' => 'c',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                // Direct user grants only. No existing FOC roles/permissions are copied.
                foreach ([108] as $userId) {
                    if (!DB::table('users')->where('id', $userId)->exists()) continue;
                    DB::table('permission_user')->updateOrInsert(
                        ['user_id' => $userId, 'company_id' => 3, 'permission_id' => $permissionId],
                        ['level' => in_array($action, ['view', 'edit'], true) ? 99 : 1, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

            if (!DB::table('categories')->where('type', 'cdc_item')->exists()) {
                $categoryMap = [];
                $categories = DB::table('categories')->where('type', 'cc_item')->where('status', 1)->orderBy('order')->get();
                foreach ($categories as $category) {
                    $attributes = (array) $category;
                    unset($attributes['id']);
                    $attributes['type'] = 'cdc_item';
                    $attributes['name'] = preg_replace('/\bCC\b/', 'CDC', $attributes['name']);
                    $attributes['notify'] = 0;
                    $attributes['notify_users'] = null;
                    $attributes['created_at'] = now();
                    $attributes['updated_at'] = now();
                    $categoryMap[$category->id] = DB::table('categories')->insertGetId($attributes);
                }
                foreach ($categories as $category) {
                    DB::table('categories')->where('id', $categoryMap[$category->id])->update([
                        'parent' => $categoryMap[$category->parent] ?? null,
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')->whereIn('slug', [
            'view.site.cdc', 'edit.site.cdc', 'add.site.cdc', 'del.site.cdc', 'sig.site.cdc',
        ])->pluck('id');
        DB::table('permission_user')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        DB::table('categories')->where('type', 'cdc_item')->delete();
    }
};
