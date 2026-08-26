<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_operation_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        // Preserve every category already used by a synced operation. The
        // operation itself continues storing the slug, so this is fully
        // backwards-compatible and does not require a risky foreign-key swap.
        if (Schema::hasTable('scheduled_operation_definitions')) {
            $now = now();
            DB::table('scheduled_operation_definitions')
                ->select('category')
                ->whereNotNull('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category')
                ->values()
                ->each(function (string $slug, int $index) use ($now) {
                    DB::table('scheduled_operation_categories')->insertOrIgnore([
                        'slug' => $slug,
                        'name' => Str::headline($slug),
                        'sort_order' => ($index + 1) * 10,
                        'enabled' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_operation_categories');
    }
};
