<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_compliance', function (Blueprint $table) {
            $table->index(
                ['site_id', 'archive', 'reason', 'status', 'date'],
                'site_compliance_list_filter_idx'
            );

            $table->index(
                ['user_id', 'reason', 'archive', 'date'],
                'site_compliance_user_nc_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('site_compliance', function (Blueprint $table) {
            $table->dropIndex('site_compliance_list_filter_idx');
            $table->dropIndex('site_compliance_user_nc_idx');
        });
    }
};
