<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_foc', function (Blueprint $table) {
            $table->text('certifier')->nullable();
            $table->text('certifier_phone')->nullable();
            $table->text('pca_name')->nullable();
            $table->text('pca_email')->nullable();
            $table->text('inspections_email')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_foc', function (Blueprint $table) {
            $table->dropColumn([
                'certifier', 'certifier_phone', 'pca_name', 'pca_email', 'inspections_email',
            ]);
        });
    }
};
