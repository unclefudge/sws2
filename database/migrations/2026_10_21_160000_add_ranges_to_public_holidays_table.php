<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_holidays', function (Blueprint $table) {
            $table->date('end_date')->nullable()->after('date');
            // Supports installations whose old type column was an enum.
            $table->string('type', 32)->default('public')->change();
        });
    }

    public function down(): void
    {
        // A range cannot be safely rolled back to one date. Preserve the blocked
        // calendar rather than silently reopen every day after its start date.
        if (\Illuminate\Support\Facades\DB::table('public_holidays')
            ->whereNotNull('end_date')->whereColumn('end_date', '>', 'date')->exists()) {
            throw new \RuntimeException('Remove or convert date ranges to individual dates before rolling back this migration.');
        }
        Schema::table('public_holidays', fn (Blueprint $table) => $table->dropColumn('end_date'));
        // Keep type as varchar; restoring an unknown former enum could lose data.
    }
};
